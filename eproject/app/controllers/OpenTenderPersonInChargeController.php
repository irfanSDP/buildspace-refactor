<?php

use PCK\Tenders\OpenTenderPersonInCharge;
use PCK\Forms\OpenTenderPersonInChargeForm;

class OpenTenderPersonInChargeController extends \BaseController {

	public function __construct(OpenTenderPersonInChargeForm $openTenderPersonInChargeForm)
	{
		$this->openTenderPersonInChargeForm = $openTenderPersonInChargeForm;
	}

	public function create($project,$tenderId)
	{
		return View::make('tenders.open_tender_person_in_charge.create', array('project'=>$project, 'tenderId' => $tenderId));
	}

	public function store($project,$tenderId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->openTenderPersonInChargeForm->validate($input);
			$input["tender_id"]  =  $tenderId;
            $input["created_by"] =  $user->id;

		    OpenTenderPersonInCharge::create($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is created successfully!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId,'personInCharge'));
	}

	public function edit($project,$tenderId,$id)
	{
		$personInCharge = OpenTenderPersonInCharge::find($id);

		return View::make('tenders.open_tender_person_in_charge.edit', array('personInCharge' => $personInCharge, 'project'=>$project, 'tenderId' => $tenderId));
	}

	public function update($project,$tenderId,$id)
	{
		$input = Input::all();

		try
		{
			$this->openTenderPersonInChargeForm->validate($input);
			$input = $this->processInput($input);

			OpenTenderPersonInCharge::where("id", $id)->update($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is updated successfully!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'personInCharge'));
	}

	public function destroy($project, $tenderId, $id)
	{
		try
		{
			$record = OpenTenderPersonInCharge::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

            return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'personInCharge'));
		}

		Flash::success("This object is successfully deleted!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'personInCharge'));

	}

	public function processInput($input)
	{
		foreach($input as $key => $value)
		{
			if($input[$key] == "")
			{
				$input[$key] = NULL;
			}

			if($key == "_method" || $key == "_token" || $key == "Submit" || $key == "form_type")
			{
				unset($input[$key]);
			}
		}

		return $input;
	}



}
