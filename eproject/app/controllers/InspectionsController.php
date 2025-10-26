<?php
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Inspections\RequestForInspection;
use PCK\Inspections\InspectionList;
use PCK\Inspections\InspectionListItem;
use PCK\Inspections\InspectionGroupUser;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListCategoryAdditionalField;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionResult;
use PCK\Inspections\InspectionItemResult;
use PCK\Inspections\InspectionVerifierTemplate;
use PCK\Inspections\InspectionGroupInspectionListCategory;
use PCK\Buildspace\ProjectStructureLocationCode;
use PCK\Forms\InspectionReadyDateAndTimeForm;
use PCK\Forms\InspectionDecisionForm;
use PCK\Verifier\Verifier;
use PCK\Verifier\VerifierRepository;
use PCK\Filters\InspectionFilters;
use PCK\Helpers\ModuleAttachment;
use PCK\Notifications\EmailNotifier;

class InspectionsController extends \BaseController {

	protected $inspectionDateTimeForm;
	protected $inspectionDecisionForm;
	protected $verifierRepository;
	protected $inspectionRepository;
	protected $emailNotifier;

	public function __construct(InspectionReadyDateAndTimeForm $inspectionDateTimeForm, InspectionDecisionForm $inspectionDecisionForm, VerifierRepository $verifierRepository, EmailNotifier $emailNotifier)
	{
		$this->inspectionDateTimeForm = $inspectionDateTimeForm;
		$this->inspectionDecisionForm = $inspectionDecisionForm;
		$this->verifierRepository 	  = $verifierRepository;
		$this->emailNotifier          = $emailNotifier;
	}

	public function inspect(Project $project, $requestForInspectionId, $inspectionId)
	{
		$user = \Confide::user();

		$requestForInspection = RequestForInspection::where('project_id', '=', $project->id)
			->where('id', '=', $requestForInspectionId)
			->first();

		$formInfo = $requestForInspection->getFormInfo();

        $locationsDescription = $formInfo['locationsDescription'];
        $inspectionListNames  = $formInfo['inspectionListNames'];
        $additionalFieldsData = $formInfo['additionalFields'];

        // Show current inspection and previous inspection, if any.
        $listItemData = array();

        $listItems = InspectionListItem::where('inspection_list_category_id', '=', $requestForInspection->inspection_list_category_id)
        	->orderBy('lft')
        	->get();

    	$currentInspection = Inspection::find($inspectionId);

		$role = InspectionRole::getRole($currentInspection, $user);

		$inspectionItemResults = Inspection::getInspectionItemResults($currentInspection);

    	$previousInspection = Inspection::where('request_for_inspection_id', '=', $requestForInspectionId)
    		->where('revision', '=', ( $currentInspection->revision - 1 ))
    		->first();

		if( $previousInspection )
		{
			// Get previous inspection values.
			$previousInspectionResults = Inspection::getInspectionItemResults($previousInspection);
		}

		$inspectionResult = InspectionResult::firstOrNew(array(
			'inspection_id'      => $inspectionId,
        	'inspection_role_id' => $role->id,
		));

		$editable = $inspectionResult->status == InspectionResult::STATUS_SUBMITTED ? false : true;

		foreach($listItems as $listItem)
		{
			$row = array(
				'id'		  => $listItem->id,
				'description' => $listItem->description,
				'depth'       => $listItem->depth,
                'type'        => $listItem->type,
				'editable'	  => $editable,
			);

			if($listItem->isTypeItem())
			{
				$row['progress_status'] 			    = $inspectionItemResults[ $listItem->id ][ $role->id ]['progress_status'] ?? number_format(0,2);
				$row['remarks']						    = $inspectionItemResults[ $listItem->id ][ $role->id ]['remarks'] ?? "";
				$row['attachmentCount'] 			    = isset($inspectionItemResults[ $listItem->id ][ $role->id ]['inspection_item_result_id']) ? count($this->getAttachmentDetails(InspectionItemResult::find($inspectionItemResults[ $listItem->id ][ $role->id ]['inspection_item_result_id']))) : 0;
				$row['route:update']    		 	    = route('inspection.inspect.itemUpdate', array($project->id, $requestForInspection->id, $currentInspection->id, $listItem->id));
				$row['route:attachmentUpload'] 	        = route('inspection.inspect.item.attachmentsUpdate', array($project->id, $requestForInspection->id, $currentInspection->id, $listItem->id));
				$row['route:attachmentRoute']  	        = route('inspection.inspect.item.attachmentsList', array($project->id, $requestForInspection->id, $currentInspection->id, $listItem->id));
				$row['route:getUploads']       	        = route('inspection.inspect.item.uploads', array($project->id, $requestForInspection->id, $currentInspection->id, $listItem->id));
				$row['route:getUpdatedAttachmentCount'] = route('inspection.inspect.item.attachments.updated.count.get', array($project->id, $requestForInspection->id, $currentInspection->id, $listItem->id));
			}

			if($listItem->isTypeItem() && $previousInspection )
			{
				$row["progress_status-{$previousInspection->revision}"] = $previousInspectionResults[ $listItem->id ][ $role->id ][ 'progress_status' ] ?? number_format(0,2);
				$row["remarks-{$previousInspection->revision}"]         = $previousInspectionResults[ $listItem->id ][ $role->id ][ 'remarks' ] ?? "";
			}

			$listItemData[] = $row;
		}

		JavaScript::put(array(
			'listItemData' => $listItemData,
		));

		return View::make('inspections.inspect', array(
            'project'              => $project,
            'editable'             => $editable,
            'requestForInspection' => $requestForInspection,
            'locationsDescription' => $locationsDescription,
            'inspectionLists'      => $inspectionListNames,
            'additionalFields'     => $additionalFieldsData,
            'inspection'           => $currentInspection,
            'previousInspection'   => $previousInspection,
        ));
	}

