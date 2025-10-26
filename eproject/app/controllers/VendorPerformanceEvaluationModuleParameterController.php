<?php

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameterRepository;
use PCK\Forms\VendorPerformanceEvaluationModuleParameterForm;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationSubmissionReminderSetting;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;

class VendorPerformanceEvaluationModuleParameterController extends Controller
{
    private $vendorPerformanceEvaluationModuleParameterRepository;
    private $form;

    public function __construct(VendorPerformanceEvaluationModuleParameterRepository $vendorPerformanceEvaluationModuleParameterRepository, VendorPerformanceEvaluationModuleParameterForm $form)
    {
        $this->vendorPerformanceEvaluationModuleParameterRepository = $vendorPerformanceEvaluationModuleParameterRepository;
        $this->form                                                 = $form;
    }

    public function edit()
    {
        $record = VendorPerformanceEvaluationModuleParameter::first();

        $submissionReminders = VendorPerformanceEvaluationSubmissionReminderSetting::orderby('number_of_days_before', 'asc')->lists('number_of_days_before', 'number_of_days_before');

        $gradingSystems = VendorManagementGrade::getGradeTemplates()->lists('name', 'id');

        return View::make('module_parameters.vendor_performance_evaluation.edit', [
            'record'              => $record,
            'submissionReminders' => $submissionReminders,
            'gradingSystems'      => $gradingSystems,
        ]);
    }

    public function update()
    {
        $inputs  = Input::all();

        $this->form->validate($inputs);

        $this->vendorPerformanceEvaluationModuleParameterRepository->update($inputs);

        Flash::success(trans('vendorManagement.formSavedSuccessfully'));

        return Redirect::back();
    }
}