<?php

use PCK\Helpers\DBTransaction;
use PCK\FormBuilder\FormColumnRepository;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\FormColumn;
use PCK\Forms\FormColumnForm;
use PCK\Exceptions\ValidationException;

class FormColumnsController extends Controller
{
    private $formColumnRepository;
    private $form;

    public function __construct(FormColumnRepository $formColumnRepository, FormColumnForm $form)
    {
        $this->formColumnRepository = $formColumnRepository;
        $this->form                 = $form;
    }

    public function createNewColumn($formId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $form = DynamicForm::find($formId);

            $this->form->validate($inputs);
            $this->formColumnRepository->createNewColumn($form, $inputs);

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

    public function update($columnId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $column = FormColumn::find($columnId);

            $this->form->validate($inputs);
            $this->formColumnRepository->updateColumnName($column, $inputs['name']);

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

    public function delete($columnId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $column = FormColumn::find($columnId);
            $column->delete();

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

    public function swap()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->formColumnRepository->swap($inputs['draggedColumnId'], $inputs['swappedColumnId']);

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

    public function getColumnSelections($formId)
    {
        $form = DynamicForm::find($formId);
        
        $columnSelections = $this->formColumnRepository->getColumnSelections($form);

        return Response::json($columnSelections);
    }

    public function importSelectedColumns($formId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $form             = DynamicForm::find($formId);
            $selecteColumnIds = $inputs['selectedIds'];

            foreach($selecteColumnIds as $columnId)
            {
                $originColumn = FormColumn::find($columnId);
                $originColumn->clone($form);
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

