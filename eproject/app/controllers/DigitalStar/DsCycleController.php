<?php

namespace DigitalStar;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Input;
use PCK\DigitalStar\ModuleParameters\DsModuleParameter;
use PCK\Helpers\DBTransaction;
use Redirect;
use Request;
use Response;
use View;

use PCK\Companies\Company;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\Evaluation\DsCycleTemplateForm;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\DigitalStar\Evaluation\DsCycleWeightedNode;
use PCK\DigitalStar\Forms\DsCycleForm;
use PCK\DigitalStar\Forms\DsCycleAddCompanyForm;
use PCK\Vendor\Vendor;
use PCK\VendorRegistration\VendorRegistration;
use PCK\WeightedNode\WeightedNode;

class DsCycleController extends \BaseController
{
    protected $dsCycleForm;
    protected $dsCycleAddCompanyForm;

    public function __construct(DsCycleForm $dsCycleForm, DsCycleAddCompanyForm $dsCycleAddCompanyForm)
    {
        $this->dsCycleForm = $dsCycleForm;
        $this->dsCycleAddCompanyForm = $dsCycleAddCompanyForm;
    }

    public function index()
    {
        $canAddCycle = !DsCycle::hasOngoingCycle();

        return View::make('digital_star.cycles.index', compact('canAddCycle'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        //$user = \Confide::user();

        $model = DsCycle::select('id',
                'start_date',
                'end_date',
                'is_completed',
                'remarks');

        $rowCount = $model->count();

        $records = $model->orderBy('id', 'desc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->id,
                'counter' => $counter,
                'start_date' => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date' => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'completed' => $record->is_completed,
                'remarks' => $record->remarks,
                'route:edit' => route('digital-star.cycle.edit', [$record->id]),
                'route:setup' => route('digital-star.setups.index', ['cycle' => $record->id]),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function create()
    {
        return View::make('digital_star.cycles.create');
    }

    public function store()
    {
        $input = Input::all();

        $this->dsCycleForm->validate($input);

        $lastCycle = DsCycle::select('id')->orderBy('id', 'desc')->first();

        if ($lastCycle) {
            $totalEvaluations = DsEvaluation::select('ds_cycle_id')->where('ds_cycle_id', '=', $lastCycle->id)->count();
        } else {
            $totalEvaluations = 0;
        }

        // Cycle
        $cycle = DsCycle::create($input);

        // Weighted node for Company
        $weightedNodeCompany = WeightedNode::create([
            'name' => 'Company',
            'weight' => $input['weight_company'] ?? 0,
            'priority' => 1,
            'parent_id' => null,
        ]);
        DsCycleWeightedNode::create([
            'ds_cycle_id' => $cycle->id,
            'weighted_node_id' => $weightedNodeCompany->id,
            'type' => 'company',
        ]);

        // Weighted node for Project
        $weightedNodeProject = WeightedNode::create([
            'name' => 'Project',
            'weight' => $input['weight_project'] ?? 0,
            'priority' => 1,
            'parent_id' => null,
        ]);
        DsCycleWeightedNode::create([
            'ds_cycle_id' => $cycle->id,
            'weighted_node_id' => $weightedNodeProject->id,
            'type' => 'project'
        ]);

        DsCycleTemplateForm::initialize($cycle);

        if ($totalEvaluations > 0) {
            //set_time_limit(0);  // Remove execution time limit : Temporary solution as script takes a long time to complete.
            set_time_limit(86400); // 86400 seconds = 24 hours

            $skip = 0;
            $limit = 50;
            $pages = ceil($totalEvaluations / $limit);

            for ($i = 1; $i <= $pages; $i++) {
                $evaluations = DsEvaluation::where('ds_cycle_id', '=', $lastCycle->id)
                    ->skip($skip)
                    ->take($limit)
                    ->get();

                foreach ($evaluations as $evaluation) {
                    // Clone VPE record and set new cycle ID and dates
                    $newEvaluation = new DsEvaluation(array(
                        'ds_cycle_id' => $cycle->id,
                        'company_id' => $evaluation->company_id,
                        'start_date' => $cycle->start_date,
                        'end_date' => $cycle->end_date,
                    ));

                    $newEvaluation->save();
                }

                $skip += $limit;
                unset($evaluations);   // Clear the variable to free memory
            }
        }

        return Redirect::route('digital-star.cycle.edit', array($cycle->id));
    }

    public function edit($cycleId)
    {
        $cycle = DsCycle::find($cycleId);

        $cycle->start_date = Carbon::parse($cycle->start_date)->format(\Config::get('dates.submitted_at'));
        $cycle->end_date = Carbon::parse($cycle->end_date)->format(\Config::get('dates.submitted_at'));

        $weightage = ['company' => 0, 'project' => 0];
        $weightedNodes = $cycle->cycleWeightedNodes;

        $weightedNodeCompany = $weightedNodes->filter(function ($node) {
            return $node->type === 'company';
        })->first();
        if ($weightedNodeCompany) {
            $weightageCompany = $weightedNodeCompany->weightedNode;
            if ($weightageCompany) {
                $weightage['company'] = round($weightageCompany->weight);
            }
        }

        $weightedNodeProject = $weightedNodes->filter(function ($node) {
            return $node->type === 'project';
        })->first();
        if ($weightedNodeProject) {
            $weightageProject = $weightedNodeProject->weightedNode;
            if ($weightageProject) {
                $weightage['project'] = round($weightageProject->weight);
            }
        }

        return View::make('digital_star.cycles.edit', compact('cycle', 'weightage'));
    }

    public function update($cycleId)
    {
        $input = Input::all();

        $this->dsCycleForm->setUpdateMode();

        $this->dsCycleForm->validate($input);

        $cycle = DsCycle::find($cycleId);
        $moduleParameter = DsModuleParameter::first();
        if (is_null($moduleParameter->vendorManagementGrade)) {
            \Flash::error(trans('digitalStar/digitalStar.errorVendorManagementGradeNotSet'));
            return Redirect::back();
        }

        $transaction = new DBTransaction;

        $transaction->begin();

        try {
            $user = \Confide::user();

            $cycle->update($input);

            $cycle->evaluations()->where('status_id', '=', DsEvaluation::STATUS_DRAFT)->update(['updated_by' => $user->id]);
            $cycle->evaluations()->where('status_id', '!=', DsEvaluation::STATUS_COMPLETED)->update(['start_date' => $input['start_date'], 'end_date' => $input['end_date'], 'updated_by' => $user->id]);

            \Queue::push('PCK\DigitalStar\QueueJobs\StartAndEndDigitalStarCycles', [], 'default');

            // Store the weightage of company & project
            // For example: company = 60%, project = 40%
            $weightedNodes = $cycle->cycleWeightedNodes;
            foreach ($weightedNodes as $weightedNode) {
                $weight = $input['weight_' . $weightedNode->type];
                $weightedNode->weightedNode->update(['weight' => $weight]);
            }

            \Flash::success(trans('digitalStar/vendorManagement.formSavedSuccessfully'));

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \Flash::error(trans('digitalStar/vendorManagement.formValidationErrorChangesNotSaved'));
        }

        return Redirect::back();
    }

    public function assignedCompanies($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DsEvaluation::select(
                'companies.id as company_id',
                'companies.name as company_name',
                'contract_group_categories.name as vendor_group',
                'ds_evaluations.start_date',
                'ds_evaluations.end_date',
                'ds_evaluations.status_id'
            )
            ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
            ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
            ->where('ds_evaluations.ds_cycle_id', '=', $cycleId);

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'company_name':
                        if (strlen($val) > 0) {
                            $model->where('companies.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'vendor_group':
                        if (strlen($val) > 0) {
                            $model->where('contract_group_categories.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'start_date':
                        if (strlen($val) > 0) {
                            $model->where('ds_evaluations.start_date', 'ILKE', '%' . $val . '%');
                        }
                        break;

                    case 'end_date':
                        break;

                    case 'status':
                        if ((int)$val > 0) {
                            $model->where('ds_evaluations.status_id', '=', $val);
                        }
                        break;
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('ds_evaluations.start_date', 'asc')
            ->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;
            
            $data[] = [
                'id' => $record->company_id,
                'counter' => $counter,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
                'start_date' => Carbon::parse($record->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date' => Carbon::parse($record->end_date)->format(\Config::get('dates.submitted_at')),
                'can_delete' => $record->status_id != DsEvaluation::STATUS_COMPLETED,
                'status' => DsEvaluation::getStatusText($record->status_id),
                'route:remove_company' => route('digital-star.cycle.removeCompany', [$cycleId, $record->company_id]),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function unassignedCompanies($cycleId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $includedCompanyIds = DsEvaluation::has('company')
            ->where('ds_cycle_id', '=', $cycleId)
            ->lists('company_id');

        $model = Company::select('companies.name as company_name', 'companies.id as company_id', 'contract_group_categories.name as vendor_group', DB::RAW("ARRAY_TO_JSON(ARRAY_AGG(vendor_work_categories.name) FILTER (WHERE vendor_work_categories.name IS NOT NULL)) AS vendor_work_categories"))
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
                FROM vendor_registrations
                WHERE status = ".VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL
                GROUP BY company_id) vr"), 'vr.company_id', '=', 'companies.id')
            ->join(\DB::raw("(SELECT id as vr_final_id, company_id, revision
                FROM vendor_registrations
                WHERE status = ".VendorRegistration::STATUS_COMPLETED."
                AND deleted_at IS NULL) vr_final"), function($join){
                $join->on('vr_final.company_id', '=', 'vr.company_id');
                $join->on('vr_final.revision', '=', 'vr.revision');
            })
            ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
                FROM vendor_registrations
                WHERE deleted_at IS NULL
                GROUP BY company_id) vr_latest"), 'vr_latest.company_id', '=', 'companies.id')
            ->join(\DB::raw("(SELECT status, submission_type, company_id, revision
                FROM vendor_registrations
                WHERE deleted_at IS NULL) vr_status"), function($join){
                $join->on('vr_status.company_id', '=', 'vr_latest.company_id');
                $join->on('vr_status.revision', '=', 'vr_latest.revision');
            })
            ->leftJoin(\DB::raw("(SELECT vendor_registration_id, ROUND(AVG(vendor_pre_qualifications.score)) AS avg_score
                FROM vendor_pre_qualifications
                WHERE deleted_at IS NULL
                GROUP BY vendor_registration_id) preq"), 'preq.vendor_registration_id', '=', 'vr_final.vr_final_id'
            )
            ->leftJoin('vendors', function($join) {
                $join->on('vendors.company_id', '=', 'companies.id');
                $join->on(\DB::raw("vendors.type = " . Vendor::TYPE_ACTIVE), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin('vendor_work_categories', function($join) {
                $join->on('vendor_work_categories.id', '=', 'vendors.vendor_work_category_id');
                $join->on(\DB::raw('vendor_work_categories.hidden IS FALSE'), \DB::raw(''), \DB::raw(''));
            })
            ->where('companies.confirmed', '=', true)
            ->where('companies.expiry_date', '>', \DB::raw('NOW()'))    // Active vendors
            ->whereNull('companies.deactivated_at')
            ->whereNotNull('companies.activation_date')
            ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            //->where('contract_group_categories.vendor_type', '=', ContractGroupCategory::VENDOR_TYPE_CONTRACTOR)
            ->where('contract_group_categories.hidden', '=',  false)
            ->whereNotIn('companies.id', $includedCompanyIds);

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'company_name':
                        if (strlen($val) > 0) {
                            $model->where('companies.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'vendor_group':
                        if (strlen($val) > 0) {
                            $model->where('contract_group_categories.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;
                }
            }
        }

        $model->groupBy('companies.id', 'contract_group_categories.id');

        $rowCount = $model->count();

        $records = $model->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->company_id,
                'counter' => $counter,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
                'route:add_company' => route('digital-star.cycle.addCompany', [$cycleId, $record->company_id]),
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function addCompany($cycleId, $companyId)
    {
        $success = false;

        $transaction = new DBTransaction;

        $transaction->begin();

        try {
            $data = [
                'cycle_id' => $cycleId,
                'company_id' => $companyId,
            ];

            $this->dsCycleAddCompanyForm->validate($data);

            if ($this->dsCycleAddCompanyForm->success) {
                $cycle = DsCycle::find($cycleId);

                $dsEvaluation = DsEvaluation::create([
                                    'ds_cycle_id' => $cycleId,
                                    'company_id' => $companyId,
                                    'start_date' => $cycle->start_date,
                                    'end_date' => $cycle->end_date,
                                    'type' => Input::get('evaluation_type'),
                                ]);
                
                $transaction->commit();

                $success = true;
            }
        } catch (\Exception $e) {
            $transaction->rollback();

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        return array(
            'success' => $success,
            'errors' => $this->dsCycleAddCompanyForm->getErrorMessages(),
        );
    }

    public function removeCompany($cycleId, $companyId)
    {
        $success = false;
        $errorMsg = null;

        try {
            $evaluation = DsEvaluation::where('ds_cycle_id', '=', $cycleId)
                ->where('company_id', '=', $companyId)
                ->where('status_id', '!=', DsEvaluation::STATUS_COMPLETED)
                ->first();

            if ($evaluation) $evaluation->delete();

            $success = true;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();

            \Log::error($e->getMessage());
        }

        return array(
            'success' => $success,
            'errorMsg' => $errorMsg,
        );
    }
}