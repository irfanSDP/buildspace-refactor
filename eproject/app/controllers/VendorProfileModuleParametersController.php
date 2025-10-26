<?php

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameterRepository;
use PCK\Forms\VendorProfileModuleParameterForm;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;

class VendorProfileModuleParametersController extends Controller
{
    private $vendorProfileModuleParameterRepository;
    private $form;

    public function __construct(VendorProfileModuleParameterRepository $vendorProfileModuleParameterRepository, VendorProfileModuleParameterForm $form)
    {
        $this->vendorProfileModuleParameterRepository = $vendorProfileModuleParameterRepository;
        $this->form                                   = $form;
    }

    public function edit()
    {
        $record = VendorProfileModuleParameter::first();

        return View::make('module_parameters.vendor_profile.edit', [
            'record' => $record,
        ]);
    }

    public function update()
    {
        $inputs  = Input::all();

        $this->form->validate($inputs);
        $this->vendorProfileModuleParameterRepository->update($inputs);

        Flash::success(trans('vendorManagement.formSavedSuccessfully'));

        return Redirect::back();
    }
}