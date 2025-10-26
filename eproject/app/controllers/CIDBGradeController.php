<?php

use PCK\CIDBGrades\CIDBGrade;
use PCK\Forms\CIDBGradeForm;

class CIDBGradeController extends Controller
{
    private $CIDBGradeForm;

	public function __construct(CIDBGradeForm $CIDBGradeForm)
	{
		$this->CIDBGradeForm = $CIDBGradeForm; 
	}

    public function index()
    {

        $user = Confide::user();

		$records = CIDBGrade::orderBy('id', 'ASC')->get();

		return View::make('cidb_grades.index', array('records'=>$records,'user'=>$user));
        
    }

    public function create()
	{

		return View::make('cidb_grades.create');
	}

    public function edit($Id)
	{
		$record = CIDBGrade::find($Id);

		return View::make('cidb_grades.edit', array('record' => $record));
	}

    public function list()
    {
        $data = [];

        foreach(CIDBGrade::orderBy('id', 'ASC')->get() as $record)
        {
            array_push($data, [
                'id'           => $record->id,
                'grade'         => $record->grade,
                'route_update' => $record->route('cidb_grades.update', [$record->id]),
                'route_delete' => $record->route('cidb_grades.delete', [$record->id]),
            ]);
        }

        return Response::json($data);
    }

    public function store()
    {
        $inputs  = Input::all();
        

        try
        {
            $this->CIDBGradeForm->validate($inputs);

            $record             = new CIDBGrade();
            $record->grade       = $inputs['grade'];
            $record->parent_id       = NULL;
            $record->save();

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("{$inputs['grade']} is successfully added!");
        
        return Redirect::route('cidb_grades.index');
    }

    public function update($Id)
    {
        $record = CIDBGrade::find($Id);
        $inputs  = Input::all();

        try
        {
            $this->CIDBGradeForm->ignoreId($record->id);
			$this->CIDBGradeForm->validate($inputs);

            // $record             = CIDBGrades::find($Id);

            if($record)
            {
                $record->grade       = $inputs['grade'];
                $record->save();
            }

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("{$inputs['grade']} is successfully updated!");
        
        return Redirect::route('cidb_grades.index');

    }

    public function destroy($Id)
    {
        
        try
        {
            $record = CIDBGrade::find($Id);

            if($record)
            {
                $record->delete();
            }

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::route('cidb_grades.index');
        }

        Flash::success("This object is successfully deleted!");

        return Redirect::route('cidb_grades.index');
    }

}
