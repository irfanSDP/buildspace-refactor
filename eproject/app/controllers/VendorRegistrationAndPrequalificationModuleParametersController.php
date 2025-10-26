<?php

use PCK\Forms\VendorRegistrationAndPrequalificationModuleParameterForm;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameterRepository;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;

class VendorRegistrationAndPrequalificationModuleParametersController extends Controller
{
    private $repository;
    private $form;

    public function __construct(VendorRegistrationAndPrequalificationModuleParameterRepository $repository, VendorRegistrationAndPrequalificationModuleParameterForm $form)
    {
        $this->repository = $repository;
        $this->form       = $form;
    }

    public function edit()
    {
        $record = VendorRegistrationAndPrequalificationModuleParameter::first();

        $gradingSystems = VendorManagementGrade::getGradeTemplates()->lists('name', 'id');

        return View::make('module_parameters.vendor_registration_and_prequalification.edit', [
            'record'         => $record,
            'gradingSystems' => $gradingSystems
        ]);
    }

    public function update()
    {
        $inputs  = Input::all();

        $this->form->validate($inputs);
        $this->repository->update($inputs);
            
        Flash::success(trans('vendorManagement.formSavedSuccessfully'));

        return Redirect::back();
    }
}