	public function getUpdatedAttachmentCount(Project $project, $requestForInspectionId, $inspectionId)
	{
		$inputs 		       = Input::all();
		$inspectionListItemId  = $inputs['inspectionListItemId'];
		$listItem 		       = InspectionListItem::find($inspectionListItemId);
		$user 			       = \Confide::user();
		$currentInspection     = Inspection::find($inspectionId);
		$role 			       = InspectionRole::getRole($currentInspection, $user);
		$inspectionItemResults = Inspection::getInspectionItemResults($currentInspection);

		return count($this->getAttachmentDetails(InspectionItemResult::find($inspectionItemResults[ $listItem->id ][ $role->id ]['inspection_item_result_id'])));
	}

	public function inspectUpdate(Project $project, $requestForInspectionId, $inspectionId)
	{
		$user 		= \Confide::user();
		$inspection = Inspection::find($inspectionId);
		$role 		= InspectionRole::getRole($inspection, $user);
		$submitters = $inspection->getSubmitters();
		$requestors = $inspection->getRequesters();
		$inspectors = $inspection->getInspectors();

		$inspectionResult = InspectionResult::firstOrNew(array(
			'inspection_id'      => $inspectionId,
        	'inspection_role_id' => $role->id,
		));

		$inspectionResult->status 		= InspectionResult::STATUS_SUBMITTED;
		$inspectionResult->submitted_by = \Confide::user()->id;
		$inspectionResult->submitted_at = \Carbon\Carbon::now();

		$inspectionResult->save();

		$recipientIds = array_unique(array_merge($requestors, $inspectors));

		foreach($recipientIds as $recipientId)
		{
			$recipient = User::find($recipientId);

			$this->emailNotifier->sendRequestForInspectionEmail($project, $inspection, $user, $recipient, 'inspection.inspection_submitted');
		}

		if(InspectionFilters::readyForSubmission($inspection))
		{
			$recipientIds = array_unique(array_merge($submitters, $requestors, $inspectors));

			foreach($recipientIds as $recipientId)
			{
				$recipient = User::find($recipientId);

				$this->emailNotifier->sendRequestForInspectionEmail($project, $inspection, $user, $recipient, 'inspection.inspection_completed');
			}
		}

		if( InspectionFilters::readyForSubmission($inspection) )
		{
			return Redirect::route('inspection.submit', array($project->id, $requestForInspectionId, $inspection->id));
		}

		return Redirect::back();
	}

