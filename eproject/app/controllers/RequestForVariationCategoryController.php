<?php

use PCK\RequestForVariation\RequestForVariationCategory;
use PCK\RequestForVariation\RequestForVariationCategoryRepository;
use PCK\RequestForVariation\RequestForVariationCategoryKpiLimitUpdateLog;
use PCK\Users\User;
use PCK\Forms\RfvCategoryForm;

class RequestForVariationCategoryController extends BaseController
{

    private $rfvCategoryRepository;
    private $rfvCategoryForm;

    public function __construct(RequestForVariationCategoryRepository $rfvCategoryRepository, RfvCategoryForm $rfvCategoryForm)
    {
        $this->rfvCategoryRepository = $rfvCategoryRepository;
        $this->rfvCategoryForm = $rfvCategoryForm;
    }

    public function index()
    {
        return View::make('request_for_variation.rfv.category.index');
    }

    public function getRfvCategories()
    {
        $rfvCategories = $this->rfvCategoryRepository->getRfvCategories();

        return Response::json($rfvCategories);
    }

    public function kpiLimitEdit($rfvCategoryId)
    {
        $rfvCategory = RequestForVariationCategory::find($rfvCategoryId);

        return View::make('request_for_variation.rfv.category.edit', [
            'rfvCategory' => $rfvCategory,
        ]);
    }

    public function kpiLimitUpdate($rfvCategoryId)
    {
        $inputs = Input::all();
        
        $this->rfvCategoryRepository->kpiLimitUpdate($rfvCategoryId, $inputs);

        return Redirect::route('requestForVariation.categories.index');
    }

    public function store()
    {
        $errors = null;
        $success = false;
        $inputs = Input::all();

        try
        {
            $this->rfvCategoryForm->validate($inputs);
            $success = $this->rfvCategoryRepository->createNewRfvCategory($inputs);
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
            'error'   => $errors,
            'success' => $success,
        ]);
    }

    public function destroy($rfvCategoryId)
    {
        $errors = null;
        $success = false;

        try
        {
            $success = $this->rfvCategoryRepository->deleteRfvCategory($rfvCategoryId);
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

    public function editableCheck($rfvCategoryId)
    {
        $editable = RequestForVariationCategory::editAllowed($rfvCategoryId);
        
        return Response::json([
            'editable' => $editable,
        ]);
    }

    public function rfvCategoryDescriptionUpdate($rfvCategoryId)
    {
        $errors = null;
        $success = false;
        $inputs = Input::all();

        try
        {
            $this->rfvCategoryForm->setParameters($rfvCategoryId);
            $this->rfvCategoryForm->validate($inputs);
            $success = $this->rfvCategoryRepository->updateRfvCategory($rfvCategoryId, $inputs);
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
            'error'   => $errors,
            'success' => $success,
        ]);
    }

    public function getKpiLimitUpdateLogs($rfvCategoryId)
    {
        $rfvCategory = RequestForVariationCategory::find($rfvCategoryId);

        $kpiLimitUpdateLogs = RequestForVariationCategoryKpiLimitUpdateLog::getKpiLimitUpdateLogs($rfvCategory);

        return Response::json($kpiLimitUpdateLogs);
    }
}

