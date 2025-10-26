<?php

namespace DigitalStar;

use PCK\DigitalStar\Forms\DsModuleParameterForm;
use PCK\DigitalStar\ModuleParameters\DsModuleParameter;
use PCK\DigitalStar\ModuleParameters\DsModuleParameterRepository;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;

class DsModuleParameterController extends \Controller
{
    private $dsModuleParameterRepository;
    private $form;

    public function __construct(
        DsModuleParameterRepository $dsModuleParameterRepository,
        DsModuleParameterForm $form
    ) {
        $this->dsModuleParameterRepository = $dsModuleParameterRepository;
        $this->form = $form;
    }

    public function edit()
    {
        $record = DsModuleParameter::first();

        $gradingSystems = VendorManagementGrade::getGradeTemplates()->lists('name', 'id');

        $unitDescriptions = DsModuleParameter::getUnitDescription();

        return \View::make('digital_star.module_parameters.edit', [
            'record' => $record,
            'gradingSystems' => $gradingSystems,
            'unitDescriptions' => $unitDescriptions,
        ]);
    }

    public function update()
    {
        $inputs = \Input::all();

        $this->form->validate($inputs);

        $this->dsModuleParameterRepository->update($inputs);

        \Flash::success(trans('digitalStar/vendorManagement.formSavedSuccessfully'));

        return \Redirect::back();
    }
}