	public function edit(Project $project, $requestForInspectionId, $inspectionId)
	{
		$user = \Confide::user();

		$userPermission = InspectionGroupUser::where('user_id', '=', $user->id)
			->whereHas('role', function($query) use ($project){
				$query->where('project_id', '=', $project->id)
					->where('can_request_inspection', '=', true);
			})
			->first();

		$requestForInspection = RequestForInspection::where('project_id', '=', $project->id)
			->where('id', '=', $requestForInspectionId)
			->first();

        $formInfo = $requestForInspection->getFormInfo();

        $locationsDescription = $formInfo['locationsDescription'];
        $inspectionListNames  = $formInfo['inspectionListNames'];
        $additionalFieldsData = $formInfo['additionalFields'];

        // Show current inspection (empty) and previous inspection, if any.
        $listItemData = array();

        $listItems = InspectionListItem::where('inspection_list_category_id', '=', $requestForInspection->inspection_list_category_id)
        	->orderBy('lft')
        	->get();

    	$currentInspection = Inspection::where('request_for_inspection_id', '=', $requestForInspection->id)
    		->orderBy('revision', 'desc')
    		->first();

    	$previousInspection = Inspection::where('request_for_inspection_id', '=', $requestForInspection->id)
    		->where('revision', '=', ( $currentInspection->revision - 1 ))
    		->first();

		if( $previousInspection )
		{
			// Get previous inspection values.
			$previousInspectionResults = Inspection::getInspectionItemResults($previousInspection);
		}

		foreach($listItems as $listItem)
		{
			$row = array(
				'id'              => $listItem->id,
				'description'     => $listItem->description,
				'depth'			  => $listItem->depth,
				'type'			  => $listItem->type,
			);

			if($listItem->isTypeItem())
			{
				$row['progress_status'] = 0;
				$row['remarks']			= null;
			}

			if( $listItem->isTypeItem() && $previousInspection )
			{
				$row["progress_status-{$previousInspection->revision}"] = $previousInspectionResults[ $listItem->id ][ $userPermission->inspection_role_id ][ 'progress_status' ] ?? number_format(0,2);
				$row["remarks-{$previousInspection->revision}"]         = $previousInspectionResults[ $listItem->id ][ $userPermission->inspection_role_id ][ 'remarks' ] ?? "";
			}

			$listItemData[] = $row;
		}

		JavaScript::put(array(
			'listItemData' => $listItemData,
		));

		return View::make('inspections.edit', array(
            'project'              => $project,
            'requestForInspection' => $requestForInspection,
            'locationsDescription' => $locationsDescription,
            'inspectionLists'      => $inspectionListNames,
            'additionalFields'     => $additionalFieldsData,
            'inspection'           => $currentInspection,
            'previousInspection'   => $previousInspection,
        ));
	}

	public function update(Project $project, $requestForInspectionId, $inspectionId)
	{
		$user  = \Confide::user();
		$input = Input::all();

		$this->inspectionDateTimeForm->validate($input);

		$inspection = Inspection::find($inspectionId);

		$inspection->ready_for_inspection_date = $input['ready_for_inspection_date'];

		if( isset($input['submit']) )
		{
			$inspection->status = Inspection::STATUS_IN_PROGRESS;
		}

		$inspection->save();

		if( $inspection->status == Inspection::STATUS_IN_PROGRESS )
		{
			$requesters   = $inspection->getRequesters();
			$inspectors   = $inspection->getInspectors();
			$recipientIds = array_unique(array_merge($requesters, $inspectors));

			foreach($recipientIds as $recipientId)
			{
				$recipient = User::find($recipientId);

				$this->emailNotifier->sendRequestForInspectionEmail($project, $inspection, $user, $recipient, 'inspection.inspection_raised');
			}

			return Redirect::route('inspection.inspect', array( $project->id, $requestForInspectionId, $inspection->id ));
		}

		return Redirect::back();
	}

