<?php
use PCK\ScheduledMaintenances\ScheduledMaintenance; 
use PCK\ScheduledMaintenances\ScheduledMaintenanceRepository;
use PCK\Forms\ScheduledMaintenanceForm;
use PCK\Users\User;

class ScheduledMaintenanceController extends \BaseController {

	private $scheduledMaintenanceForm;
	private $scheduledMaintenanceRepo;
	
	public function __construct(ScheduledMaintenanceForm $scheduledMaintenanceForm, ScheduledMaintenanceRepository $scheduledMaintenanceRepo)
	{
		$this->scheduledMaintenanceForm = $scheduledMaintenanceForm;
		$this->scheduledMaintenanceRepo = $scheduledMaintenanceRepo;
	}

	public function index()
	{
		$scheduledMaintenances = ScheduledMaintenance::orderBy('id', 'asc')->get();

		return View::make('scheduled_maintenance.index', array('scheduledMaintenances' => $scheduledMaintenances)); 
	}

	public function create()
	{
		return View::make('scheduled_maintenance.create');
	}

	public function store()
	{
		$user = Confide::user();
		$input = Input::all();

		$originalName = '';
		if (Input::hasFile('image')) {
			$file = Input::file('image');
			$originalName = $file->getClientOriginalName();
		}

		try
		{
			$this->scheduledMaintenanceForm->validate($input);
			$record = new ScheduledMaintenance;
			$record->message = $input['message'];
			$record->start_time = $input['start_time'];
			$record->end_time = $input['end_time'];
			$record->is_under_maintenance = array_key_exists('status', $input) ? 'yes' : 'no';
			$record->image = $originalName;
			$record->created_by = $user->id;
			$record->save();

			if (Input::file('image')) {
				$this->scheduledMaintenanceRepo->upload(Input::file('image'), $record->id , $originalName);
			}
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }
			
		Flash::success("Scheduled Maintenance is added successfully!");

		return Redirect::to('scheduled_maintenance');
	}

	public function edit($id)
	{
        $scheduledMaintenance = ScheduledMaintenance::find($id);

        return View::make('scheduled_maintenance.edit', array(
			'id'          => $id,
            'message'     => $scheduledMaintenance->message,
            'start_time'  => $scheduledMaintenance->start_time,
			'end_time'    => $scheduledMaintenance->end_time,
			'status'      => $scheduledMaintenance->is_under_maintenance,
			'image'       => $scheduledMaintenance->image,
            'backRoute'   => route('scheduled_maintenance.index'),
        ));
	}

	public function update($id)
	{
		$user = Confide::user();
		$scheduledMaintenance = ScheduledMaintenance::find($id);
		$input = Input::all();
		$input['end_time'];

		if (Input::hasFile('image')) {
			$file = Input::file('image');
			$originalName = $file->getClientOriginalName();
		}
		else
		{
			$originalName = $scheduledMaintenance->image ?? null;
		}

		try
		{
			$this->scheduledMaintenanceForm->validate($input);
			$scheduledMaintenance->message = $input['message'];
			$scheduledMaintenance->start_time = $input['start_time'];
			$scheduledMaintenance->end_time = $input['end_time'];
			$scheduledMaintenance->is_under_maintenance = array_key_exists('status', $input) ? 'yes' : 'no';
			$scheduledMaintenance->created_by = $user->id;
			$scheduledMaintenance->image = $originalName;
			$scheduledMaintenance->save();

			if (Input::file('image')) {
				$updateLogo1 = $this->scheduledMaintenanceRepo->upload(Input::file('image'), $scheduledMaintenance->id , $originalName);
			}
	
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		// if( Input::hasFile('company_logo') )
        // {
        //     $success = $this->myCompanyProfileRepo->updateCompanyLogo($companyProfile, Input::file('company_logo'));

        //     if( ! $success )
        //     {
        //         Flash::error("Oh dear! The file could not be saved. Please make sure the file is an image.");

        //         return Redirect::back();
        //     }
        // }
		Flash::success("Scheduled Maintenance is updated!");

		return Redirect::to('scheduled_maintenance');
	}

	public function destroy($id)
	{
		$scheduledMaintenance = ScheduledMaintenance::find($id);

		try
		{
			$scheduledMaintenance->delete();
		} 
		catch(Exception $e){

			Flash::error("Scheduled cannot be deleted because it is used in other module.");

			return Redirect::to('scheduled_maintenance');
		}

		Flash::success("Scheduled is deleted successfully!");

		return Redirect::to('scheduled_maintenance');
	}

}