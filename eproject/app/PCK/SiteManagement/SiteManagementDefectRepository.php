<?php namespace PCK\SiteManagement;

use Illuminate\Events\Dispatcher;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;
use PCK\DailyLabourReports\ProjectLabourRate;
use PCK\SiteManagement\SiteManagementDefectBackchargeDetail;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class SiteManagementDefectRepository extends BaseModuleRepository {

	protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public static function processQuery($user, $project){

    	$query = SiteManagementDefect::where("project_id", $project->id);

		if($project->isSubProject())
		{
			$query = SiteManagementDefect::where("project_id", $project->parent_project_id);
		}    
		
		if(SiteManagementUserPermission::isQsUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
		{
			$query = $query->where(function($query){

				$query->where("status_id", SiteManagementDefect::STATUS_BACKCHARGE)
						    ->orWhere('status_id', SiteManagementDefect::STATUS_BACKCHARGE_PENDING)
						    ->orWhere('status_id', SiteManagementDefect::STATUS_BACKCHARGE_SUBMITTED)
						    ->orWhere('status_id', SiteManagementDefect::STATUS_BACKCHARGE_REJECTED);
			});
		}

		elseif(SiteManagementUserPermission::isSiteUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project)||
		   SiteManagementUserPermission::isClientUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
		{

			$query = $query->where(function($query) use ($user){

				$query->where("pic_user_id",$user->id)->orWhere('submitted_by', $user->id);

			});
		}

		elseif(SiteManagementUserPermission::isProjectAssignedContractor($user,$project))
		{
			$query = $query->where("contractor_id",$user->company->id);
		}

		else
		{
			if(SiteManagementUserPermission::isPmUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
			{
				return $query;
			}

			$query = $query->where(function($query) use ($user){

				$query->where("pic_user_id",$user->id)->orWhere('submitted_by', $user->id);

			});
		}

		return $query; 
    }


    public function store($project, $input)
    {
    	$user = \Confide::user();

    	// To get highest level of location

		foreach ($input as $key => $value) 
		{
		    if (strpos($key, 'locationLevel_') === 0) 
		    {
		        $locations[$key] = $value;
		    }
		}

		foreach ($locations as $key => $value)
		{
			$levels[] = substr($key,14);
		}

		foreach($levels as $level)
		{
			$highestLevel = 0;

			if($level > $highestLevel)
			{
				$highestLevel = $level;
			}
		}	

		$siteManagementDefect = new SiteManagementDefect;
		$siteManagementDefect->project_structure_location_code_id = $input['locationLevel_' . $highestLevel];
		$siteManagementDefect->pre_defined_location_code_id = $input['trade'];
		$siteManagementDefect->contractor_id = $input['contractor']?? NULL;
		$siteManagementDefect->defect_category_id = $input['category'];
		$siteManagementDefect->defect_id = $input['defect']?? NULL;
		$siteManagementDefect->remark = $input['remark'];
		$siteManagementDefect->submitted_by = $user->id;
		if(SiteManagementUserPermission::isSiteUser(SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
		{
			$siteManagementDefect->pic_user_id = $user->id;
		}
		$siteManagementDefect->project_id = $project->id;
		$siteManagementDefect->save(); 

		$this->saveAttachments($siteManagementDefect, $input);

		return $siteManagementDefect;
    }

    public function storeDefectFormResponse($form_id, $input)
    {
    	$user = \Confide::user();
    	
    	$siteManagementDefectFormResponse = new SiteManagementDefectFormResponse;
		$siteManagementDefectFormResponse->remark = $input['remark'];
		$siteManagementDefectFormResponse->response_identifier = $input['response'];
		$siteManagementDefectFormResponse->site_management_defect_id = $form_id;
		$siteManagementDefectFormResponse->user_id = $user->id;
		$siteManagementDefectFormResponse->save();

		$this->saveAttachments($siteManagementDefectFormResponse, $input);
    }

    public function generateUniqueMcarNumber()
	{   
		return uniqid('MCAR/');
	}

	public function sendBackchargeNotification($project, $model)
	{
		$users = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)->toArray();
		
		$this->sendEmailNotificationByUsers($project, $model, $users, 'backcharge_notification', 'site-management-defect.getResponse');
		$this->sendSystemNotificationByUsers($project, $model, $users, 'backcharge_notification', 'site-management-defect.getResponse');
	}

	public function sendDefectFormSubmitNotification($project, $model)
	{
		$contractors = $model->company->users->toArray();

		$subProjects = Project::where("parent_project_id", $project->id)->get();

        foreach($subProjects as $subProject)
        {
            $selectedContractor = $subProject->getSelectedContractor();

            if($selectedContractor)
            {
				if($selectedContractor->id == $model->company->id)
				{
					$pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);
					$users = $pm->toArray();

					if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$subProject))
					{
						$this->sendEmailNotificationByUsers($subProject, $model, $contractors, 'siteManagement.form_submit_notification', 'site-management-defect.getResponse');
						$this->sendSystemNotificationByUsers($subProject, $model, $contractors, 'siteManagement.form_submit_notification', 'site-management-defect.getResponse');

						$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.form_submit_notification', 'site-management-defect.getResponse');
						$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.form_submit_notification', 'site-management-defect.getResponse');
					}
				}
            }
        }

        $selectedContractor = $project->getSelectedContractor();

        if($selectedContractor)
        {
        	if($selectedContractor->id == $model->company->id)
        	{
	        	$pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)->toArray();
	        	$users = array_merge($pm,$contractors);

	        	if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$project))
	        	{
		        	$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.form_submit_notification', 'site-management-defect.getResponse');
					$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.form_submit_notification', 'site-management-defect.getResponse');
				}
        	}

        }
	}

	public function sendContractorRepliedNotification($project, $model)
	{
		if($project->isMainProject())
		{
			$pic = $model->user;

			$pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

			if(isset($pic))
			{
				$users = $pm->add($pic)->toArray();
			}
			else
			{
				$users = $pm->toArray();
			}

			$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.contractor_replied_notification', 'site-management-defect.getResponse');
			$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.contractor_replied_notification', 'site-management-defect.getResponse');
		}
		else
		{
			$main_project = Project::where("id", $project->parent_project_id)->first();

			$pic = $model->user;

			$pm = SiteManagementUserPermission::getAssignedPms($main_project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

			if(isset($pic))
			{
				$users = $pm->add($pic)->toArray();
			}
			else
			{
				$users = $pm->toArray();
			}

			$this->sendEmailNotificationByUsers($main_project, $model, $users, 'siteManagement.contractor_replied_notification', 'site-management-defect.getResponse');
			$this->sendSystemNotificationByUsers($main_project, $model, $users, 'siteManagement.contractor_replied_notification', 'site-management-defect.getResponse');
		}
	}

	public function sendPicRepliedNotification($project, $model)
	{
		$contractors = $model->company->users->toArray();

		$subProjects = Project::where("parent_project_id", $project->id)->get();

        foreach($subProjects as $subProject)
        {
            $selectedContractor = $subProject->getSelectedContractor();

            if($selectedContractor)
            {
	            if($selectedContractor->id == $model->company->id)
	        	{
	        		$pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);
	        		$users = $pm->toArray();

	        		if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$subProject))
	        		{
						$this->sendEmailNotificationByUsers($subProject, $model, $contractors, 'siteManagement.site_replied_notification', 'site-management-defect.getResponse');
						$this->sendSystemNotificationByUsers($subProject, $model, $contractors, 'siteManagement.site_replied_notification', 'site-management-defect.getResponse');

						$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.site_replied_notification', 'site-management-defect.getResponse');
						$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.site_replied_notification', 'site-management-defect.getResponse');
	        		}
	        	}
            }
        }

        $selectedContractor = $project->getSelectedContractor();

        if($selectedContractor)
        {
        	if( $selectedContractor->id == $model->company->id )
        	{
	        	$pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)->toArray();
	        	$users = array_merge($pm,$contractors);

	        	if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$project))
	        	{
		        	$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.site_replied_notification', 'site-management-defect.getResponse');
					$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.site_replied_notification', 'site-management-defect.getResponse');
				}
        	}
        }
	}

	public function sendWithoutVerifierNotification($project, $model)
	{
		$pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)->toArray();

		$qs = SiteManagementUserPermission::getAssignedQs($project,SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)->toArray();

		$users = array_merge($pm,$qs);
		
		$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.without_verifier_notification', 'site-management-defect.getResponse');
		$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.without_verifier_notification', 'site-management-defect.getResponse');
	}

	public function sendPicAssignedNotification($project, $model)
	{
		$pic = $model->user;

		$users = array();
		$users[] = $pic;

		$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.pic_assigned_notification', 'site-management-defect.getResponse');
		$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.pic_assigned_notification', 'site-management-defect.getResponse');

	}

	public function sendDefectFormClosedNotification($project, $model)
	{
		$submitted_user = $model->submittedUser;

		$users = array();
		$users[] = $submitted_user;

		$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.defect_closed_notification', 'site-management-defect.getResponse');
		$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.defect_closed_notification', 'site-management-defect.getResponse');

	}

	public function sendBackchargeApprovedNotificationToContractor($mainProject, $model)
	{
		$contractors = $model->company->users->toArray();

		$users = $contractors;

		$projects = Project::where("parent_project_id", $mainProject->id)->get();

        $projects->add($mainProject);

        foreach($projects as $project)
        {
            $selectedContractor = $project->getSelectedContractor();

            if($selectedContractor)
            {
            	if($selectedContractor->id == $model->company->id)
        		{
	        		if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$project))
	        		{
						$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.backcharge_approved_notification', 'site-management-defect.getResponse');
						$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.backcharge_approved_notification', 'site-management-defect.getResponse');
					}
        		}
            }
        }
	}

	public function sendBackchargeRejectedNotificationToContractor($mainProject, $model)
	{
		$contractors = $model->company->users->toArray();

		$users = $contractors;

		$projects = Project::where("parent_project_id", $mainProject->id)->get();

        $projects->add($mainProject);

        foreach($projects as $project)
        {
            $selectedContractor = $project->getSelectedContractor();

            if($selectedContractor)
            {
	            if($selectedContractor->id == $model->company->id)
	        	{
	        		if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$project))
	        		{
						$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.backcharge_rejected_notification', 'site-management-defect.getResponse');
						$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.backcharge_rejected_notification', 'site-management-defect.getResponse');
					}
	        	}
            }
        }
	}

	public function sendMcarActivatedNotification($project, $model)
	{
		$pic = $model->user;

		$users = array();
		$users[] = $pic;

		$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.mcar_activated_notification', 'site-management-defect.createMCAR');
		$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.mcar_activated_notification', 'site-management-defect.createMCAR');

	}

	public function sendMcarSubmittedNotification($mainProject, $model)
	{
		$contractors = $model->company->users->toArray();

		$users = $contractors;

		$projects = Project::where("parent_project_id", $mainProject->id)->get();

        $projects->add($mainProject);

        foreach($projects as $project)
        {
            $selectedContractor = $project->getSelectedContractor();

            if($selectedContractor)
            {
	            if($selectedContractor->id == $model->company->id)
	        	{
	        		if(ProjectLabourRate::checkSelectedContractorInTrade($selectedContractor->id, $model->preDefinedLocationCode->id,$project))
	        		{
						$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.mcar_submitted_notification', 'site-management-defect.replyMCAR');
						$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.mcar_submitted_notification', 'site-management-defect.replyMCAR');
					}
	        	}
            }

        }

	}

	public function sendMcarRepliedNotification($project, $model)
	{
		$pic = $model->user;

        if( ! $project->isMainProject() )
		{
		    // Get Main Project.
            $project = Project::where("id", $project->parent_project_id)->first();
		}

        $pm = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

        $users = $pm->add($pic)->toArray();

        $this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.mcar_replied_notification', 'site-management-defect.replyMCAR');
        $this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.mcar_replied_notification', 'site-management-defect.replyMCAR');
	}

	public function sendMcarSiteVerifiedNotification($project, $model)
	{
		$users = SiteManagementUserPermission::getAssignedPms($project, SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)->toArray();

		$this->sendEmailNotificationByUsers($project, $model, $users, 'siteManagement.mcar_verified_notification', 'site-management-defect.replyMCAR');
		$this->sendSystemNotificationByUsers($project, $model, $users, 'siteManagement.mcar_verified_notification', 'site-management-defect.replyMCAR');
	}

	public function getPendingSiteManagementDefectBackcharges(User $user, $includeFutureTasks, $project = null)
	{
		$pendingSiteManagementDefectBackcharges = [];

		if($project)
		{
			foreach($project->siteManagementDefects as $siteManagementDefect)
			{
				foreach($siteManagementDefect->siteManagementDefectBackchargeDetails as $backchargeDetail)
				{
					$isCurrentVerifier = Verifier::isCurrentVerifier($user, $backchargeDetail);
					$proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $backchargeDetail) : $isCurrentVerifier;

					$backchargeDetail['is_future_task'] = ! $isCurrentVerifier;

					if($proceed)
					{
						$pendingSiteManagementDefectBackcharges[$backchargeDetail->id] = $backchargeDetail;
					}
				}
			}
		}
		else
		{
			$records = Verifier::where('verifier_id', $user->id)->where('object_type', SiteManagementDefectBackchargeDetail::class)->get();

			foreach($records as $record)
			{
				$siteManagementBackChargeDetail = SiteManagementDefectBackchargeDetail::find($record->object_id);

				if( ! $siteManagementBackChargeDetail ) continue;

				$project = $siteManagementBackChargeDetail->siteManagementDefect->project;
				$isCurrentVerifier = Verifier::isCurrentVerifier($user, $siteManagementBackChargeDetail);
				$proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $siteManagementBackChargeDetail) : $isCurrentVerifier;

				$siteManagementBackChargeDetail['is_future_task'] = ! $isCurrentVerifier;

				if($project && $proceed)
				{
					$pendingSiteManagementDefectBackcharges[$siteManagementBackChargeDetail->id] = $siteManagementBackChargeDetail;
				}

			}
		}

		return $pendingSiteManagementDefectBackcharges;
	}
}