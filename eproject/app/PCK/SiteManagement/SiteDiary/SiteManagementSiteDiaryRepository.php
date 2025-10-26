<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Events\Dispatcher;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;
use PCK\Users\User;
use PCK\Verifier\Verifier;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryMachinery;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryLabour;
use PCK\SiteManagement\Machinery;
use PCK\SiteManagement\Labour;
use PCK\Base\Helpers;

class SiteManagementSiteDiaryRepository extends BaseModuleRepository {

	protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public static function processQuery($user, $project){

		  $query = SiteManagementSiteDiaryGeneralFormResponse::where("project_id", $project->id);	
		  return $query; 
    }

    public function getVerifiers($project)
    {
        $verifiers = [];
		$siteDiaryUsers = SiteManagementUserPermission::getAssignedVerifiers($project, SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY);

        return $siteDiaryUsers;
    }

	public function submitForApproval(SiteManagementSiteDiaryGeneralFormResponse $siteManagementSiteDiaryGeneralFormResponse, $input)
    {
        $verifiers = array_filter($input['verifiers'], function($value)
        {
            return $value != "";
        });

        if( empty( $verifiers ) )
        {
            $siteManagementSiteDiaryGeneralFormResponse->status = SiteManagementSiteDiaryGeneralFormResponse::STATUS_APPROVED;
            $siteManagementSiteDiaryGeneralFormResponse->save();

			Verifier::setVerifierAsApproved(\Confide::user(), $siteManagementSiteDiaryGeneralFormResponse);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $siteManagementSiteDiaryGeneralFormResponse);

            $siteManagementSiteDiaryGeneralFormResponse->submitted_for_approval_by = \Confide::user()->id;
            $siteManagementSiteDiaryGeneralFormResponse->status                    = SiteManagementSiteDiaryGeneralFormResponse::STATUS_PENDING_FOR_APPROVAL;
            $siteManagementSiteDiaryGeneralFormResponse->save();

            Verifier::sendPendingNotification($siteManagementSiteDiaryGeneralFormResponse);
        }
    }

    public function insertIntoSiteManagementSiteDiaryMachinery($id, $input, $project)
	{
		$machinery = Machinery::all();

		$input = Helpers::replaceUnderscoreWithSpaces($input);
		
		foreach($machinery as $record)
		{
			if(isset($input[$record->name . "-machinery-id"]) && $input[$record->name])
			{
				$formRecord["machinery_id"] = $record->id;
				$formRecord["value"] = $input[$record->name];
				$formRecord["site_diary_id"] = $id;

				$siteManagementSiteDiaryMachinery = SiteManagementSiteDiaryMachinery::where("machinery_id", $record->id)->where("site_diary_id", $id)->first();

				if(isset($siteManagementSiteDiaryMachinery))
				{
					$siteManagementSiteDiaryMachinery->update($formRecord);
				}
				else
				{
					SiteManagementSiteDiaryMachinery::create($formRecord);
				}
			}
		}
	}

	public function insertIntoSiteManagementSiteDiaryLabour($id, $input, $project)
	{		
		$labours = Labour::all();

		$input = Helpers::replaceUnderscoreWithSpaces($input);

		foreach($labours as $record)
		{
			if(isset($input[$record->name . "-labour-id"]) && $input[$record->name])
			{
				$formRecord["labour_id"] = $record->id;
				$formRecord["value"] = $input[$record->name];
				$formRecord["site_diary_id"] = $id;

				$siteManagementSiteDiaryLabour = SiteManagementSiteDiaryLabour::where("labour_id", $record->id)->where("site_diary_id", $id)->first();

				if(isset($siteManagementSiteDiaryLabour))
				{
					$siteManagementSiteDiaryLabour->update($formRecord);
				}
				else
				{
					SiteManagementSiteDiaryLabour::create($formRecord);
				}
			}
		}
	}


    public function getPendingSiteManagementSiteDiary(User $user, $includeFutureTasks, $project = null)
    {
        $pendingSiteDiary = [];

		if($project)
		{
			foreach($project->siteManagementSiteDiaryGeneralFormResponse as $siteDiary)
			{
                $isCurrentVerifier = Verifier::isCurrentVerifier($user, $siteDiary);
                $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $siteDiary) : $isCurrentVerifier;
				$daysPending = Helpers::getDaysPending($siteDiary);

                if($proceed)
                {
					$siteDiary['is_future_task'] = ! $isCurrentVerifier;
					$siteDiary['daysPending']    = $daysPending;

                    $pendingSiteDiary[$siteDiary->id] = $siteDiary;
                }
			}
		}
		else
		{
            $records = Verifier::where('verifier_id', $user->id)
                ->where('object_type', SiteManagementSiteDiaryGeneralFormResponse::class)
                ->get();

            foreach($records as $record)
            {
                $siteDiary = SiteManagementSiteDiaryGeneralFormResponse::find($record->object_id);

                if($siteDiary)
                {
                    $isCurrentVerifier  = Verifier::isCurrentVerifier($user, $siteDiary);
                    $proceed            = $includeFutureTasks ? Verifier::isAVerifierInline($user, $siteDiary) : $isCurrentVerifier;
					$daysPending 		= Helpers::getDaysPending($siteDiary);

                    if($siteDiary->project && $proceed)
                    {
                        $siteDiary['is_future_task'] = ! $isCurrentVerifier;
                        $siteDiary['company_id']     = $siteDiary->project->business_unit_id;
						$siteDiary['daysPending']    = $daysPending;

                        $pendingSiteDiary[$siteDiary->id] = $siteDiary;
                    }
                }
            }
		}

		return $pendingSiteDiary;
    }

}