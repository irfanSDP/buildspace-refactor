<?php

use PCK\Exceptions\ValidationException;
use PCK\AccountCodeSettings\ApportionmentTypeRepository;
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\Forms\ApportionmentTypeForm;

class ApportionmentTypeController extends \BaseController
{
    private $repository;
    private $apportionmentTypeForm;

    public function __construct(ApportionmentTypeRepository $repository, ApportionmentTypeForm $apportionmentTypeForm)
	{
        $this->repository = $repository;
        $this->apportionmentTypeForm = $apportionmentTypeForm;
    }
    
    public function index()
    {
        return View::make('finance.apportionmentTypes.index');
    }

    public function getApportionmentTypesTableData()
    {
        $apportionmentTypesTableData = $this->repository->getApportionmentTypesTableData();

        return Response::json($apportionmentTypesTableData);
    }

    public function store()
    {
        $errors = null;
        $success = false;
        $inputs = Input::all();

        try
        {
            $this->apportionmentTypeForm->validate($inputs);
            $success = $this->repository->createNewApportionmentType($inputs);
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
            'error'   => $errors,
            'success' => $success,
        ]);
    }

    public function editableCheck($apportionmentTypeId)
    {
        $editable = (AccountCodeSetting::getInUseApportionmentTypeById($apportionmentTypeId)->count() == 0);

        return Response::json([
            'editable' => $editable,
        ]);
    }

    public function update($apportionmentTypeId)
    {
        $errors = null;
        $success = false;
        $inputs = Input::all();

        try
        {
            $this->apportionmentTypeForm->setParameters($apportionmentTypeId);
            $this->apportionmentTypeForm->validate($inputs);
            $success = $this->repository->updateApportionmentType($apportionmentTypeId, $inputs);
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
            'error'   => $errors,
            'success' => $success,
        ]);
    }

    public function destroy($apportionmentTypeId)
    {
        $errors = null;
        $success = false;

        try
        {
            $success = $this->repository->deleteApportionmentType($apportionmentTypeId);
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return Response::json([
            'error'   => $errors,
            'success' => $success,
        ]);
    }
}

