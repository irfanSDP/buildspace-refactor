<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaireOption;
use PCK\ConsultantManagement\ConsultantManagementExcludeQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaireOption;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaireReply;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpQuestionnaireReply;
use PCK\ConsultantManagement\ConsultantManagementConsultantReplyAttachment;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpReplyAttachment;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\VendorCategory\VendorCategory;

use PCK\Notifications\EmailNotifier;

use PCK\Helpers\ModuleAttachment;
use PCK\ObjectField\ObjectField;

use PCK\Forms\ConsultantManagement\QuestionnaireForm;
use PCK\Forms\ConsultantManagement\RfpQuestionnaireForm;

class ConsultantManagementQuestionnaireController extends \BaseController
{
    private $questionnaireForm;
    private $rfpQuestionnaireForm;
    private $emailNotifier;

    public function __construct(QuestionnaireForm $questionnaireForm, RfpQuestionnaireForm $rfpQuestionnaireForm, EmailNotifier $emailNotifier)
    {
        $this->questionnaireForm = $questionnaireForm;
        $this->rfpQuestionnaireForm = $rfpQuestionnaireForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementContract $consultantManagementContract)
    {
        $user = Confide::user();

        return View::make('consultant_management.questionnaires.general.index', compact('consultantManagementContract', 'user'));
    }