	public function submissionForm(Project $project, $requestForInspectionId, $inspectionId)
	{
		$user = \Confide::user();

		$requestForInspection = RequestForInspection::where('project_id', '=', $project->id)
			->where('id', '=', $requestForInspectionId)
			->first();

		$formInfo = $requestForInspection->getFormInfo();

        $locationsDescription = $formInfo['locationsDescription'];
        $inspectionListNames  = $formInfo['inspectionListNames'];
        $additionalFieldsData = $formInfo['additionalFields'];

        $listItemData = array();

        $listItems = InspectionListItem::where('inspection_list_category_id', '=', $requestForInspection->inspection_list_category_id)
        	->orderBy('lft')
        	->get();

    	$inspection = Inspection::find($inspectionId);

		$roles = InspectionRole::where('project_id', '=', $project->id)->orderBy('created_at', 'asc')->get();

		$inspectionItemResults = Inspection::getInspectionItemResults($inspection);

		foreach($listItems as $listItem)
		{
			$row = array(
				'id'         	   => $listItem->id,
				'description'	   => $listItem->description,
				'depth'       	   => $listItem->depth,
                'type'        	   => $listItem->type,
				'route:getUploads' => route('inspection.inspect.item.role.uploads', array($project->id, $requestForInspectionId, $inspectionId, $listItem->id)),
			);

			if($listItem->isTypeItem())
			{
				foreach($roles as $role)
				{
					$row["progress_status-{$role->id}"] = $inspectionItemResults[ $listItem->id ][ $role->id ][ 'progress_status' ] ?? number_format(0,2);
					$row["remarks-{$role->id}"]         = $inspectionItemResults[ $listItem->id ][ $role->id ][ 'remarks' ] ?? "";
					$row["attachmentCount-{$role->id}"] = isset($inspectionItemResults[ $listItem->id ][ $role->id ]['inspection_item_result_id']) ? count($this->getAttachmentDetails(InspectionItemResult::find($inspectionItemResults[ $listItem->id ][ $role->id ]['inspection_item_result_id']))) : 0;
				}
			}

			$listItemData[] = $row;
		}

		JavaScript::put(array(
			'listItemData' => $listItemData,
		));

		$editable = InspectionFilters::readyForSubmission($inspection) && in_array($user->id, $inspection->getSubmitters());

		return View::make('inspections.show', array(
            'project'              => $project,
            'editable'             => $editable,
            'requestForInspection' => $requestForInspection,
            'locationsDescription' => $locationsDescription,
            'inspectionLists'      => $inspectionListNames,
            'additionalFields'     => $additionalFieldsData,
            'inspection'           => $inspection,
            'roles'                => $roles,
        ));
	}

	public function submit(Project $project, $requestForInspectionId, $inspectionId)
	{
		$input = Input::all();

		$this->inspectionDecisionForm->validate($input);

		$inspection = Inspection::find($inspectionId);

		$input['status'] = Inspection::STATUS_VERIFYING;

		$inspection->update($input);

		ModuleAttachment::saveAttachments($inspection, $input);

		$rootInspectionListCategory = InspectionListCategory::where('inspection_list_id', '=', $inspection->requestForInspection->inspectionListCategory->inspection_list_id)
			->where('lft', '<=', $inspection->requestForInspection->inspectionListCategory->lft)
			->where('rgt', '>=', $inspection->requestForInspection->inspectionListCategory->rgt)
			->orderBy('lft')
			->first();

		$inspectionGroupList = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $rootInspectionListCategory->id)
			->first();

		$verifierTemplate = InspectionVerifierTemplate::where('inspection_group_id', '=', $inspectionGroupList->inspection_group_id)
			->orderBy('priority')
			->get();

		if( ! empty($verifierTemplate->lists('user_id')) )
		{
			Verifier::setVerifiers($verifierTemplate->lists('user_id'), $inspection);
			$this->verifierRepository->executeFollowUp($inspection);
		}
		else
		{
			// if no verifiers, set to approved
			$inspection->getOnApprovedFunction();
		}

		return Redirect::back()->withInput();
	}

	public function approvalLogs(Project $project, $requestForInspectionId, $inspectionId)
	{
		$inspection = Inspection::find($inspectionId);

		$log = Verifier::getAssignedVerifierRecords($inspection, true);

		$data = array();

		foreach($log as $logEntry)
		{
			$data[] = array(
				'id' 		  => $logEntry->id,
				'name' 		  => $logEntry->verifier->name,
				'approved'    => $logEntry->approved,
				'remarks'     => $logEntry->remarks,
				'verified_at' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($logEntry->verified_at))->format(\Config::get('dates.readable_timestamp')),
			);
		}

		return $data;
	}

	public function submissionLogs(Project $project, $requestForInspectionId, $inspectionId)
	{
		$inspection = Inspection::find($inspectionId);

		$inspectionResults = InspectionResult::where('inspection_id', '=', $inspectionId)
			->where('status', '=', InspectionResult::STATUS_SUBMITTED)
			->orderBy('submitted_at')
			->get();

		$data = array();

		$rolesWithResults = array();

		foreach($inspectionResults as $result)
		{
			$data[] = array(
				'id' 	       => $result->role->id,
				'name' 	       => $result->submitter->name,
				'role' 	       => $result->role->name,
				'submitted_at' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($result->submitted_at))->format(\Config::get('dates.readable_timestamp')),
			);

			$rolesWithResults[] = $result->role->id;
		}

		$inProgressRoles = $project->inspectionRoles()->whereNotIn('id', $rolesWithResults)->orderBy('created_at', 'asc')->get();

		foreach($inProgressRoles as $role)
		{
			$data[] = array(
				'id' 	       => $role->id,
				'name' 	       => "-",
				'role' 	       => $role->name,
				'submitted_at' => "-",
			);
		}

		return $data;
	}
}
