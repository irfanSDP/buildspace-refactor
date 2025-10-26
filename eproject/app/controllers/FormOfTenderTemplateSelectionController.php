<?php

use PCK\FormOfTender\FormOfTenderTemplateSelectionRepository;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Forms\FormOfTenderTemplateForm;
use PCK\FormOfTender\FormOfTender;

class FormOfTenderTemplateSelectionController extends BaseController
{
    private $templateSelectionRepository;
    private $formOfTenderRepository;
    private $form;

    public function __construct(
        FormOfTenderTemplateSelectionRepository $templateSelectionRepository,  
        FormOfTenderRepository $formOfTenderRepository,
        FormOfTenderTemplateForm $form)
    {
        $this->templateSelectionRepository = $templateSelectionRepository;
        $this->formOfTenderRepository = $formOfTenderRepository;
        $this->form = $form;
    }

    public function index()
    {
        return View::make('form_of_tender.template_selection.index');
    }

    public function getAllTemplates()
    {
        $templates = $this->templateSelectionRepository->getAllTemplates();

        return Response::json($templates);
    }

    public function store()
    {
        $inputs = Input::all();

        $errors = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->formOfTenderRepository->createNewTemplate($inputs);

            $success = true;
        }
        catch(\PCK\Exceptions\ValidationException $e)
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

    public function update($templateId)
    {
        $errors = null;
        $success = false;
        $inputs = Input::all();

        try
        {
            $this->form->validate($inputs);
            $success = $this->templateSelectionRepository->updateTemplate($templateId, $inputs);
        }
        catch(\PCK\Exceptions\ValidationException $e)
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

    public function destroy($templateId)
    {
        try
        {
            FormOfTender::find($templateId)->delete();
            Flash::success('Successfully deleted template.');
        }
        catch(Exception $e)
        {
            Flash::error('Error while deleting template.');
        }

        return Redirect::route('form_of_tender.template.selection');
    }
}

