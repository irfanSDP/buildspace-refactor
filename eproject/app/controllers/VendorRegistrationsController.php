<?php

use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\DynamicFormRepository;

class VendorRegistrationsController extends Controller
{
    private $formRepository;

    public function __construct(DynamicFormRepository $formRepository)
    {
        $this->formRepository = $formRepository;
    }

    public function index()
    {
        $moduleIdentifier = DynamicForm::VENDOR_REGISTRATION_IDENTIFIER;
        $getFormsRoute    = route('vendor.registration.forms.get');

        return View::make('form_builder.index', [
            'moduleIdentifier' => $moduleIdentifier,
            'getFormsRoute'    => $getFormsRoute,
        ]);
    }

    public function getVendorRegistrationForms()
    {
        $formTemplates = $this->formRepository->getAllFormTemplates(DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        return Response::json($formTemplates);
    }
}