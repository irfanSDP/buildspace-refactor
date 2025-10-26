<?php 

use PCK\Helpers\DBTransaction;
use PCK\Forms\VendorManagementGradeForm;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeRepository;

class VendorManagementGradesController extends Controller
{
    private $vmGradeRepository;
    private $form;

    public function __construct(VendorManagementGradeRepository $vmGradeRepository, VendorManagementGradeForm $form)
    {
        $this->vmGradeRepository = $vmGradeRepository;
        $this->form              = $form;
    }

    public function index()
    {
        return View::make('vendor_management_grades.index');
    }

    public function getAllGrades()
    {
        $grades = $this->vmGradeRepository->getAllGrades();

        return Response::json($grades);
    }

    public function store()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);
            VendorManagementGrade::createNewRecord($inputs['name']);

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

    public function update($gradeId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);

            $grade = VendorManagementGrade::find($gradeId);

            $this->vmGradeRepository->update($grade, $inputs);

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

    public function delete($gradeId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $grade = VendorManagementGrade::find($gradeId);

            if($grade)
            {
                $grade->delete();
            }

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