<?php

use PCK\Tenders\OpenTenderTenderDocument;
use PCK\Forms\OpenTenderTenderDocumentForm;
use PCK\Helpers\ModuleAttachment;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\Base\Upload;
use PCK\Base\Helpers;
use PCK\Users\User;
use Carbon\Carbon;

class OpenTenderTenderDocumentController extends \BaseController {

	public function __construct(OpenTenderTenderDocumentForm $openTenderTenderDocumentForm)
	{
		$this->openTenderTenderDocumentForm = $openTenderTenderDocumentForm;
	}

	public function create($project,$tenderId)
	{
		return View::make('tenders.open_tender_tender_document.create', array('project'=>$project, 'tenderId' => $tenderId));
	}

	public function store($project,$tenderId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->openTenderTenderDocumentForm->validate($input);
			$input["tender_id"]  =  $tenderId;
            $input["created_by"] =  $user->id;

		    $record = OpenTenderTenderDocument::create($input);

			ModuleAttachment::saveAttachments($record, $input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is created successfully!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId,'tenderDocuments'));
	}

	public function edit($project,$tenderId,$id)
	{
		$tenderDocument = OpenTenderTenderDocument::find($id);

		$uploadedFilesId = ModuleUploadedFile::where('uploadable_type', get_class($tenderDocument))->where('uploadable_id', $tenderDocument->id)->lists('upload_id');

		$uploadedFiles = Upload::whereIn('id', $uploadedFilesId)->get();

		if($this->getAttachmentDetails($tenderDocument))
		{
			$attachmentsCount = $this->getAttachmentDetails($tenderDocument)->count();
		}
		else
		{
			$attachmentsCount = 0;
		}

		return View::make('tenders.open_tender_tender_document.edit', array('tenderDocument' => $tenderDocument, 'project'=>$project, 'tenderId' => $tenderId, 'attachmentsCount'=>$attachmentsCount,'uploadedFiles'=>$uploadedFiles));
	}

	public function update($project,$tenderId,$id)
	{
		$input  = Input::all();
		$record = OpenTenderTenderDocument::find($id);

		try
		{
			$this->openTenderTenderDocumentForm->validate($input);
			$record->update($input);

			ModuleAttachment::saveAttachments($record, $input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is updated successfully!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'tenderDocuments'));
	}

	public function show($project,$tenderId,$id)
	{
		$tenderDocument = OpenTenderTenderDocument::find($id);
		
		if($this->getAttachmentDetails($tenderDocument))
		{
			$attachmentsCount = $this->getAttachmentDetails($tenderDocument)->count();
		}
		else
		{
			$attachmentsCount = 0;
		}

		return View::make('tenders.open_tender_tender_document.show', array('project'=>$project, 'tenderId' => $tenderId,'attachmentsCount'=>$attachmentsCount,'tenderDocument'=>$tenderDocument));
	}

	public function destroy($project, $tenderId, $id)
	{
		try
		{
			$record = OpenTenderTenderDocument::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

            return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'tenderDocuments'));
		}

		Flash::success("This object is successfully deleted!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'tenderDocuments'));

	}

	public function getAttachmentsList($project, $tenderId, $id)
	{
		$record        = OpenTenderTenderDocument::find($id);
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
