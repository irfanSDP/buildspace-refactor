<?php

use PCK\Forms\WorkCategoryForm;
use PCK\WorkCategories\WorkCategoryRepository;

class WorkCategoriesController extends \BaseController {

    private $repository;
    private $form;

    public function __construct(WorkCategoryRepository $repository, WorkCategoryForm $form)
    {
        $this->repository = $repository;
        $this->form = $form;
    }

    public function index()
    {
        $workCategories = $this->repository->getAll();

        return View::make('work_categories.index', array(
            'workCategories' => $workCategories,
        ));
    }

    public function list()
    {
        $data = [];

        foreach($this->repository->getAll() as $record)
        {
            array_push($data, [
                'id'         => $record->id,
                'name'       => $record->name,
                'identifier' => $record->identifier,
                'enabled'    => $record->enabled,
            ]);
        }

        return Response::json($data);
    }

    public function store()
    {
        $input   = Input::get('data');
        $success = false;

        $workCategory = null;

        try
        {
            $this->form->validate($input);

            $workCategory = $this->repository->store($input);
            $errors       = null;
            $success      = true;
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors  = $e->getErrors();
            $success = false;
        }

        return array(
            'success' => $success,
            'errors'  => $errors,
            'item'    => $workCategory,
        );
    }

    public function update()
    {
        $input   = Input::get('data');
        $success = false;

        $workCategory = null;

        try
        {
            $this->form->ignoreUnique($input['id']);
            $this->form->validate($input);

            $workCategory = $this->repository->update($input);
            $errors       = null;
            $success      = true;
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return array(
            'success' => $success,
            'errors'  => $errors,
            'item'    => $workCategory,
        );
    }

    public function enabledStateToggle()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $workCategory = $this->repository->find($inputs['id']);
            $workCategory->enabled = !$workCategory->enabled;
            $workCategory->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return array(
            'success' => $success,
            'errors'  => $errors,
            'item'    => $workCategory,
        );
    }
}