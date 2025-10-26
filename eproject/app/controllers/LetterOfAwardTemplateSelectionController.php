<?php

use PCK\LetterOfAward\LetterOfAward;
use PCK\LetterOfAward\LetterOfAwardTemplateSelectionRepository;
use PCK\Forms\LetterOfAwardTemplateForm;

class LetterOfAwardTemplateSelectionController extends BaseController
{
    private $repository;
    private $form;

    public function __construct(LetterOfAwardTemplateSelectionRepository $repository, LetterOfAwardTemplateForm $form)
    {
        $this->repository = $repository;
        $this->form = $form;
    }

    public function index()
    {
        return View::make('letter_of_award.letterOfAward.template_selection.index');
    }

    public function getAllTemplates()
    {
        $templates = $this->repository->getAllTemplates();

        return Response::json($templates);
    }

    public function update($templateId)
    {
        $errors = null;
        $success = false;
        $inputs = Input::all();

        try
        {
            $this->form->validate($inputs);
            $success = $this->repository->updateTemplate($templateId, $inputs);
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
            $template = LetterOfAward::find($templateId);
            $template->delete();

            Flash::success('Successfully deleted template.');
        }
        catch(Exception $e)
        {
            Flash::error('Error while deleting template.');
        }

        return Redirect::route('letterOfAward.templates.selection');
    }
}

