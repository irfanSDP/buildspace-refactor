<?php

use PCK\Base\Upload;
use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\ContractManagementVerifier;
use PCK\Buildspace\PostContractClaimRevision;
use PCK\Buildspace\PostContractClaim;
use PCK\Buildspace\ProjectCodeSetting;
use PCK\ContractManagementModule\ProjectContractManagementModule;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\Arrays;
use PCK\ModulePermission\ModulePermission;
use PCK\Projects\Project;
use PCK\TenderDocumentFolders\TenderDocumentFile;
use PCK\TenderDocumentFolders\TenderDocumentFolder;
use PCK\TenderDocumentFolders\TenderDocumentFolderRepository;
use PCK\Users\User;
use PCK\Verifier\Verifier;
use PCK\Subsidiaries\Subsidiary;
use PCK\Notifications\EmailNotifier;

class BuildSpaceAccessController extends \BaseController {

    private $tenderDocumentFolderRepo;
    private $emailNotifier;

    public function __construct(TenderDocumentFolderRepository $tenderDocumentFolderRepo, EmailNotifier $emailNotifier)
    {
        $this->tenderDocumentFolderRepo = $tenderDocumentFolderRepo;
        $this->emailNotifier            = $emailNotifier;
    }

    public function projectPushToTenderingState(Project $project)
    {
        // will create a default tender document folder to store document(s) derived from BuildSpace
        $folder = $this->tenderDocumentFolderRepo->findByFolderName($project, TenderDocumentFolder::DEFAULT_BQ_FOLDER_NAME, true);

        if( ! $folder )
        {
            $folder = $this->tenderDocumentFolderRepo->createNewSystemFolderUnderBQFiles($project, TenderDocumentFolder::DEFAULT_BQ_FOLDER_NAME);
        }

        // will save the exported BQ file into the folder
        $file = Input::file('file');

        $upload = new Upload;
        $upload->setPresetUserId(Input::get('posted_by'));

        try
        {
            $upload->process($file);
        }
        catch(Exception $exception)
        {
            // Something went wrong. Log it.
            Log::error($exception);

            $errors = array(
                'name'  => $file->getClientOriginalName(),
                'size'  => $file->getSize(),
                'error' => $exception->getMessage()
            );

            // Return error
            return Response::json($errors, 400);
        }

        return $this->saveUploadedFile($upload, $folder);
    }

    public function projectAddendum(Project $project)
    {
        $folderName = TenderDocumentFolder::DEFAULT_ADDENDUM_FOLDER_NAME . ' ' . Input::get('addendumVersion');

        // will create a default tender document folder to store document(s) derived from BuildSpace
        $folder = $this->tenderDocumentFolderRepo->findByFolderName($project, $folderName, true);

        if( ! $folder )
        {
            $folder = $this->tenderDocumentFolderRepo->createNewSystemFolderUnderBQFiles($project, $folderName);
        }

        // will save the exported BQ file into the folder
        $file = Input::file('file');

        $upload = new Upload;
        $upload->setPresetUserId(Input::get('posted_by'));

        try
        {
            $upload->process($file);
        }
        catch(Exception $exception)
        {
            // Something went wrong. Log it.
            Log::error($exception);

            $errors = array(
                'name'  => $file->getClientOriginalName(),
                'size'  => $file->getSize(),
                'error' => $exception->getMessage()
            );

            // Return error
            return Response::json($errors, 400);
        }

        $filename = substr($project->title, 0, 200) . ' - Addendum ' . Input::get('addendumVersion');

        return $this->saveUploadedFile($upload, $folder, $filename);
    }

    private function saveUploadedFile(Upload $upload, TenderDocumentFolder $folder, $customFileName = null)
    {
        // once everyday is okay then return response to BuildSpace for further processing
        // If it now has an id, it should have been successful.
        if( ! $upload->id )
        {
            return Response::json('Error', 400);
        }

        $fileParts = pathinfo($upload->filename);

        //save project doc file and links it to cabinet file
        $tenderDocumentFile                            = new TenderDocumentFile();
        $tenderDocumentFile->filename                  = $customFileName ?? $fileParts['filename'];
        $tenderDocumentFile->cabinet_file_id           = $upload->id;
        $tenderDocumentFile->tender_document_folder_id = $folder->id;

        $tenderDocumentFile->save();

        // this creates the response structure for jquery file upload
        $success               = new stdClass();
        $success->name         = $customFileName ?? $upload->filename;
        $success->size         = $upload->size;
        $success->url          = $upload->download_url;
        $success->thumbnailUrl = $upload->generateThumbnailURL();
        $success->deleteUrl    = action('TenderDocumentFoldersController@uploadDelete', array( $folder->project->id, $upload->id ));
        $success->deleteType   = 'POST';
        $success->fileID       = $upload->id;

        return Response::json(array( 'files' => array( $success ) ), 200);
    }

