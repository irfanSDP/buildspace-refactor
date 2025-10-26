<?php

use PCK\Helpers\DBTransaction;
use PCK\Forms\VendorManagementGradeLevelForm;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeRepository;

class VendorManagementGradeLevelsController extends Controller
{
    private $vmGradeRepository;
    private $form;

    public function __construct(VendorManagementGradeRepository $vmGradeRepository, VendorManagementGradeLevelForm $form)
    {
        $this->vmGradeRepository = $vmGradeRepository;
        $this->form              = $form;
    }

    public function show($gradeId)
    {
        $grade = VendorManagementGrade::find($gradeId);

        return View::make('vendor_management_grades.show', [
            'grade' => $grade,
        ]);
    }

    public function getLevels($gradeId)
    {
        $grade  = VendorManagementGrade::find($gradeId);
        $levels = $this->vmGradeRepository->getGradeLevels($grade);

        return Response::json($levels);
    }

    public function store($gradeId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $grade = VendorManagementGrade::find($gradeId);

            $inputs['gradeId'] = $grade->id;

            $this->form->validate($inputs);
        
            VendorManagementGradeLevel::createNewRecord($grade, $inputs['description'], $inputs['score_upper_limit'], $inputs['definition']);

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
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update($levelId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $level = VendorManagementGradeLevel::find($levelId);

            $inputs['levelId'] = $level->id;

            $this->form->validate($inputs);

            $this->vmGradeRepository->updateGradeLevel($level, $inputs);

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
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function delete($levelId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $level = VendorManagementGradeLevel::find($levelId);
            $level->delete();

            $transaction->commit();
            
            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}