    public function generalList(ConsultantManagementContract $consultantManagementContract)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementQuestionnaire::select("consultant_management_questionnaires.id AS id", "consultant_management_questionnaires.question",
        "consultant_management_questionnaires.type", "consultant_management_questionnaires.required")
        ->where('consultant_management_questionnaires.consultant_management_contract_id', '=', $consultantManagementContract->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'question':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_questionnaires.question', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_questionnaires.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'question'     => trim($record->question),
                'type'         => $record->type,
                'type_txt'     => $record->getTypeText(),
                'required'     => $record->required,
                'created_at'   => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show'   => route('consultant.management.questionnaire.settings.show', [$consultantManagementContract->id, $record->id]),
                'route:update' => route('consultant.management.questionnaire.settings.edit', [$consultantManagementContract->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function generalShow(ConsultantManagementContract $consultantManagementContract, $questionnaireId)
    {
        $questionnaire = ConsultantManagementQuestionnaire::findOrFail((int)$questionnaireId);
        $user = \Confide::user();

        return View::make('consultant_management.questionnaires.general.show', compact('consultantManagementContract', 'questionnaire', 'user'));
    }

    public function generalCreate(ConsultantManagementContract $consultantManagementContract)
    {
        $questionnaire = null;
        $user  = \Confide::user();

        $typeList = [
            ConsultantManagementQuestionnaire::TYPE_TEXT => ConsultantManagementQuestionnaire::TYPE_TEXT_TEXT,
            ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY => ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY_TEXT,
            ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT => ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT_TEXT,
            ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT => ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT_TEXT
        ];

        return View::make('consultant_management.questionnaires.general.edit', compact('questionnaire', 'consultantManagementContract', 'user', 'typeList'));
    }

    public function generalEdit(ConsultantManagementContract $consultantManagementContract, $questionnaireId)
    {
        $questionnaire = ConsultantManagementQuestionnaire::findOrFail((int)$questionnaireId);
        $user  = \Confide::user();

        $typeList = [
            ConsultantManagementQuestionnaire::TYPE_TEXT => ConsultantManagementQuestionnaire::TYPE_TEXT_TEXT,
            ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY => ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY_TEXT,
            ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT => ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT_TEXT,
            ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT => ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT_TEXT
        ];

        return View::make('consultant_management.questionnaires.general.edit', compact('questionnaire', 'consultantManagementContract', 'user', 'typeList'));
    }

    public function generalStore(ConsultantManagementContract $consultantManagementContract)
    {
        $this->questionnaireForm->validate(Input::all());

        $user  = \Confide::user();
        $input = Input::all();

        $questionnaire = ConsultantManagementQuestionnaire::find($input['id']);

        if(!$questionnaire)
        {
            $questionnaire = new ConsultantManagementQuestionnaire();

            $questionnaire->consultant_management_contract_id = $consultantManagementContract->id;
            $questionnaire->created_by = $user->id;
        }

        $questionnaire->question        = trim($input['question']);
        $questionnaire->type            = $input['type'];
        $questionnaire->required        = $input['required'];
        $questionnaire->with_attachment = array_key_exists('with_attachment', $input);
        $questionnaire->updated_by      = $user->id;

        $questionnaire->save();

        $questionnaire->options()->delete();

        if($questionnaire->type == ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT or $questionnaire->type == ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT)
        {
            foreach($input['options'] as $idx => $fields)
            {
                $option = new ConsultantManagementQuestionnaireOption();

                $option->consultant_management_questionnaire_id = $questionnaire->id;
                $option->text                                   = trim($fields['text']);
                $option->order                                  = $idx;
                $option->created_by                             = $user->id;
                $option->updated_by                             = $user->id;

                $option->save();
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.questionnaire.settings.show', [$consultantManagementContract->id, $questionnaire->id]);
    }

    public function generalDelete(ConsultantManagementContract $contract, $questionnaireId)
    {
        $questionnaire = ConsultantManagementQuestionnaire::findOrFail((int)$questionnaireId);
        $user = \Confide::user();

        if($questionnaire->deletable())
        {
            $questionnaire->delete();

            \Log::info("Delete consultant management questionnaire [questionnaire id: {$questionnaireId}]][user id:{$user->id}]");
        }

        return Redirect::route('consultant.management.questionnaire.settings.index', [$contract->id]);
    }

    public function consultantRfpShow(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user = \Confide::user();
        $consultantQuestionnaire = ConsultantManagementConsultantQuestionnaire::where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('company_id', '=', $company->id)
        ->first();

        return View::make('consultant_management.questionnaires.consultant.show', compact('vendorCategoryRfp', 'company', 'consultantQuestionnaire', 'user'));
    }

    public function consultantGeneralList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementQuestionnaire::select("consultant_management_questionnaires.id AS id", "consultant_management_questionnaires.question",
        "consultant_management_questionnaires.type", "consultant_management_questionnaires.required", "consultant_management_exclude_questionnaires.id AS exclude_id")
        ->leftJoin('consultant_management_exclude_questionnaires', function($join) use($vendorCategoryRfp, $company){
            $join->on('consultant_management_exclude_questionnaires.consultant_management_questionnaire_id', '=', 'consultant_management_questionnaires.id');
            $join->on('consultant_management_exclude_questionnaires.vendor_category_rfp_id','=', \DB::raw($vendorCategoryRfp->id));
            $join->on('consultant_management_exclude_questionnaires.company_id','=', \DB::raw($company->id));
        })
        ->where('consultant_management_questionnaires.consultant_management_contract_id', '=', $vendorCategoryRfp->consultant_management_contract_id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'question':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_questionnaires.question', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_questionnaires.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'             => $record->id,
                'counter'        => $counter,
                'question'       => trim($record->question),
                'type'           => $record->type,
                'type_txt'       => $record->getTypeText(),
                'required'       => $record->required,
                'exclude'        => ($record->exclude_id) ? 'yes' : 'no',
                'created_at'     => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show'     => route('consultant.management.consultant.questionnaire.general.show', [$vendorCategoryRfp->id, $company->id, $record->id]),
                'route:exclude'  => route('consultant.management.consultant.questionnaire.general.exclude', [$vendorCategoryRfp->id, $company->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function generalExclude(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();
        $input   = Input::all();

        $questionnaire = ConsultantManagementQuestionnaire::findOrFail($input['id']);

        $success = false;
        $excludeQuestionnaire = null;

        switch($input['field'])
        {
            case 'exclude':
                $excludeQuestionnaire = ConsultantManagementExcludeQuestionnaire::where('consultant_management_questionnaire_id', '=', $questionnaire->id)
                ->where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
                ->where('company_id', '=', $company->id)
                ->first();

                if($input['val']=='yes' && !$excludeQuestionnaire)
                {
                    $excludeQuestionnaire = new ConsultantManagementExcludeQuestionnaire;

                    $excludeQuestionnaire->consultant_management_questionnaire_id = $questionnaire->id;
                    $excludeQuestionnaire->vendor_category_rfp_id = $vendorCategoryRfp->id;
                    $excludeQuestionnaire->company_id = $company->id;
                    $excludeQuestionnaire->created_by = $user->id;
                    $excludeQuestionnaire->updated_by = $user->id;

                    $excludeQuestionnaire->save();
                }
                elseif($input['val']=='no' && $excludeQuestionnaire)
                {
                    $excludeQuestionnaire->delete();

                    $excludeQuestionnaire = null;
                }

                $success = true;
                break;
        }

        return Response::json([
            'updated' => $success,
            'item' => [
                'id' => $questionnaire->id,
                'exclude' => ($excludeQuestionnaire) ? 'yes' : 'no'
            ]
        ]);
    }

    public function consultantGeneralShow(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId, $questionnaireId)
    {
        $company = Company::findOrFail((int)$companyId);
        $questionnaire = ConsultantManagementQuestionnaire::findOrFail((int)$questionnaireId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $user = \Confide::user();

        return View::make('consultant_management.questionnaires.general.show', compact('vendorCategoryRfp', 'consultantManagementContract', 'questionnaire', 'company', 'user'));
    }

    public function consultantRfpList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementRfpQuestionnaire::select("consultant_management_rfp_questionnaires.id AS id", "consultant_management_rfp_questionnaires.question",
        "consultant_management_rfp_questionnaires.type", "consultant_management_rfp_questionnaires.required", "consultant_management_rfp_questionnaires.created_at")
        ->where('consultant_management_rfp_questionnaires.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('consultant_management_rfp_questionnaires.company_id', '=', $company->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'question':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_rfp_questionnaires.question', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_rfp_questionnaires.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'question'     => trim($record->question),
                'type'         => $record->type,
                'type_txt'     => $record->getTypeText(),
                'required'     => $record->required,
                'created_at'   => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show'   => route('consultant.management.consultant.questionnaire.rfp.show', [$vendorCategoryRfp->id, $company->id, $record->id]),
                'route:update' => route('consultant.management.consultant.questionnaire.rfp.edit', [$vendorCategoryRfp->id, $company->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function rfpShow(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId, $questionnaireId)
    {
        $questionnaire                = ConsultantManagementRfpQuestionnaire::findOrFail((int)$questionnaireId);
        $company                      = Company::findOrFail((int)$companyId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $user                         = \Confide::user();

        return View::make('consultant_management.questionnaires.consultant.rfp_show', compact('vendorCategoryRfp', 'consultantManagementContract', 'questionnaire', 'company', 'user'));
    }

    public function rfpCreate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company                      = Company::findOrFail((int)$companyId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $questionnaire                = null;
        $user                         = \Confide::user();

        $typeList = [
            ConsultantManagementRfpQuestionnaire::TYPE_TEXT => ConsultantManagementRfpQuestionnaire::TYPE_TEXT_TEXT,
            ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY => ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY_TEXT,
            ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT => ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT_TEXT,
            ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT => ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT_TEXT
        ];

        return View::make('consultant_management.questionnaires.consultant.edit', compact('questionnaire', 'vendorCategoryRfp', 'consultantManagementContract', 'company', 'user', 'typeList'));
    }

    public function rfpEdit(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId, $questionnaireId)
    {
        $questionnaire                = ConsultantManagementRfpQuestionnaire::findOrFail((int)$questionnaireId);
        $company                      = Company::findOrFail((int)$companyId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $user                         = \Confide::user();

        $typeList = [
            ConsultantManagementRfpQuestionnaire::TYPE_TEXT => ConsultantManagementRfpQuestionnaire::TYPE_TEXT_TEXT,
            ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY => ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY_TEXT,
            ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT => ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT_TEXT,
            ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT => ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT_TEXT
        ];

        return View::make('consultant_management.questionnaires.consultant.edit', compact('questionnaire', 'vendorCategoryRfp', 'consultantManagementContract', 'company', 'user', 'typeList'));
    }

    public function rfpStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->rfpQuestionnaireForm->validate(Input::all());

        $input   = Input::all();
        $company = Company::findOrFail((int)$input['cid']);
        $user    = \Confide::user();

        $questionnaire = ConsultantManagementRfpQuestionnaire::find($input['id']);

        if(!$questionnaire)
        {
            $questionnaire = new ConsultantManagementRfpQuestionnaire();

            $questionnaire->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $questionnaire->company_id             = $company->id;
            $questionnaire->created_by             = $user->id;
        }

        $questionnaire->question        = trim($input['question']);
        $questionnaire->type            = $input['type'];
        $questionnaire->required        = $input['required'];
        $questionnaire->with_attachment = array_key_exists('with_attachment', $input);
        $questionnaire->updated_by      = $user->id;

        $questionnaire->save();

        $questionnaire->options()->delete();

        if($questionnaire->type == ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT or $questionnaire->type == ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT)
        {
            foreach($input['options'] as $idx => $fields)
            {
                $option = new ConsultantManagementRfpQuestionnaireOption();

                $option->consultant_management_rfp_questionnaire_id = $questionnaire->id;
                $option->text                                       = trim($fields['text']);
                $option->order                                      = $idx;
                $option->created_by                                 = $user->id;
                $option->updated_by                                 = $user->id;

                $option->save();
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.consultant.questionnaire.rfp.show', [$vendorCategoryRfp->id, $company->id, $questionnaire->id]);
    }

    public function rfpDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId, $questionnaireId)
    {
        $questionnaire = ConsultantManagementRfpQuestionnaire::findOrFail((int)$questionnaireId);
        $company       = Company::findOrFail((int)$companyId);
        $user          = \Confide::user();

        if($questionnaire->deletable())
        {
            $questionnaire->delete();

            \Log::info("Delete consultant management rfp questionnaire [questionnaire id: {$questionnaireId}]][company id: {$company->id}][user id:{$user->id}]");
        }

        return Redirect::route('consultant.management.consultant.questionnaire.show', [$vendorCategoryRfp->id, $company->id]);
    }

    public function publish(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        $company = Company::findOrFail((int)$request->get('cid'));
        $user    = \Confide::user();

        $consultantQuestionnaire = ConsultantManagementConsultantQuestionnaire::where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if(!$consultantQuestionnaire)
        {
            $consultantQuestionnaire = new ConsultantManagementConsultantQuestionnaire();

            $consultantQuestionnaire->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $consultantQuestionnaire->company_id             = $company->id;
            $consultantQuestionnaire->status                 = ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED;
            $consultantQuestionnaire->published_date         = date('Y-m-d H:i:s');
            $consultantQuestionnaire->created_by             = $user->id;
        }
        else
        {
            if($consultantQuestionnaire->status == ConsultantManagementConsultantQuestionnaire::STATUS_UNPUBLISHED)
            {
                $consultantQuestionnaire->status         = ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED;
                $consultantQuestionnaire->published_date = date('Y-m-d H:i:s');
            }
            else
            {
                $consultantQuestionnaire->status           = ConsultantManagementConsultantQuestionnaire::STATUS_UNPUBLISHED;
                $consultantQuestionnaire->unpublished_date = date('Y-m-d H:i:s');
            }
        }

        $consultantQuestionnaire->updated_by = $user->id;

        $consultantQuestionnaire->save();

        if($consultantQuestionnaire->status == ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        {
            $contract = $vendorCategoryRfp->consultantManagementContract;

            $recipients = User::select('users.*')
                ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
                AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
                AND consultant_management_user_roles.editor IS TRUE
                AND users.confirmed IS TRUE
                AND users.account_blocked_status IS FALSE')
                ->groupBy('users.id')
                ->get();

            if(!empty($recipients))
            {
                $content = [
                    'subject' => "Consultant Management - Questionnaire Published  (".$contract->Subsidiary->name.")",//need to move this to i10n
                    'view' => 'consultant_management.email.questionnaire',
                    'data' => [
                        'developmentPlanningTitle' => $contract->title,
                        'subsidiaryName' => $contract->Subsidiary->name,
                        'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                        'companyName' => $company->name,
                        'actionName' => 'published',
                        'creator' => $user->name,
                        'forConsultant' => false,
                        'route' => route('consultant.management.consultant.questionnaire.show', [$vendorCategoryRfp->id, $company->id])
                    ]
                ];

                $this->emailNotifier->sendGeneralEmail($content, $recipients);
            }

            $consultantUsers = User::select('users.*')
                ->join('consultant_management_consultant_users', 'users.id', '=', 'consultant_management_consultant_users.user_id')
                ->whereRaw('users.company_id = '.$company->id.'
                AND users.confirmed IS TRUE
                AND users.account_blocked_status IS FALSE')
                ->groupBy('users.id')
                ->get();
            
            if(!empty($consultantUsers))
            {
                $content = [
                    'subject' => "Consultant Management - Questionnaire Published  (".$contract->Subsidiary->name.")",//need to move this to i10n
                    'view' => 'consultant_management.email.questionnaire',
                    'data' => [
                        'developmentPlanningTitle' => $contract->title,
                        'subsidiaryName' => $contract->Subsidiary->name,
                        'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                        'companyName' => $company->name,
                        'actionName' => 'published',
                        'creator' => $user->name,
                        'forConsultant' => true,
                        'route' => route('consultant.management.consultant.rfp.questionnaire.show', [$consultantQuestionnaire->id])
                    ]
                ];

                $this->emailNotifier->sendGeneralEmail($content, $consultantUsers);
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.consultant.questionnaire.show', [$vendorCategoryRfp->id, $company->id]);
    }

    public function consultantRfpReplies(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();

        $consultantQuestionnaires = ConsultantManagementConsultantQuestionnaire::where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('company_id', '=', $company->id)
        ->first();

        $records = [];

        if($consultantQuestionnaires)
        {
            $counter = 1;

            $generalQuestions = $consultantQuestionnaires->generalQuestions();

            $replies = ConsultantManagementConsultantQuestionnaireReply::select('consultant_management_consultant_questionnaire_replies.consultant_management_questionnaire_id',
            'consultant_management_consultant_questionnaire_replies.consultant_management_consultant_questionnaire_id', 'consultant_management_consultant_questionnaire_replies.created_at',
            'consultant_management_consultant_questionnaire_replies.text', 'consultant_management_questionnaire_options.text AS option_text')
            ->leftJoin('consultant_management_questionnaire_options', function($join){
                $join->on('consultant_management_questionnaire_options.consultant_management_questionnaire_id', '=', 'consultant_management_consultant_questionnaire_replies.consultant_management_questionnaire_id');
                $join->on('consultant_management_questionnaire_options.id','=', 'consultant_management_consultant_questionnaire_replies.consultant_management_questionnaire_option_id');
            })
            ->where('consultant_management_consultant_questionnaire_replies.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaires->id)
            ->orderBy('consultant_management_consultant_questionnaire_replies.consultant_management_questionnaire_id', 'DESC')
            ->orderBy('consultant_management_questionnaire_options.id', 'ASC')
            ->get();

            $attachments = ConsultantManagementConsultantReplyAttachment::select(\DB::raw('consultant_management_consultant_reply_attachments.consultant_management_questionnaire_id,
            COUNT(uploads.id) AS count'))
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_reply_attachments.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaires->id)
            ->groupBy('consultant_management_consultant_reply_attachments.consultant_management_questionnaire_id')
            ->lists('count', 'consultant_management_questionnaire_id');
            
            foreach($generalQuestions as $record)
            {
                $data = [
                    'id'                     => 'gen-'.$record->id,
                    'record_id'              => $record->id,
                    'record_type'            => 'general',
                    'counter'                => $counter,
                    'question'               => trim($record->question),
                    'type'                   => $record->type,
                    'type_txt'               => $record->getTypeText(),
                    'replies'                => [],
                    'with_attachment'        => $record->with_attachment,
                    'attachment_count'       => array_key_exists($record->id, $attachments) ? $attachments[$record->id] : 0,
                    'submitted_date'         => '-',
                    'required'               => $record->required,
                    'route:attachment-list'  => $record->with_attachment ? route('consultant.management.consultant.questionnaire.rfp.attachments', [$vendorCategoryRfp->id, $company->id]) : ''
                ];

                $replyCount = 1;
                foreach($replies as $idx => $reply)
                {
                    if($reply->consultant_management_questionnaire_id == $record->id)
                    {
                        if($record->type == ConsultantManagementQuestionnaire::TYPE_TEXT or $record->type == ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY)
                        {
                            $replyTxt = mb_strlen(trim($reply->text)) ? trim($reply->text) : "-";
                        }
                        else
                        {
                            $replyTxt = mb_strlen(trim($reply->option_text)) ? $replyCount.'. '.trim($reply->option_text) : "-";
                        }

                        $data['replies'][] = $replyTxt;
                        $data['submitted_date'] = Carbon::parse($reply->created_at)->format('d/m/Y H:i:s');

                        $replyCount++;
                        unset($replies[$idx]);
                    }
                }

                $records[] = $data;
                
                $counter++;
            }

            $rfpQuestions = $consultantQuestionnaires->rfpQuestions();

            $replies = ConsultantManagementConsultantRfpQuestionnaireReply::select('consultant_management_consultant_rfp_questionnaire_replies.consultant_management_rfp_questionnaire_id',
            'consultant_management_consultant_rfp_questionnaire_replies.consultant_management_consultant_questionnaire_id',
            'consultant_management_consultant_rfp_questionnaire_replies.created_at',
            'consultant_management_consultant_rfp_questionnaire_replies.text', 'consultant_management_rfp_questionnaire_options.text AS option_text')
            ->leftJoin('consultant_management_rfp_questionnaire_options', function($join){
                $join->on('consultant_management_rfp_questionnaire_options.consultant_management_rfp_questionnaire_id', '=', 'consultant_management_consultant_rfp_questionnaire_replies.consultant_management_rfp_questionnaire_id');
                $join->on('consultant_management_rfp_questionnaire_options.id','=', 'consultant_management_consultant_rfp_questionnaire_replies.consultant_management_rfp_questionnaire_option_id');
            })
            ->where('consultant_management_consultant_rfp_questionnaire_replies.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaires->id)
            ->orderBy('consultant_management_consultant_rfp_questionnaire_replies.consultant_management_rfp_questionnaire_id', 'DESC')
            ->get();

            $attachments = ConsultantManagementConsultantRfpReplyAttachment::select(\DB::raw('consultant_management_consultant_rfp_reply_attachments.consultant_management_rfp_questionnaire_id,
            COUNT(uploads.id) AS count'))
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_rfp_reply_attachments.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaires->id)
            ->groupBy('consultant_management_consultant_rfp_reply_attachments.consultant_management_rfp_questionnaire_id')
            ->lists('count', 'consultant_management_rfp_questionnaire_id');

            foreach($rfpQuestions as $record)
            {
                $data = [
                    'id'                     => 'rfp-'.$record->id,
                    'record_id'              => $record->id,
                    'record_type'            => 'rfp',
                    'counter'                => $counter,
                    'question'               => trim($record->question),
                    'type'                   => $record->type,
                    'type_txt'               => $record->getTypeText(),
                    'replies'                => [],
                    'with_attachment'        => $record->with_attachment,
                    'attachment_count'       => array_key_exists($record->id, $attachments) ? $attachments[$record->id] : 0,
                    'submitted_date'         => '-',
                    'required'               => $record->required,
                    'route:attachment-list'  => $record->with_attachment ? route('consultant.management.consultant.questionnaire.rfp.attachments', [$vendorCategoryRfp->id, $company->id]) : ''
                ];

                $replyCount = 1;
                foreach($replies as $idx => $reply)
                {
                    if($reply->consultant_management_rfp_questionnaire_id == $record->id)
                    {
                        if($record->type == ConsultantManagementRfpQuestionnaire::TYPE_TEXT or $record->type == ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY)
                        {
                            $replyTxt = mb_strlen(trim($reply->text)) ? trim($reply->text) : "-";
                        }
                        else
                        {
                            $replyTxt = mb_strlen(trim($reply->option_text)) ? $replyCount.'. '.trim($reply->option_text) : "-";
                        }

                        $data['replies'][] = $replyTxt;
                        $data['submitted_date'] = Carbon::parse($reply->created_at)->format('d/m/Y H:i:s');

                        $replyCount++;
                        unset($replies[$idx]);
                    }
                }

                $records[] = $data;
                
                $counter++;
            }
        }

        return Response::json($records);
    }

    public function consultantAttachmentList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();

        $request = Request::instance();
        
        $consultantQuestionnaire = ConsultantManagementConsultantQuestionnaire::where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if(!$consultantQuestionnaire)
        {
            return Response::json([]);
        }

        $question = null;

        if($request->get('type')=='general')
        {
            $question = ConsultantManagementQuestionnaire::findOrFail($request->get('id'));

            $model = ConsultantManagementConsultantReplyAttachment::select('uploads.id AS id', 'uploads.filename', 'uploads.extension', 'uploads.created_at AS uploaded_at',
            'consultant_management_consultant_reply_attachments.id AS reply_id')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_reply_attachments.consultant_management_questionnaire_id', '=', $question->id)
            ->where('consultant_management_consultant_reply_attachments.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id);
        }
        elseif($request->get('type')=='rfp')
        {
            $question = ConsultantManagementRfpQuestionnaire::findOrFail($request->get('id'));

            $model = ConsultantManagementConsultantRfpReplyAttachment::select('uploads.id', 'uploads.filename', 'uploads.extension', 'uploads.created_at',
            'consultant_management_consultant_rfp_reply_attachments.id AS reply_id')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_rfp_reply_attachments.consultant_management_rfp_questionnaire_id', '=', $question->id)
            ->where('consultant_management_consultant_rfp_reply_attachments.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id);
        }

        $uploadedFiles = $model->orderBy('uploads.filename', 'asc')->get();

        $attachments = [];
        
        foreach($uploadedFiles as $uploadedFile)
        {
            $attachments[] = [
                'id'             => $uploadedFile->id,
                'title'          => trim($uploadedFile->filename),
                'type'           => 'file',
                'extension'      => $uploadedFile->extension,
                'uploaded_at'    => Carbon::parse($uploadedFile->uploaded_at)->format('d/m/Y H:i:s'),
                'route:download' => route('consultant.management.consultant.rfp.questionnaire.attachments.download', [$uploadedFile->id])
            ];
        }

        return Response::json($attachments);
    }
}