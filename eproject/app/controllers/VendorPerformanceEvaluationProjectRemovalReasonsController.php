<?php

use PCK\VendorPerformanceEvaluation\ProjectRemovalReason;
use PCK\Forms\VendorPerformanceEvaluationProjectRemovalReasonForm;
use PCK\Settings\SystemSettings;

class VendorPerformanceEvaluationProjectRemovalReasonsController extends \BaseController {

    protected $vendorPerformanceEvaluationProjectRemovalReasonForm;

    public function __construct(VendorPerformanceEvaluationProjectRemovalReasonForm $vendorPerformanceEvaluationProjectRemovalReasonForm)
    {
        $this->vendorPerformanceEvaluationProjectRemovalReasonForm = $vendorPerformanceEvaluationProjectRemovalReasonForm;
    }

    public function index()
    {
        $data = [];

        $records = ProjectRemovalReason::orderBy('name', 'asc')
            ->get();

        $recordIds = [];
        $hiddenIds = [];

        foreach($records as $record)
        {
            $data[] = [
                'id'           => $record->id,
                'name'         => $record->name,
                'hidden'       => $record->hidden,
            ];

            $recordIds[] = $record->id;
            if($record->hidden) $hiddenIds[] = $record->id;
        }

        $data[] = [
            'id'     => 'others',
            'name'   => trans('forms.othersPleaseSpecify'),
            'hidden' => ! SystemSettings::getValue('allow_other_vpe_project_removal_reasons'),
        ];

        $recordIds[] = 'others';
        if(! SystemSettings::getValue('allow_other_vpe_project_removal_reasons')) $hiddenIds[] = 'others';

        return View::make('vendor_performance_evaluation.project_removal_reasons.index', compact('data', 'recordIds', 'hiddenIds'));
    }

    public function create()
    {
        return View::make('vendor_performance_evaluation.project_removal_reasons.create');
    }

    public function store()
    {
        $this->vendorPerformanceEvaluationProjectRemovalReasonForm->validate(Input::all());

        ProjectRemovalReason::create(Input::all());

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorPerformanceEvaluation.projectRemovalReasons.index');
    }

    public function update()
    {
        $input = Input::get('id') ?? [];

        SystemSettings::setValue('allow_other_vpe_project_removal_reasons', !isset($input['others']));

        unset($input['others']);

        ProjectRemovalReason::whereIn('id', $input)->update(array('hidden' => true));
        ProjectRemovalReason::whereNotIn('id', $input)->update(array('hidden' => false));

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorPerformanceEvaluation.projectRemovalReasons.index');
    }
}