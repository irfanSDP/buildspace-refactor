<?php

use PCK\Tenders\OpenTenderAnnouncement;
use PCK\Forms\OpenTenderAnnouncementForm;
use PCK\Base\Helpers;

class OpenTenderAnnouncementController extends \BaseController {

	public function __construct(OpenTenderAnnouncementForm $openTenderAnnouncementForm)
	{
		$this->openTenderAnnouncementForm = $openTenderAnnouncementForm;
	}

	public function create($project,$tenderId)
	{
		return View::make('tenders.open_tender_announcement.create', array('project'=>$project, 'tenderId' => $tenderId));
	}

	public function store($project,$tenderId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->openTenderAnnouncementForm->validate($input);

			$input["tender_id"]  =  $tenderId;
            $input["created_by"] =  $user->id;
			$input = Helpers::processInput($input);

		    OpenTenderAnnouncement::create($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is created successfully!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId,'announcementInfo'));
	}

	public function edit($project,$tenderId,$id)
	{
		$announcementInfo = OpenTenderAnnouncement::find($id);

		return View::make('tenders.open_tender_announcement.edit', array('announcementInfo' => $announcementInfo, 'project'=>$project, 'tenderId' => $tenderId));
	}

	public function update($project,$tenderId,$id)
	{
		$input = Input::all();

		try
		{
			$this->openTenderAnnouncementForm->validate($input);
			$input = Helpers::processInput($input);

			OpenTenderAnnouncement::where("id", $id)->update($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is updated successfully!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'announcementInfo'));
	}

	public function destroy($project, $tenderId, $id)
	{
		try
		{
			$record = OpenTenderAnnouncement::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

            return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'announcementInfo'));
		}

		Flash::success("This object is successfully deleted!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'announcementInfo'));

	}

}
