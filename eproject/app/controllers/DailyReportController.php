<?php
use PCK\DailyReport\DailyReport;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\Verifier\Verifier; 
use PCK\Projects\Project;
use PCK\Base\Helpers;
use PCK\Users\User;
use Carbon\Carbon;
use PCK\Helpers\ModuleAttachment;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\Base\Upload;

class DailyReportController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($project)
	{
		$user = Confide::user();

			$records = DailyReport::where("project_id", $project->id)->orderBy("id", "desc")->get();

			foreach($records as $record)
		{
	
			$record->status_text = DailyReport::getStatusText($record->status);
		}
	
		return View::make('daily_report.index', array('records'=>$records, 'project'=>$project, 'user'=>$user, 'records'=>$records));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($project)
	{
		$user = Confide::user();
		$verifiers = DailyReport::getVerifiers($project);

		return View::make('daily_report.create' ,array('project'=>$project, 'verifiers' => $verifiers, 'projectId' => $project->id));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($project)
	{
		$user = Confide::user();
		$inputs = Input::all();
		
		try
		{
			$record = new DailyReport;
			$record->instruction = $inputs['instruction'];
			$record->instruction_date = date("Y-m-d H:i:s");
			$record->submitted_by = $user->id;
			$record->status = DailyReport::STATUS_OPEN;
			$record->project_id  = $project->id;   
			$record->save();

			ModuleAttachment::saveAttachments($record, $inputs);

			$this->submitForApproval($record,$inputs);
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }
	
		Flash::success("Daily Report Instruction is added successfully!");
		return Redirect::route('daily-report.index', array('projectId' => $project->id));
		
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($project,$id)
	{
		$record = DailyReport::find($id);
		$isCurrentVerifier	= Verifier::isCurrentVerifier(\Confide::user(), $record);
		$isVerified = false;
		$show = true;
		
		if($this->getAttachmentDetails($record))
		{
			$attachmentsCount = $this->getAttachmentDetails($record)->count();
		}
		else
		{
			$attachmentsCount = 0;
		}

		$verifierLogs  = Verifier::getAssignedVerifierRecords($record, true);

		if($record->status == DailyReport::STATUS_APPROVED || $record->status == DailyReport::STATUS_REJECT)
		{
			$isVerified = true;
		}

		return View::make('daily_report.show', array('project'=>$project,'attachmentsCount'=>$attachmentsCount,'verifierLogs' => $verifierLogs, 'record'=>$record, 'isVerified'=>$isVerified, 'isCurrentVerifier'=>$isCurrentVerifier));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($project,$id)
	{
		$record = DailyReport::find($id);
		$verifiers = DailyReport::getVerifiers($project);

		$uploadedFilesId = ModuleUploadedFile::where('uploadable_type', get_class($record))->where('uploadable_id', $record->id)->lists('upload_id');

		$uploadedFiles = Upload::whereIn('id', $uploadedFilesId)->get();

		if($this->getAttachmentDetails($record))
		{
			$attachmentsCount = $this->getAttachmentDetails($record)->count();
		}
		else
		{
			$attachmentsCount = 0;
		}

		return View::make('daily_report.edit', array('project'=>$project, 'record'=>$record, 'verifiers' => $verifiers,'attachmentsCount'=>$attachmentsCount,'uploadedFiles'=>$uploadedFiles));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($project,$id)
	{
		$record = DailyReport::find($id);
		$input = Input::all();

		try
		{
			$record->update($input);
			$this->submitForApproval($record,$input);
			ModuleAttachment::saveAttachments($record, $input);
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Daily Report Instruction is updated successfully!");
		return Redirect::route('daily-report.index', array('projectId' => $project->id));
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($project,$id)
	{
		try
		{
			$record = DailyReport::find($id);
			$record->delete();
		} 
		catch(Exception $e){

			Flash::error("Daily Report Instruction cannot be deleted.");

			return Redirect::route('daily-report.index', array('projectId' => $project->id));
		}

		Flash::success("Daily Report Instruction is deleted successfully!");

		return Redirect::route('daily-report.index', array('projectId' => $project->id));
		
	}

	public function submitForApproval(DailyReport $record, $inputs)
    {
		$verifiers = isset($inputs['verifiers']) ? $inputs['verifiers'] : [];

        if( empty( $verifiers ) )
        {
            $record->status = DailyReport::STATUS_APPROVED;
            $record->save();

			Verifier::setVerifierAsApproved(\Confide::user(), $record);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $record);

            $record->status = DailyReport::STATUS_PENDING_FOR_APPROVAL;
            $record->save();

            Verifier::sendPendingNotification($record);
        }
    }

	public function getAttachmentsList(Project $project, $modelId)
	{
		$record        = DailyReport::find($modelId);
		$uploadedFiles = $this->getAttachmentDetails($record);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);
			$file['uploaded_by'] = User::find($file['user_id'])->name;
			$file['uploaded_at'] = Carbon::parse($file['uploaded_at'])->format(\Config::get('dates.created_at'));

			$data[] = $file;
		}

		return $data;
	}

	public function attachmentDelete($project,$uploadedItemId, $id)
	{
		$record = DailyReport::find($id);

		try 
		{
			$uploadedItem = Upload::find($uploadedItemId);
			$uploadedItem->delete();
		}
		catch(Exception $e)
		{
			Flash::error("Attachment cannot be deleted.");
		}

		Flash::success("Attachment is deleted successfully!");
		return Redirect::route('daily-report.edit', array('projectId'=>$project->id, 'id' => $record->id));
	}


}
