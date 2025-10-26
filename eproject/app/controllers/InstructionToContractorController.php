<?php
use PCK\InstructionsToContractors\InstructionsToContractor;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\Verifier\Verifier; 
use PCK\Projects\Project;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\Users\User;
use Carbon\Carbon;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\Base\Upload;

class InstructionToContractorController extends \BaseController  {
	
	

    // Your other methods...
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($project)
	{
		
		
			 $user = Confide::user();

			 $records = InstructionsToContractor::where("project_id", $project->id)->orderBy("id", "desc")->get();
			 

			 foreach($records as $record)
			{
		
				$record->status_text = InstructionsToContractor::getStatusText($record->status);
			}
		
			return View::make('instruction_to_contractor.index', array('records'=>$records,'project'=>$project, 'user'=>$user,'records'=>$records));
			
		
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($project)
	{
		$user = Confide::user();
		$verifiers = InstructionsToContractor::getVerifiers($project);

		return View::make('instruction_to_contractor.create' ,array('project'=>$project, 'verifiers' => $verifiers, 'projectId' => $project->id));
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
			$record = new InstructionsToContractor;
			$record->instruction = $inputs['instruction'];
			$record->instruction_date = date("Y-m-d H:i:s");
			$record->submitted_by = $user->id;
			$record->status = InstructionsToContractor::STATUS_OPEN;
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
			
		Flash::success("Instructions to Contractor is added successfully!");
		return Redirect::route('instruction-to-contractor.index', array('projectId' => $project->id));
		
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($project,$id)
	{
		$record = InstructionsToContractor::find($id);
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

		if($record->status == InstructionsToContractor::STATUS_APPROVED || $record->status == InstructionsToContractor::STATUS_REJECT)
		{
			$isVerified = true;
		}

		return View::make('instruction_to_contractor.show', array('project'=>$project, 'verifierLogs' => $verifierLogs, 'attachmentsCount'=>$attachmentsCount, 'record'=>$record, 'isVerified'=>$isVerified, 'isCurrentVerifier'=>$isCurrentVerifier));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($project,$id)
	{
		$record = InstructionsToContractor::find($id);
		$verifiers = InstructionsToContractor::getVerifiers($project);

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

		
		return View::make('instruction_to_contractor.edit', array('project'=>$project, 'record'=>$record, 'verifiers' => $verifiers, 'attachmentsCount'=>$attachmentsCount,'uploadedFiles'=>$uploadedFiles));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($project,$id)
	{
		$record = InstructionsToContractor::find($id);
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

		Flash::success("Instructions to Contractor is updated successfully!");
		return Redirect::route('instruction-to-contractor.index', array('projectId' => $project->id));
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
			$record = InstructionsToContractor::find($id);
			$record->delete();
		} 
		catch(Exception $e){

			Flash::error("Instruction cannot be deleted.");

			return Redirect::route('instruction-to-contractor.index', array('projectId' => $project->id));
		}

		Flash::success("Instruction is deleted successfully!");

		return Redirect::route('instruction-to-contractor.index', array('projectId' => $project->id));
		
	}

	public function submitForApproval(InstructionsToContractor $record, $inputs)
    {
        $verifiers = array_filter($inputs['verifiers'], function($value)
        {
            return $value != "";
        });

        if( empty( $verifiers ) )
        {
            $record->status = InstructionsToContractor::STATUS_APPROVED;
            $record->save();

			Verifier::setVerifierAsApproved(\Confide::user(), $record);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $record);

            $record->status = InstructionsToContractor::STATUS_PENDING_FOR_APPROVAL;
            $record->save();

            Verifier::sendPendingNotification($record);
        }
    }

	public function getAttachmentsList(Project $project, $modelId)
	{
		$record        = InstructionsToContractor::find($modelId);
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
		$record = InstructionsToContractor::find($id);

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
		return Redirect::route('instruction-to-contractor.edit', array('projectId'=>$project->id, 'id' => $record->id));
	}
	
}