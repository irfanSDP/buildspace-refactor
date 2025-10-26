<?php

use PCK\Helpers\DBTransaction;
use PCK\FormBuilder\DynamicFormRepository;
use PCK\Forms\DynamicFormForm;
use PCK\Forms\DynamicFormSubmitForm;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\FormColumn;
use PCK\FormBuilder\FormColumnSection;
use PCK\Exceptions\ValidationException;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use PCK\Verifier\Verifier;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ObjectLog\ObjectLog;
use PCK\FormBuilder\FormColumnRepository;

class DynamicFormsController extends Controller
{
    private $formRepository;
    private $form;
    private $submitForm;
    private $formColumnRepository;

    public function __construct(DynamicFormRepository $formRepository, DynamicFormForm $form, DynamicFormSubmitForm $submitForm, FormColumnRepository $formColumnRepository)
    {
        $this->formRepository       = $formRepository;
        $this->form                 = $form;
        $this->submitForm           = $submitForm;
        $this->formColumnRepository = $formColumnRepository;
    }

    public function store()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->formRepository->store($inputs);

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function createNewRevision($formId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $form = DynamicForm::find($formId);

            $this->formRepository->createNewRevision($form);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function update($formId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->formRepository->update($formId, $inputs);

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function clone($formId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);
            $form = DynamicForm::find($formId);

            $this->formRepository->clone($form, $inputs);

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $transaction->rollback();
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }

        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function delete($formId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $form = DynamicForm::find($formId);
            $form->delete();
    
            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function show($formId)
    {
        $user = \Confide::user();
        $form = DynamicForm::find($formId);

        $canEditFormDesign    = $form->isOpenForEditing();
        $canApproveFormDesign = $form->isDesignPendingForApproval() && Verifier::isCurrentVerifier($user, $form);

        return View::make('form_builder.form_designer', [
            'form'                 => $form,
            'canEditFormDesign'    => $canEditFormDesign,
            'canApproveFormDesign' => $canApproveFormDesign,
            'hasRejectedElements'  => $form->hasRejectedElements(),
            'formDesignApprovers'  => $form->getFormDesignApprovers(),
        ]);
    }

    public function submitFormSection($sectionId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $user     = \Confide::user();
            $section  = FormColumnSection::find($sectionId);
            $formData = $this->submitForm->formDataCleanUp($inputs);
            $formData['sectionId'] = $section->id;

            $this->submitForm->validate($formData);

            $vendorRegistration = VendorRegistration::find($section->column->dynamicForm->formObjectMapping->object_id);

            $isVendor = ($user->company->id == $vendorRegistration->company->id);

            $this->formRepository->saveSectionInputs($section, $inputs, $isVendor);

            if( ! $isVendor )
            {
                ObjectLog::recordAction($vendorRegistration, ObjectLog::ACTION_EDIT, ObjectLog::MODULE_VENDOR_REGISTRATION_REGISTRATION_FORM);
            }

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $transaction->rollback();
            $errors = $e->getMessageBag();
        }

        return Response::json([
            'success'   => $success,
            'errors'    => $errors,
        ]);
    }

    public function getPreviousRevisionForms($formId)
    {
        $form                  = DynamicForm::find($formId);
        $previousRevisionForms = $this->formRepository->getPreviousRevisionForms($form);

        return Response::json($previousRevisionForms);
    }
    
    public function submitFormDesignForApproval($formId)
    {
        $inputs = Input::all();
        $form   = DynamicForm::find($formId);

        $verifiers = array_unique(array_filter($inputs['verifiers'], function($id) {
            return (trim($id) != '');
        }));

        Verifier::setVerifiers($verifiers, $form);

        $form->status                    = DynamicForm::STATUS_DESIGN_PENDING_FOR_APPROVAL;
        $form->submitted_for_approval_by = \Confide::user()->id;
        $form->save();

        $form = DynamicForm::find($form->id);

        Verifier::sendPendingNotification($form);

        return Redirect::to($form->getIndexRouteByIdentifier());
    }

    public function getFormContents($formId)
    {
        $form = DynamicForm::find($formId);
        $data = [];
        
        foreach($form->columns()->orderBy('priority', 'ASC')->get() as $column)
        {
            array_push($data, [
                'id'                   => $column->id,
                'name'                 => $column->name,
                'contents'             => $this->formColumnRepository->getColumnContents($column, true),
                'route_update_column'  => route('form.column.update', [$column->id]),
                'route_delete_column'  => route('form.column.delete', [$column->id]),
                'route_new_subsection' => route('form.column.section.store', [$column->id]),
                'route_swap_section'   => route('form.column.section.swap', [$column->id]),
            ]);
        }

        return Response::json($data);
    }

    public function getVendorFormContents($formId)
    {
        $form = DynamicForm::find($formId);
        $data = [];
        
        foreach($form->columns()->orderBy('priority', 'ASC')->get() as $column)
        {
            array_push($data, [
                'id'       => $column->id,
                'name'     => $column->name,
                'contents' => $this->formColumnRepository->getColumnContents($column),
            ]);
        }

        return Response::json($data);
    }
}