    public function getDefaultTenderingStageUsers(Project $project)
    {
        // Group Contract Division is the first choice as the main company.
        $mainCompany = $project->selectedCompanies()
            ->where('contract_group_id', '=', \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::GROUP_CONTRACT))
            ->first();

        if( ! $mainCompany )
        {
            // If Group Contract Division is non-existant, Business Unit is selected as the main company.
            $mainCompany = $project->selectedCompanies()
                ->where('contract_group_id', '=', \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::PROJECT_OWNER))
                ->first();
        }

        $tenderDocumentCompany = null;

        if( $project->contractGroupTenderDocumentPermission )
        {
            $tenderDocumentCompany = $project->selectedCompanies()
                ->where('contract_group_id', '=', $project->contractGroupTenderDocumentPermission->contract_group_id)
                ->first();
        }

        $userIds = array();

        $allUsers = $mainCompany->getAllUsers();

        if( $tenderDocumentCompany ) $allUsers = $allUsers->merge($tenderDocumentCompany->getAllUsers());

        foreach($allUsers as $user)
        {
            // Only send the id back to BuildSpace if it exists in BuildSpace.
            if( $user->getBsUser() ) Arrays::addUnique($userIds, $user->id);
        }

        return Response::json(array( 'userIds' => $userIds ), 200);
    }

    public function getDefaultPostContractStageUsers(Project $project)
    {
        $bu = $project->selectedCompanies()
            ->where('contract_group_id', '=', \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::PROJECT_OWNER))
            ->first();

        $allUsers = $bu->getAllUsers();

        $gcd = $project->selectedCompanies()
            ->where('contract_group_id', '=', \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::GROUP_CONTRACT))
            ->first();

        if( $gcd ) $allUsers = $allUsers->merge($gcd->getAllUsers());

        $pm = $project->selectedCompanies()
            ->where('contract_group_id', '=', \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::PROJECT_MANAGER))
            ->first();

        if( $pm ) $allUsers = $allUsers->merge($pm->getAllUsers());

        $tenderDocumentCompany = null;

        if( $project->contractGroupTenderDocumentPermission )
        {
            $tenderDocumentCompany = $project->selectedCompanies()
                ->where('contract_group_id', '=', $project->contractGroupTenderDocumentPermission->contract_group_id)
                ->first();

            if( $tenderDocumentCompany ) $allUsers = $allUsers->merge($tenderDocumentCompany->getAllUsers());
        }

        $userIds = array();

        foreach($allUsers as $user)
        {
            // Only send the id back to BuildSpace if it exists in BuildSpace.
            if( $user->getBsUser() ) Arrays::addUnique($userIds, $user->id);
        }

        return Response::json(array( 'userIds' => $userIds ), 200);
    }

    public function getContractManagementVerifiers(Project $project, int $moduleIdentifier)
    {
        $userIds = array();

        $record = ProjectContractManagementModule::getRecord($project->id, $moduleIdentifier);

        $verifiers = Verifier::getAssignedVerifierRecords($record);

        foreach($verifiers as $verifierRecord)
        {
            $info = array(
                'user_id'         => $verifierRecord->verifier->id,
                'sequence_number' => $verifierRecord->sequence_number,
                'days_to_verify'  => $verifierRecord->days_to_verify,
            );

            Arrays::addUnique($userIds, $info, $verifierRecord->verifier->id);
        }

        return Response::json(array( 'userIds' => $userIds ), 200);
    }

    public function sendContractManagementClaimReviewNotifications(Project $project, $moduleIdentifier, $objectId)
    {
        $success      = false;
        $errorMessage = null;

        $inputs = Input::all();

        if(array_key_exists('userId', $inputs))
        {
            $user = User::find($inputs['userId']);
    
            \Auth::login($user);
        }

        try
        {
            ContractManagementClaimVerifier::sendNotifications($project, $moduleIdentifier, $objectId);

            $success = true;
        }
        catch(Exception $e)
        {
            Log::error("Error sending notifications from BuildSpace: {$e->getMessage()}");
            $errorMessage = $e->getMessage();
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function sendContractManagementReviewNotifications(Project $project, $moduleIdentifier)
    {
        $success      = false;
        $errorMessage = null;

        try
        {
            ContractManagementVerifier::sendNotifications($project, $moduleIdentifier);

            $success = true;
        }
        catch(Exception $e)
        {
            Log::error("Error sending notifications from BuildSpace: {$e->getMessage()}");
            Log::error($e->getTraceAsString());
            $errorMessage = $e->getMessage();
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function pushToPostContract(Project $project)
    {
        $userId = Input::get('user_id');

        Auth::loginUsingId($userId);

        $bsProjectMainInformation = $project->getBsProjectMainInformation();

        // check first if project is in post contract in BuildSpace.
        if( $bsProjectMainInformation->status != \PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT )
        {
            Log::error("[BuildSpace request] Project could not be published to Post Contract. Error: BuildSpace project not in Post Contract stage.");

            return array(
                'success'      => false,
                'errorMessage' => "The project is not in Post Contract in BuildSpace",
            );
        }

        $success      = false;
        $errorMessage = null;

        try
        {
            $letterOfAward = $bsProjectMainInformation->projectStructure->letterOfAward;

            $input = array(
                'commencement_date' => $letterOfAward->contract_period_from,
                'completion_date'   => $letterOfAward->contract_period_to,
                'trade'             => $letterOfAward->pre_defined_location_code_id,
                'contract_sum'      => $bsProjectMainInformation->projectStructure->postContract->getContractSum(),
            );

            $success = $project->skipToStage(Project::STATUS_TYPE_POST_CONTRACT, array( 'selectedContractorId' => $bsProjectMainInformation->getAwardedEProjectCompany()->id, 'postContractFormInput' => $input ));

            if( ! $success ) {
                throw new Exception('Unable to skip to post contract.');
            }

            Log::info("[BuildSpace request] Project published to Post Contract (id: {$project->id})");
        }
        catch(Exception $e)
        {
            $errorMessage = $e->getMessage();

            Log::error("[BuildSpace request] Project could not be published to Post Contract. Error: {$e->getMessage()}");
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function canAccessBqEditor(Project $project, $userId)
    {
        $success      = false;
        $errorMessage = null;

        try
        {
            $user = \PCK\Users\User::find($userId);

            $success = $user->canAccessBqEditor($project);
        }
        catch(Exception $e)
        {
            Log::error("Error getting BQ Editor access status: {$e->getMessage()}");

            $errorMessage = $e->getMessage();
        }

        return array(
            'canAccess'    => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function submitTendererRate(Project $project, $userId)
    {
        $success      = false;
        $errorMessage = null;

        $tenderRepository = App::make('PCK\Tenders\TenderRepository');

        $tender = $project->latestTender;

        $rates = Input::file('ratesFile');

        $user     = User::find($userId);
        $tenderer = $user->company;

        try
        {
            $tenderRepository->saveRates($tender, $rates, $user, $tenderer);
            $success = true;
        }
        catch(ValidationException $e)
        {
            $errorMessage = $e->getMessage();
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function canAccessMasterCostData($masterCostDataId, $userId)
    {
        $success      = false;
        $errorMessage = null;

        try
        {
            $user = \PCK\Users\User::find($userId);

            $success = ModulePermission::hasPermission($user, ModulePermission::MODULE_ID_MASTER_COST_DATA);
        }
        catch(Exception $e)
        {
            Log::error("Error getting Master Cost Data access status: {$e->getMessage()}");

            $errorMessage = $e->getMessage();
        }

        return array(
            'canAccess'    => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function canAccessCostData($costDataId, $userId)
    {
        $assigned     = false;
        $isEditor     = false;
        $errorMessage = null;

        $costData = \PCK\Buildspace\CostData::find($costDataId);

        try
        {
            $user = \PCK\Users\User::find($userId);

            $assigned = \PCK\General\ObjectPermission::isAssigned($user, $costData);
            $isEditor = \PCK\General\ObjectPermission::isEditor($user, $costData);
        }
        catch(Exception $e)
        {
            Log::error("Error getting Cost Data access status: {$e->getMessage()}");

            $errorMessage = $e->getMessage();
        }

        return array(
            'canAccess'    => $assigned,
            'isEditor'     => $isEditor,
            'errorMessage' => $errorMessage,
        );
    }

    public function getFullSubsidiaryName($subsidiaryId)
    {
        $subsidiary = Subsidiary::find($subsidiaryId);

        return array(
            'fullSubsidiaryName' => $subsidiary->fullName,
        );
    }

    public function checkLicenseValidity()
    {
        $licenseRepository = App::make('PCK\Licenses\LicenseRepository');
        $isLicenseValid = $licenseRepository->checkLicenseValidity();

        return array(
            'isLicenseValid' => $isLicenseValid,
        );
    }

    public function getRoundedAmount()
    {
        $inputs = Input::all();
        $project = Project::find($inputs['eProjectOriginId']);
        $roundedAmount = $project->country->currencySetting->getRoundedAmount($inputs['amount']);

        return array(
            'roundedAmount' => $roundedAmount,
        );
    }

    public function getProportionsGroupedByIds()
    {
        $inputs = Input::all();
        $project = Project::find($inputs['eProjectOriginId']);
        $projectCodeSettingIds = explode(',', $inputs['projectCodeSettingIds']);
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $projectCodeSettings = [];

        foreach($projectCodeSettingIds as $projectCodeSettingId)
        {
            array_push($projectCodeSettings, ProjectCodeSetting::find($projectCodeSettingId));
        }

        $proportionsGroupedByIds = ProjectCodeSetting::getProportionsGroupedByIds($projectStructure, $projectCodeSettings);

        return array(
            'proportionsGroupedByIds' => $proportionsGroupedByIds,
        );
    }

    public function getSubsidiaryHierarchicalCollection()
    {
        $inputs = Input::all();
        $subsidiaryId = $inputs['subsidiaryId'];
        $subsidiaryRepository = App::make('PCK\Subsidiaries\SubsidiaryRepository');
        $subsidiaryHierarchicalCollection = $subsidiaryRepository->getHierarchicalCollection($subsidiaryId)->toArray();
        
        return array(
            'subsidiaryHierarchicalCollection' => $subsidiaryHierarchicalCollection,
        );
    }

    public function sendNewClaimRevisionInitiatedNotifications()
    {
        $claimRevisionId = Input::get('claim_revision_id');

        $success     = false;
        $errorMessage = null;

        try
        {
            $this->emailNotifier->sendNewClaimRevisionInitiatedNotifications(PostContractClaimRevision::find($claimRevisionId));

            $success = true;
        }
        catch(Exception $e)
        {
            Log::error("Error sending notification for new claim certificate initiation: {$e->getMessage()}");

            $errorMessage = $e->getMessage();
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function sendContractorClaimSubmittedNotifications()
    {
        $claimRevisionId = Input::get('claim_revision_id');

        $success     = false;
        $errorMessage = null;

        try
        {
            $this->emailNotifier->sendContractorClaimSubmittedNotifications(PostContractClaimRevision::find($claimRevisionId));

            $success = true;
        }
        catch(Exception $e)
        {
            Log::error("Error sending notification for submitted claim: {$e->getMessage()}");

            $errorMessage = $e->getMessage();
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function sendClaimApprovedNotifications()
    {
        $claimRevisionId = Input::get('claim_revision_id');

        $success     = false;
        $errorMessage = null;

        try
        {
            $this->emailNotifier->sendClaimApprovedNotifications(PostContractClaimRevision::find($claimRevisionId));

            $success = true;
        }
        catch(Exception $e)
        {
            Log::error("Error sending notification for approved claim: {$e->getMessage()}");

            $errorMessage = $e->getMessage();
        }

        return array(
            'success'      => $success,
            'errorMessage' => $errorMessage,
        );
    }

    public function getPostContractClaimTopManagementVerifiers()
    {
        $inputs  = Input::all();
        $project = Project::find($inputs['projectId']);

        $projectContractManagementModule    = ProjectContractManagementModule::getRecord($project->id, $inputs['module_identifier']);
        $normalVerifierIds                  = Verifier::getAssignedVerifierRecords($projectContractManagementModule)->lists('verifier_id');
        $assignableTopManagementVerifierIds = empty($normalVerifierIds) ? [] : array_diff($project->getTopManagementVerifiersWithProjectAccess()->lists('id'), $normalVerifierIds);

        return [
            'topManagementVerifiers' => $assignableTopManagementVerifierIds,
        ];
    }
}