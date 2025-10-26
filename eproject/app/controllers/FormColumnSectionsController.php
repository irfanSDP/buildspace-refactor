<?php

use PCK\Helpers\DBTransaction;
use PCK\FormBuilder\FormColumnSectionRepository;
use PCK\FormBuilder\FormColumn;
use PCK\FormBuilder\FormColumnSection;
use PCK\Forms\FormColumnSectionForm;
use PCK\Exceptions\ValidationException;

class FormColumnSectionsController extends Controller
{
    private $formColumnSectionRepository;
    private $form;

    public function __construct(FormColumnSectionRepository $formColumnSectionRepository, FormColumnSectionForm $form)
    {
        $this->formColumnSectionRepository = $formColumnSectionRepository;
        $this->form                        = $form;
    }

    public function getColumnSectionComponents($formId, $formColumnId)
    {
        $sectionComponents = $this->formColumnSectionRepository->getColumnSectionComponents($formColumnId);

        return Response::json($sectionComponents);
    }

    public function store($formColumnId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->formColumnSectionRepository->store($formColumnId, $inputs);

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

    public function swap($formColumnId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->formColumnSectionRepository->swap($inputs['draggedSectionId'], $inputs['swappedSectionId']);

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

    public function update($sectionId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $section = FormColumnSection::find($sectionId);

            $this->form->validate($inputs);
            $this->formColumnSectionRepository->updateColumnName($section, $inputs['name']);

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
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function delete($sectionId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $section = FormColumnSection::find($sectionId);
            $section->delete();

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

    public function getSectionSelections($formColumnId)
    {
        $column = FormColumn::find($formColumnId);
    
        $sectionSelection = $this->formColumnSectionRepository->getSectionSelections($column);

        return Response::json($sectionSelection);
    }

    public function importSelectedSections($formColumnId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $column            = FormColumn::find($formColumnId);
            $selecteSectionIds = $inputs['selectedIds'];

            foreach($selecteSectionIds as $sectionId)
            {
                $originSection = FormColumnSection::find($sectionId);
                $originSection->clone($column);
            }

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
}

