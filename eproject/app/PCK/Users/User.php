<?php namespace PCK\Users;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\ContractManagementVerifier;
use PCK\Buildspace\CostData;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\General\ObjectPermission;
use PCK\ModulePermission\ModulePermission;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\Verifier\Verifier;
use PCK\Subsidiaries\Subsidiary;
use PCK\Settings\Settings;
use Zizaco\Confide\ConfideUser;
use Illuminate\Database\Eloquent\Model;
use Zizaco\Confide\ConfideUserInterface;
use Laracasts\Presenter\PresentableTrait;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\TenderFormVerifierLogs\TenderFormVerifierLog;
use PCK\OpenTenderVerifierLogs\OpenTenderVerifierLog;
use PCK\TechnicalEvaluationVerifierLogs\TechnicalEvaluationVerifierLog;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\RequestForVariation\RequestForVariationUserPermission;
use PCK\LetterOfAward\LetterOfAwardUserPermission;
use PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission;
use PCK\ContractManagementModule\ProjectContractManagementModule;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroups\ContractGroup;
use PCK\Countries\Country;
use PCK\Dashboard\DashboardGroup;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TendererTechnicalEvaluationInformation\TechnicalEvaluation;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation;
use PCK\LetterOfAward\LetterOfAward;
use PCK\RequestForInformation\RequestForInformation;
use PCK\RequestForInformation\RequestForInformationMessage;
use PCK\RiskRegister\RiskRegister;
use PCK\RiskRegister\RiskRegisterMessage;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionGroupUser;
use PCK\Inspections\InspectionVerifierTemplate;
use PCK\Inspections\InspectionSubmitter;
use PCK\Inspections\RequestForInspection;
use PCK\DailyReport\DailyReport;
use PCK\InstructionsToContractors\InstructionsToContractor;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\SystemModules\SystemModuleConfiguration;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\LetterOfAward as ConsultantManagementLetterOfAward;
use PCK\ConsultantManagement\ConsultantUser as ConsultantManagementConsultantUser;

use PCK\ExternalApplication\Client as ExtAppClient;

use PCK\Companies\Company;
use PCK\Companies\CompanyImportedUsersLog;
use PCK\EBiddingCommittees\EBiddingCommittee;
use PCK\EBiddings\EBidding;

class User extends Model implements ConfideUserInterface {

    use ConfideUser, UserSuperAdminTrait, CompanyTrait, ProjectRole, PresentableTrait, VerifyCompanyTrait, BsUserTrait;

    protected $hidden = array( 'password' );

    protected $presenter = 'PCK\Users\UserPresenter';

    const AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS = "AUTO REJECT From User Module [Blocked User]";

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $user)
        {
            Settings::initialise($user);
            $user->createBsUser();
            $user->addCompanyLogEntry();
        });

        static::updating(function(self $user)
        {
            if( $user->isDirty('password') ) $user->password_updated_at = Carbon::now();

            if( $user->isDirty('company_id') ) $user->addCompanyLogEntry();

            if($user->isDirty('account_blocked_status') && $user->account_blocked_status)
            {
                $user->removeAndRejectPendingTasks();
            }
        });

        static::updated(function(self $user)
        {
            $user->updateBsUser();
        });

        static::deleting(function(self $user)
        {
            \Log::info("Attempting to delete user [{$user->id}:{$user->username}].");

            $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

            if(( ! $vendorManagementModuleEnabled ) && $user->confirmed && $user->isPermanentAccount())
            {
                \Log::info("Unable to delete user [{$user->id}:{$user->username}]. User has already been confirmed.");

                return false;
            }

            UserCompanyLog::where('user_id', '=', $user->id)->delete();

            CompanyImportedUsersLog::where('user_id', '=', $user->id)->delete();

            $user->contractGroupProjectUsers()->delete();

            \Notifynder::deleteAll($user->id);

            $user->deleteBsUser();
        });

        static::deleted(function(self $user)
        {
            $actingUser = \Confide::user();

            if($actingUser)
            {
                \Log::info("Deleted user [{$user->id}:{$user->username}]. Action by [{$actingUser->id}:{$actingUser->username}].");
            }
            else
            {
                \Log::info("Deleted user [{$user->id}:{$user->username}]. Action by System.");
            }
        });
    }

    // Date mutations are disabled due to the issue with model serialization (which uses queried values) with inconsistent datetime formats.
    // The accessors for created_at and updated_at are set to return Carbon instances manually.
    public function getDates()
    {
        return array();
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getNameWithDesignationAttribute($value)
    {
        $designation = ( !is_null($this->designation) && (trim($this->designation)) ) ? '( ' . trim($this->designation) . ' )' : '';

        return trim($this->name . ' ' . $designation);
    }

    public function settings()
    {
        return $this->hasOne('PCK\Settings\Settings');
    }

    public function dashboardGroup($type=null)
    {
        $groupTypes = [];
        switch ($type) {
            case DashboardGroup::TYPE_E_BIDDING:
                $groupTypes[] = DashboardGroup::TYPE_E_BIDDING;
                break;
            default:
                $groupTypes[] = DashboardGroup::TYPE_DEVELOPER;
                $groupTypes[] = DashboardGroup::TYPE_MAIN_CONTRACTOR;
        }
        return DashboardGroup::join('dashboard_groups_users AS u', 'dashboard_groups.type', '=', 'u.dashboard_group_type')
            ->where('u.user_id', '=', $this->id)
            ->whereIn('dashboard_groups.type', $groupTypes)
            ->first();
    }

    public function projects()
    {
        return $this->hasMany('PCK\Projects\Project')->orderBy('id', 'desc');
    }

    public function contractGroupProjectUsers()
    {
        return $this->hasMany('PCK\ContractGroupProjectUsers\ContractGroupProjectUser');
    }

    public function architectInstructions()
    {
        return $this->hasMany('PCK\ArchitectInstructions\ArchitectInstruction');
    }

    public function architectInstructionMessages()
    {
        return $this->hasMany('PCK\ArchitectInstructionMessages\ArchitectInstructionMessage', 'created_by')->orderBy('id', 'asc');
    }

    public function architectInstructionThirdLevelMessages()
    {
        return $this->hasMany('PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessage', 'created_by')->orderBy('id', 'asc');
    }

    public function extensionOfTimes()
    {
        return $this->hasMany('PCK\ExtensionOfTimes\ExtensionOfTime')->orderBy('id', 'desc');
    }

    public function extensionOfTimeFirstLevelMessages()
    {
        return $this->hasMany('PCK\ExtensionOfTimeFirstLevelMessages\ExtensionOfTimeFirstLevelMessage')->orderBy('id', 'asc');
    }

    public function eotContractorConfirmDelay()
    {
        return $this->hasOne('PCK\ExtensionOfTimeContractorConfirmDelays\ExtensionOfTimeContractorConfirmDelay');
    }

    public function extensionOfTimeClaim()
    {
        return $this->hasOne('PCK\ExtensionOfTimeClaims\ExtensionOfTimeClaim');
    }

    public function additionalExpenses()
    {
        return $this->hasMany('PCK\AdditionalExpenses\AdditionalExpense');
    }

    public function authenticationLogs()
    {
        return $this->hasMany('PCK\AuthenticationLog\AuthenticationLog');
    }

    public function verifiers()
    {
        return $this->belongsToMany('PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation', 'tender_rot_information_user', 'user_id', 'tender_rot_information_id')
            ->withTimestamps();
    }

    public function openTenders()
    {
        return $this->belongsToMany('PCK\Tenders\Tender', 'tender_user_verifier_open_tender', 'user_id', 'tender_id')
            ->withTimestamps()
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS);
    }

    public function technicalEvaluations()
    {
        return $this->belongsToMany('PCK\Tenders\Tender', 'tender_user_technical_evaluation_verifier', 'user_id', 'tender_id')
            ->withTimestamps()
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS);
    }

    public function commitmentStatusLogs()
    {
        return $this->hasMany('PCK\ContractorsCommitmentStatusLogs\ContractorsCommitmentStatusLog');
    }

    public function requestForVariationUserPermissions()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariationUserPermission');
    }

    public function letterOfAwardUserPermissions()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardUserPermission');
    }

    public function modulePermission($moduleId = null)
    {
        if (! empty($moduleId)) {
            return $this->hasOne(ModulePermission::class)->where('module_identifier', $moduleId)->with('subsidiaries');
        } else {
            return $this->hasMany(ModulePermission::class)->with('subsidiaries');
        }
    }

    public function consultantManagementConsultantUser()
    {
        return $this->hasOne(ConsultantManagementConsultantUser::class);
    }

    public function extAppClient()
    {
        return $this->hasOne(ExtAppClient::class, 'user_id');
    }
    
    public function getDocumentManagementFoldersByProject(Project $project, $onlyRoots = false)
    {
        $query = \DB::table('document_management_folders as d')
            ->where('d.project_id', '=', $project->id);

        if( ! $this->isSuperAdmin() )
        {
            $contractGroup = $this->getAssignedCompany($project)->getContractGroup($project);

            $query->where('d.contract_group_id', '=', $contractGroup->id);
        }

        if( $onlyRoots )
        {
            $query->whereNull('d.parent_id');
            $query->where('d.depth', '=', 0);
        }

        return $query->orderBy('d.priority', 'ASC')
            ->orderBy('d.lft', 'ASC')
            ->orderBy('d.depth', 'ASC')
            ->get();
    }

    public function isActive()
    {
        return ($this->confirmed && ( ! $this->account_blocked_status ));
    }

    public function isProjectCreator()
    {
        return $this->hasCompanyRoles([Role::PROJECT_OWNER]) && $this->isGroupAdmin();
    }

    public function isGroupAdmin()
    {
        return $this->attributes['is_admin'];
    }

    public function isCurrentVerifier($id)
    {
        $eBidding = EBidding::where('project_id', $id)->first();

        if (!$eBidding) {
            return false;
        }
        return Verifier::isCurrentVerifier($this, $eBidding);
    }

    public function isEbiddingCommittee($id)
    {
        $results = EBiddingCommittee::where('project_id',$id)->where('user_id',$this->id)->where('is_committee',true)->first();

        if($results)
        {
            return true;
        }
        return false;
    }

    public function isTopManagementVerifier()
    {
        return ModulePermission::hasPermission($this, ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS);
    }

    public function canSendEmailNotifications(Project $project)
    {
        $eligibleRoles = array(Role::PROJECT_OWNER, Role::GROUP_CONTRACT);
        $tenderDocumentContractGroup = $project->contractGroupTenderDocumentPermission->contractGroup->group;
        $restrictedProjectStatuses = [Project::STATUS_TYPE_DESIGN, Project::STATUS_TYPE_POST_CONTRACT, Project::STATUS_TYPE_COMPLETED];

        return $this->hasCompanyProjectRole($project, $eligibleRoles, $tenderDocumentContractGroup) && (!in_array($project->status_id, $restrictedProjectStatuses));
    }

    public function getPasswordUpdatedAtAttribute($value)
    {
        if( empty( $value ) ) return null;

        return Carbon::parse($value);
    }

    public function getSiteManagementUserPermissionsMobileFormat()
    {
        $projectTableName = with(new Project)->getTable();

        $siteManagementUserPermTableName = with(new SiteManagementUserPermission)->getTable();
        $dbh                             = \DB::getPdo();
        $sth                             = $dbh->prepare("SELECT perm.id, module_identifier, project_id, user_id, site, qa_qc_client, pm, qs, is_editor, is_viewer, is_rate_editor, perm.created_at
            FROM " . $siteManagementUserPermTableName . " perm
            JOIN " . $projectTableName . " p ON perm.project_id = p.id
            WHERE module_identifier = :moduleIdentifier AND user_id = :userId
            AND p.deleted_at IS NULL");

        $sth->execute([
            'moduleIdentifier' => SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT,
            'userId'           => $this->id
        ]);

        $permissions = $sth->fetchAll(\PDO::FETCH_ASSOC);

        if( ! empty( $permissions ) )
        {
            foreach($permissions as $idx => $permission)
            {
                $permissions[ $idx ]['created_at'] = Carbon::parse($permission['created_at'], \Config::get('app.timezone'))->toAtomString();
            }
        }

        return $permissions;
    }

    public function getRequestForVariationUserPermissionsByProject($project = null)
    {
        $query = $this->requestForVariationUserPermissions()->join('request_for_variation_user_permission_groups', 'request_for_variation_user_permissions.request_for_variation_user_permission_group_id', '=', 'request_for_variation_user_permission_groups.id');

        if($project)
        {
            $query->where('request_for_variation_user_permission_groups.project_id', $project->id);
        }

        return $query->get()->reject(function($permission) {
            return is_null($permission->group->project);
        });
    }

    public function getLetterOfAwardUserPermissionsByProject(Project $project, $withEditorOption = false)
    {
        $query = $this->letterOfAwardUserPermissions()->where('project_id', $project->id);

        if( $withEditorOption )
        {
            $query = $query->where('is_editor', $withEditorOption);
        }

        return $query->get();
    }

    public function getVisibleCostData()
    {
        $costDataRecords = CostData::orderBy('created_at', 'desc')->get();

        if( ! ModulePermission::hasPermission($this, ModulePermission::MODULE_ID_COST_DATA) )
        {
            $costDataRecords = $costDataRecords->filter(function($record)
            {
                return ObjectPermission::isAssigned($this, $record);
            });
        }

        return $costDataRecords;
    }

    protected function addCompanyLogEntry()
    {
        if( $this->isSuperAdmin() ) return false;

        $actingUser = \Confide::user();

        if( ! $actingUser ) $actingUser = $this;

        return UserCompanyLog::create(array(
            'user_id'    => $this->id,
            'company_id' => $this->company_id,
            'created_by' => $actingUser->id,
        ));
    }

    public function getPendingReviews($includeFutureTasks, Project $project = null)
    {
        $tenderRepository                         = \App::make('PCK\Tenders\TenderRepository');
        $requestForVariationRepository            = \App::make('PCK\RequestForVariation\RequestForVariationRepository');
        $accountCodeSettingRepository             = \App::make('PCK\AccountCodeSettings\AccountCodeSettingRepository');
        $siteManagementDefectBackchargeRepository = \App::make('PCK\SiteManagement\SiteManagementDefectRepository');
        $vendorMangementRepository                = \App::make('PCK\VendorManagement\VendorManagementRepository');
        $projectReportRepository                  = \App::make('PCK\ProjectReport\ProjectReportRepository');
        $siteManagementSiteDiaryRepository        = \App::make('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryRepository');

        $pendingPostContractReviews = ContractManagementVerifier::getPendingRecords($this, !$includeFutureTasks, $project)
            ->merge(ContractManagementClaimVerifier::getPendingRecords($this, !$includeFutureTasks, $project))
            ->merge($requestForVariationRepository->getPendingRequestForVariation($this, $includeFutureTasks, $project))
            ->merge($accountCodeSettingRepository->getPendingAccountCodeSettings($this, $includeFutureTasks, $project))
            ->merge($siteManagementDefectBackchargeRepository->getPendingSiteManagementDefectBackcharges($this, $includeFutureTasks, $project))
            ->merge($projectReportRepository->getPendingApprovalProjectReports($this, $includeFutureTasks, $project))
            ->sortByDesc('daysPending');

        $pendingSiteModuleReviews  = RequestForInspection::getPendingRequestForInspections($this, $includeFutureTasks, $project)
                                                            ->merge($siteManagementSiteDiaryRepository->getPendingSiteManagementSiteDiary($this, $includeFutureTasks, $project))
                                                            ->merge(DailyReport::getPendingSiteManagementDailyReport($this, $includeFutureTasks, $project))
                                                            ->merge(InstructionsToContractor::getPendingSiteManagementInstructionToContractor($this, $includeFutureTasks, $project));

        $pendingTenderReviews          = new Collection($tenderRepository->getPendingTenderProcessesByUser($this, $includeFutureTasks, $project));
        $pendingVendorMangementReviews = $vendorMangementRepository->getPendingVendorManagementTasks($this, $includeFutureTasks);

        $pendingList = [
            'tendering'         => $pendingTenderReviews,
            'postContract'      => $pendingPostContractReviews,
            'siteModule'        => $pendingSiteModuleReviews,
            'vendorManagement'   => $pendingVendorMangementReviews,
        ];

        if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
        {
            $dsEvaluationRepository = \App::make('PCK\DigitalStar\Evaluation\DsEvaluationRepository');
            $pendingList['digitalStar'] = $dsEvaluationRepository->getPendingApprovals($this, $includeFutureTasks);
        }

        return $pendingList;
    }

    public function hasPendingReviews($includeFutureTasks, Project $project = null)
    {
        foreach($this->getPendingReviews($includeFutureTasks, $project) as $category => $categoryReviews)
        {
            if( ! $categoryReviews->isEmpty() ) return true;
        }

        return false;
    }

    public function getPendingReviewTenderResubmissions(Project $project = null)
    {
        $tenderRepository          = \App::make('PCK\Tenders\TenderRepository');
        return $tenderRepository->getPendingApprovalTenderResubmission($this, true, $project);
    }

    public function isTransferable(Project $project = null)
    {
        if($this->hasPendingReviews(true, $project)) return false;
        if(!empty($this->getUserPermissionsInLetterOfAward($project))) return false;
        if(!$this->getRequestForVariationUserPermissionsByProject($project)->isEmpty()) return false;
        if(!empty(ContractManagementUserPermission::getModulesOfAssignedUsers($this, $project))) return false;
        if(!empty(SiteManagementUserPermission::getModulesOfAssignedUsers($this, $project))) return false;
        if(!empty($this->getUserPermissionsInRequestForInspection($project))) return false;
        if(!empty($this->getPendingVendorPerformanceEvaluationFormApprovals($project))) return false;

        return true;
    }

    public function getListOfTenderingPendingReviewTasks($includeFutureTasks, Project $project = null)
    {
        $listOfTenderingPendingReviewTasks = [];

        foreach($this->getPendingReviews($includeFutureTasks, $project)['tendering'] as $tenderingPendingReview)
        {
            $task = [
                'user_id'           => $this->id,
                'user'              => $this->name,
                'project_id'        => $tenderingPendingReview['project_id'],
                'company_id'        => $tenderingPendingReview['company_id'],
                'project_title'     => $tenderingPendingReview['project_title'],
                'project_reference' => $tenderingPendingReview['project_reference'],
                'module'            => $tenderingPendingReview['module'],
                'tender_id'         => array_key_exists('tender_id', $tenderingPendingReview) ? $tenderingPendingReview['tender_id'] : null,
                'is_future_task'    => array_key_exists('is_future_task', $tenderingPendingReview) ? $tenderingPendingReview['is_future_task'] : false
            ];

            if(array_key_exists('obj_id', $tenderingPendingReview))
            {
                $task['obj_id'] = $tenderingPendingReview['obj_id'];
            }

            array_push($listOfTenderingPendingReviewTasks, $task);
        }

        return $listOfTenderingPendingReviewTasks;
    }

    public function getListOfPostContractPendingReviewTasks($includeFutureTasks, Project $project = null)
    {
        $listOfPostContractPendingReviewTasks = [];

        foreach($this->getPendingReviews($includeFutureTasks, $project)['postContract'] as $postContractPendingReview)
        {
            array_push($listOfPostContractPendingReviewTasks, [
                'user_id'           => $this->id,
                'user'              => $this->name,
                'project_id'        => $postContractPendingReview->getProject()->id,
                'company_id'        => $postContractPendingReview->getProject()->business_unit_id,
                'project_title'     => $postContractPendingReview->getProject()->title,
                'project_reference' => $postContractPendingReview->getProject()->reference,
                'module'            => $postContractPendingReview->getModuleName(),
            ]);
        }

        return $listOfPostContractPendingReviewTasks;
    }

    public function getListOfSiteModulePendingReviewTasks($includeFutureTasks, Project $project = null)
    {
        $listOfPostContractPendingReviewTasks = [];

        foreach($this->getPendingReviews($includeFutureTasks, $project)['siteModule'] as $siteModulePendingReview)
        {
            array_push($listOfPostContractPendingReviewTasks, [
                'user_id'           => $this->id,
                'user'              => $this->name,
                'project_id'        => $siteModulePendingReview->getProject()->id,
                'company_id'        => $siteModulePendingReview->getProject()->business_unit_id,
                'project_title'     => $siteModulePendingReview->getProject()->title,
                'project_reference' => $siteModulePendingReview->getProject()->reference,
                'module'            => $siteModulePendingReview->getModuleName(),
            ]);
        }

        return $listOfPostContractPendingReviewTasks;
    }

    public function getUserPermissionsInLetterOfAward(Project $project = null)
    {
        $listOfLetterOfAwardUserPermissions = [];

        $letterOfAwardUserPermissions = $this->letterOfAwardUserPermissions->filter(function($permission) use ($project)
        {
            if(is_null($project) && !is_null($permission->project)) return ($permission->user->id == $this->id);

            return ($permission->project  && ($permission->project->id == $project->id) && ($permission->user_id == $this->id));
        });

        foreach($letterOfAwardUserPermissions as $permission)
        {
            array_push($listOfLetterOfAwardUserPermissions, [
                'id'                => $permission->id,
                'user_id'           => $permission->user->id,
                'user'              => $permission->user->name,
                'project_id'        => $permission->project->id,
                'company_id'        => $permission->project->business_unit_id,
                'project_title'     => $permission->project->title,
                'project_reference' => $permission->project->reference,
                'role'              => LetterOfAwardUserPermission::getRoleNameByModuleId($permission->module_identifier),
            ]);
        }

        return $listOfLetterOfAwardUserPermissions;
    }

    public function getUserPermissionsInRequestOfVariation(Project $project = null)
    {
        $listOfRfvUserPermissions = [];

        foreach($this->getRequestForVariationUserPermissionsByProject($project) as $permission)
        {
            if(is_null($permission->group->project)) continue;

            array_push($listOfRfvUserPermissions, [
                'id'                => $permission->id,
                'user_id'           => $permission->user->id,
                'user'              => $permission->user->name,
                'project_id'        => $permission->group->project->id,
                'company_id'        => $permission->group->project->business_unit_id,
                'project_title'     => $permission->group->project->title,
                'project_reference' => $permission->group->project->reference,
                'module'            => $permission->getModuleName(),
                'permission_group'  => $permission->group->name,
                'role'              => RequestForVariationUserPermission::getRoleNameByModuleId($permission->module_id),
            ]);
        }

        return $listOfRfvUserPermissions;
    }

    public function getUserPermissionsInContractManagement(Project $project = null)
    {
        $listOfContractManagementUserPermissions = [];

        foreach(ContractManagementUserPermission::getModulesOfAssignedUsers($this, $project) as $permission)
        {
            array_push($listOfContractManagementUserPermissions, [
                'id'                => $permission->id,
                'user_id'           => $permission->user->id,
                'user'              => $permission->user->name,
                'project_id'        => $permission->project->id,
                'company_id'        => $permission->project->business_unit_id,
                'project_title'     => $permission->project->title,
                'project_reference' => $permission->project->reference,
                'module'            => ProjectContractManagementModule::getModuleNames($permission->module_identifier),
            ]);
        }

        return $listOfContractManagementUserPermissions;
    }

    public function getUserPermissionsInSiteManagement(Project $project = null)
    {
        $listOfSiteManagementUserPermissions = [];

        foreach(SiteManagementUserPermission::getModulesOfAssignedUsers($this, $project) as $permission)
        {
            array_push($listOfSiteManagementUserPermissions, [
                'id'                => $permission->id,
                'user_id'           => $permission->user->id,
                'user'              => $permission->user->name,
                'project_id'        => $permission->project->id,
                'company_id'        => $permission->project->business_unit_id,
                'project_title'     => $permission->project->title,
                'project_reference' => $permission->project->reference,
                'module'            => SiteManagementUserPermission::getModuleNames($permission->module_identifier),
            ]);
        }

        return $listOfSiteManagementUserPermissions;
    }

    public function getUserPermissionsInRequestForInspection(Project $project = null)
    {
        $records = [];

        $inspectionGroups = $project ? $project->inspectionGroups : InspectionGroup::all();
        
        foreach($inspectionGroups as $inspectionGroup)
        {
            foreach($inspectionGroup->inspectionSubmitters as $inspectionSubmitter)
            {
                if($inspectionSubmitter->user->id != $this->id) continue;

                array_push($records, [
                    'id'                => $inspectionSubmitter->id,
                    'user_id'           => $inspectionSubmitter->user->id,
                    'user'              => $inspectionSubmitter->user->name,
                    'project_title'     => $inspectionSubmitter->group->project->title,
                    'company_id'        => $inspectionSubmitter->group->project->business_unit_id,
                    'project_reference' => $inspectionSubmitter->group->project->reference,
                    'module'            => trans('inspection.requestForInspection'),
                    'group'             => $inspectionSubmitter->group->name,
                    'role'              => trans('inspection.submitForApproval'),
                ]);
            }

            foreach($inspectionGroup->inspectionVerifiers as $inspectionVerifier)
            {
                if($inspectionVerifier->user->id != $this->id) continue;

                array_push($records, [
                    'id'                => $inspectionVerifier->id,
                    'user_id'           => $inspectionVerifier->user->id,
                    'user'              => $inspectionVerifier->user->name,
                    'project_title'     => $inspectionVerifier->group->project->title,
                    'company_id'        => $inspectionSubmitter->group->project->business_unit_id,
                    'project_reference' => $inspectionVerifier->group->project->reference,
                    'module'            => trans('inspection.requestForInspection'),
                    'group'             => $inspectionVerifier->group->name,
                    'role'              => trans('inspection.verifiers'),
                ]);
            }

            // other user-defined groups
            foreach($inspectionGroup->inspectionGroupUsers as $inspectionGroupUser)
            {
                if($inspectionGroupUser->user->id != $this->id) continue;

                array_push($records, [
                    'id'                => $inspectionGroupUser->id,
                    'user_id'           => $inspectionGroupUser->user->id,
                    'user'              => $inspectionGroupUser->user->name,
                    'project_title'     => $inspectionGroupUser->group->project->title,
                    'company_id'        => $inspectionSubmitter->group->project->business_unit_id,
                    'project_reference' => $inspectionGroupUser->group->project->reference,
                    'module'            => trans('inspection.requestForInspection'),
                    'group'             => $inspectionGroupUser->group->name,
                    'role'              => $inspectionGroupUser->role->name,
                ]);
            }
        }

        return $records;
    }

    public function getPendingVendorPerformanceEvaluationFormApprovals(Project $project = null)
    {
        $records = [];

        $pendingEvaluationFormIds = Verifier::where('object_type', '=', get_class(new VendorPerformanceEvaluationCompanyForm))
            ->where('verifier_id', '=', $this->id)
            ->whereNull('approved')
            ->lists('object_id');

        $evaluationFormsQuery = VendorPerformanceEvaluationCompanyForm::whereIn('evaluator_company_id', $this->getAllCompanies()->lists('id'))
            ->where('status_id', '=', VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION)
            ->whereIn('id', $pendingEvaluationFormIds);

        if($project) $evaluationFormsQuery->whereHas('vendorPerformanceEvaluation', function($q) use ($project){
            $q->where('project_id', '=', $project->id);
        });

        $evaluationForms = $evaluationFormsQuery->get();

        foreach($evaluationForms as $evaluationForm)
        {
            array_push($records, [
                'id'                => $evaluationForm->id,
                'project_title'     => $evaluationForm->vendorPerformanceEvaluation->project->title,
                'project_reference' => $evaluationForm->vendorPerformanceEvaluation->project->reference,
                'evaluated_company' => $evaluationForm->company->name,
                'company_id'        => $evaluationForm->evaluator_company_id,
            ]);
        }

        return $records;
    }

    public function canCreateRequestForVariationForProject(Project $project)
    {
        $count = \DB::table('request_for_variation_user_permission_groups AS g')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('g.id')
            ->where('g.project_id', '=', $project->id)
            ->where('p.module_id', '=', RequestForVariationUserPermission::ROLE_SUBMIT_RFV)
            ->where('p.user_id', '=', $this->id)
            ->distinct('g.id')
            ->count();

        return $count != 0;
    }

    public function canAccessRequestForVariationContractAndContingencySumForProject(Project $project)
    {
        $count = \DB::table('request_for_variation_user_permission_groups AS g')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('g.id')
            ->where('g.project_id', '=', $project->id)
            ->where('p.module_id', '=', RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION)
            ->where('p.user_id', '=', $this->id)
            ->distinct('g.id')
            ->count();

        return $count != 0;
    }

    public function canAccessRequestForVariationVOReportForProject(Project $project)
    {
        $count = \DB::table('request_for_variation_user_permission_groups AS g')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('g.id')
            ->where('g.project_id', '=', $project->id)
            ->where('p.can_view_vo_report', true)
            ->where('p.user_id', '=', $this->id)
            ->distinct('g.id')
            ->count();

        return $count != 0;
    }

    public function getRequestForVariationUserPermissionGroups(Project $project)
    {
        return \DB::table('request_for_variation_user_permission_groups AS g')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('g.*')
            ->where('g.project_id', '=', $project->id)
            ->where('p.user_id', '=', $this->id)
            ->distinct('g.id')
            ->orderBy('g.created_at', 'DESC')
            ->get();
    }

    public function getProjectCompanyName(Project $project, $timestamp = null)
    {
        $assignedCompany = $this->getAssignedCompany($project, $timestamp);
        return ($assignedCompany) ? $assignedCompany->getNameInProject($project) : null;
    }

    public function canSubmitClaim($project)
    {
        $buildSpaceProjectMainInfo = $project->getBsProjectMainInformation();

        $lastestClaimRevision = $buildSpaceProjectMainInfo->projectStructure->postContract->postContractClaimRevisions->first();

        return $this->hasCompanyProjectRole($project, Role::CONTRACTOR) && $lastestClaimRevision->claimCertificate && ( ! $lastestClaimRevision->claim_submission_locked );
    }

    public function getDashboardSubsidiaries(Country $country, Array $fromDate, Array $toDate, Array $filterParams = [], $currentPage=0, $pageSize=0)
    {
        $data = [];

        if($this->dashboardGroup()->type == DashboardGroup::TYPE_DEVELOPER)
        {
            $data = $this->dashboardGroup()->getDeveloperSubsidiaries($this, $country, $fromDate, $toDate, $filterParams, ['current_page' => $currentPage, 'page_size' => $pageSize]);
        }

        return $data;
    }

    public function getDashboardMainContracts(Country $country, Array $fromDate, Array $toDate, Array $filterParams = [], $currentPage=0, $pageSize=0)
    {
        $data = [];

        if($this->dashboardGroup()->type == DashboardGroup::TYPE_MAIN_CONTRACTOR)
        {
            $data = $this->dashboardGroup()->getMainContracts($this, $country, $fromDate, $toDate, $filterParams, ['current_page' => $currentPage, 'page_size' => $pageSize]);
        }

        return $data;
    }

    public function getDeveloperDashboardADataByCountry(Country $country, Array $fromDate, Array $toDate)
    {
        $data = [];

        if($this->dashboardGroup()->type == DashboardGroup::TYPE_DEVELOPER)
        {
            $data = $this->dashboardGroup()->getDeveloperDashboardADataByUserAndCountry($this, $country, $fromDate, $toDate);
        }

        return $data;
    }

    public function getDeveloperDashboardBData(Country $country, Array $subsidiaryIds, Array $fromDate, Array $toDate)
    {
        $data = [];

        if($this->dashboardGroup()->type == DashboardGroup::TYPE_DEVELOPER)
        {
            $data = $this->dashboardGroup()->getDeveloperDashboardBDataByUserAndCountry($this, $country, $subsidiaryIds, $fromDate, $toDate);
        }

        return $data;
    }

    public function getSubsidiariesSavingOrOverRunByWorkCategories(Country $country, Array $subsidiaryIds, Array $fromDate, Array $toDate)
    {
        $data = [];

        if($this->dashboardGroup()->type == DashboardGroup::TYPE_DEVELOPER)
        {
            $data = $this->dashboardGroup()->getDeveloperDashboardCDataByUserAndCountry($this, $country, $subsidiaryIds, $fromDate, $toDate);
        }

        return $data;
    }

    public function getMainContractorDashboardADataByCountry(Country $country, Array $fromDate, Array $toDate)
    {
        $data = [];

        if($this->dashboardGroup()->type == DashboardGroup::TYPE_MAIN_CONTRACTOR)
        {
            $data = $this->dashboardGroup()->getMainContractorDashboardADataByUserAndCountry($this, $country, $fromDate, $toDate);
        }

        return $data;
    }

    public function getDashboardProjects(Country $country=null, Array $fromDate, Array $toDate)
    {
        $postContractProjects = $this->dashboardGroup()->getPostContractProjects($country, $fromDate, $toDate);
        $closedTenderProjects = $this->dashboardGroup()->getClosedTenderProjects($country, $fromDate, $toDate);
        
        if($closedTenderProjects)
            return ($postContractProjects) ? $postContractProjects->merge($closedTenderProjects) : $closedTenderProjects;
        else
            return $postContractProjects;
    }

    public function getDashboardCountries()
    {
        return $this->dashboardGroup()->getCountries();
    }

    public function getOverallBudgetVersusContractSumAndVOByWorkCategories(Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getOverallBudgetVersusContractSumAndVOByWorkCategories($this, $country, $fromDate, $toDate);
    }

    public function getProcurementMethodSummary(Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getProcurementMethodSummary($this, $country, $fromDate, $toDate);
    }

    public function getProjectStatusSummary(Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getProjectStatusSummary($this, $country, $fromDate, $toDate);
    }

    public function getETenderWaiverStatusSummary(Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getWaiverStatusSummary($country, 'tender', $fromDate, $toDate);
    }

    public function getEAuctionWaiverStatusSummary(Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getWaiverStatusSummary($country, 'auction', $fromDate, $toDate);
    }

    public function getETenderWaiverOtherStatusDetails(Subsidiary $subsidiary, Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getWaiverOtherStatusDetails($subsidiary, 'tender', $country, $fromDate, $toDate);
    }

    public function getEAuctionWaiverOtherStatusDetails(Subsidiary $subsidiary, Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getWaiverOtherStatusDetails($subsidiary, 'auction', $country, $fromDate, $toDate);
    }

    public function getOverallCertifiedPayment(Country $country, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getOverallCertifiedPayment($country, $fromDate, $toDate);
    }

    public function getOverallCertifiedPaymentBySubsidiaries(Country $country, Array $subsidiaryIds, Array $fromDate, Array $toDate)
    {
        return $this->dashboardGroup()->getOverallCertifiedPaymentBySubsidiaries($country, $subsidiaryIds, $fromDate, $toDate);
    }

    public function removeAndRejectTenderingTasks()
    {
        $tasks = $this->getListOfTenderingPendingReviewTasks(true);

        foreach($tasks as $task)
        {
            $tender = Tender::find($task['tender_id']);

            if($task['is_future_task'])
            {
                $obj = null;
                $verifiable = false;

                switch($task['module'])
                {
                    case TenderRecommendationOfTendererInformation::RECOMMENDATION_OF_TENDERER_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->recommendationOfTendererInformation;
                        }
                        break;
                    case TenderListOfTendererInformation::LIST_OF_TENDERER_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->listOfTendererInformation;
                        }
                        break;
                    case TenderCallingTenderInformation::CALLING_TENDER_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->callingTenderInformation;
                        }
                        break;
                    case TechnicalEvaluation::TECHNICAL_ASSESSMENT_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->technicalEvaluation;
                            $verifiable = true;
                        }
                        break;
                    case OpenTenderAwardRecommendation::AWARD_RECOMMENDATION_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->openTenderAwardRecommendtion;
                            $verifiable = true;
                        }
                        break;
                    case LetterOfAward::LETTER_OF_AWARD_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->project->letterOfAward;
                            $verifiable = true;
                        }
                        break;
                    case RequestForInformation::REQUEST_FOR_INFORMATION_MODULE_NAME:
                        if(array_key_exists('obj_id', $task) && !empty($task['obj_id']) && $obj = RequestForInformationMessage::find($task['obj_id']))
                        {
                            $verifiable = true;
                        }
                        break;
                    case RiskRegister::RISK_REGISTER_MODULE_NAME:
                        if(array_key_exists('obj_id', $task) && !empty($task['obj_id']) && $obj = RiskRegisterMessage::find($task['obj_id']))
                        {
                            $verifiable = true;
                        }
                        break;
                }

                if($obj)
                {
                    if($verifiable)
                    {
                        Verifier::removeVerifier($obj, $task['user_id']);
                    }
                    else
                    {
                        $obj->verifiers()->detach($task['user_id']);
                    }
                }
            }
            else
            {
                switch($task['module'])
                {
                    case TenderRecommendationOfTendererInformation::RECOMMENDATION_OF_TENDERER_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->recommendationOfTendererInformation;

                            $obj->rejectVerification();

                            $log                  = new TenderFormVerifierLog();
                            $log->user_id         = $task['user_id'];
                            $log->type            = TenderRecommendationOfTendererInformation::USER_VERIFICATION_REJECTED;
                            $log->verifier_remark = self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS;

                            $obj->verifierLogs()->save($log);
                        }
                        break;
                    case TenderListOfTendererInformation::LIST_OF_TENDERER_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->listOfTendererInformation;

                            $obj->rejectVerification();

                            $log                  = new TenderFormVerifierLog();
                            $log->user_id         = $task['user_id'];
                            $log->type            = TenderListOfTendererInformation::USER_VERIFICATION_REJECTED;
                            $log->verifier_remark = self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS;

                            $obj->verifierLogs()->save($log);
                        }
                        break;
                    case TenderCallingTenderInformation::CALLING_TENDER_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->callingTenderInformation;

                            $obj->rejectVerification();

                            $log                  = new TenderFormVerifierLog();
                            $log->user_id         = $task['user_id'];
                            $log->type            = TenderCallingTenderInformation::USER_VERIFICATION_REJECTED;
                            $log->verifier_remark = self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS;

                            $obj->verifierLogs()->save($log);
                        }
                        break;
                    case Tender::OPEN_TENDER_MODULE_NAME:
                        if($tender)
                        {
                            $tender->rejectOpenTenderVerification();

                            $log          = new OpenTenderVerifierLog();
                            $log->user_id = $task['user_id'];
                            $log->type    = Tender::USER_VERIFICATION_REJECTED;
                            
                            $tender->openTenderVerifierLogs()->save($log);
                        }
                        break;
                    case TechnicalEvaluation::TECHNICAL_OPENING_MODULE_NAME:
                    case TechnicalEvaluation::TECHNICAL_ASSESSMENT_MODULE_NAME:
                        if($tender)
                        {
                            $tender->rejectTechnicalEvaluationVerification();

                            $log          = new TechnicalEvaluationVerifierLog();
                            $log->user_id = $task['user_id'];
                            $log->type    = Tender::USER_VERIFICATION_REJECTED;
                            
                            $tender->technicalEvaluationVerifierLogs()->save($log);
                        }
                        break;
                    case OpenTenderAwardRecommendation::AWARD_RECOMMENDATION_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->openTenderAwardRecommendtion;

                            $record = Verifier::getCurrentVerifierRecord($obj);
                            if($record)
                            {
                                $record->approved = false;
                                $record->verified_at = Carbon::now();

                                $record->save();
                            }

                            $func = $obj->getOnRejectedFunction();

                            call_user_func($func);

                            Verifier::where('object_type', '=', get_class($obj))
                                ->where('object_id' ,'=', $obj->id)
                                ->where('verifier_id', '=', $task['user_id'])
                                ->update(array('remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS));

                            if( \PCK\Forum\ObjectThread::objectHasThread($obj) )
                            {
                                $thread = \PCK\Forum\ObjectThread::getObjectThread($obj);
                                $thread->users()->sync(array());
                            }
                        }
                        break;
                    case LetterOfAward::LETTER_OF_AWARD_MODULE_NAME:
                        if($tender)
                        {
                            $obj = $tender->project->letterOfAward;

                            $record = Verifier::getCurrentVerifierRecord($obj);
                            if($record)
                            {
                                $record->approved = false;
                                $record->verified_at = Carbon::now();

                                $record->save();
                            }

                            $func = $obj->getOnRejectedFunction();

                            call_user_func($func);

                            Verifier::where('object_type', '=', get_class($obj))
                                ->where('object_id' ,'=', $obj->id)
                                ->where('verifier_id', '=', $task['user_id'])
                                ->update(array('remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS));

                            if( \PCK\Forum\ObjectThread::objectHasThread($obj) )
                            {
                                $thread = \PCK\Forum\ObjectThread::getObjectThread($obj);
                                $thread->users()->sync(array());
                            }
                        }
                        break;
                    case RequestForInformation::REQUEST_FOR_INFORMATION_MODULE_NAME:
                        if(array_key_exists('obj_id', $task) && !empty($task['obj_id']) && $obj = RequestForInformationMessage::find($task['obj_id']))
                        {
                            $record = Verifier::getCurrentVerifierRecord($obj);
                            if($record)
                            {
                                $record->approved = false;
                                $record->verified_at = Carbon::now();

                                $record->save();
                            }

                            Verifier::where('object_type', '=', get_class($obj))
                                ->where('object_id' ,'=', $obj->id)
                                ->where('verifier_id', '=', $task['user_id'])
                                ->update(array('remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS));

                            if( \PCK\Forum\ObjectThread::objectHasThread($obj) )
                            {
                                $thread = \PCK\Forum\ObjectThread::getObjectThread($obj);
                                $thread->users()->sync(array());
                            }
                        }
                        break;
                    case RiskRegister::RISK_REGISTER_MODULE_NAME:
                        if(array_key_exists('obj_id', $task) && !empty($task['obj_id']) && $obj = RiskRegisterMessage::find($task['obj_id']))
                        {
                            $record = Verifier::getCurrentVerifierRecord($obj);
                            if($record)
                            {
                                $record->approved = false;
                                $record->verified_at = Carbon::now();

                                $record->save();
                            }

                            Verifier::where('object_type', '=', get_class($obj))
                                ->where('object_id' ,'=', $obj->id)
                                ->where('verifier_id', '=', $task['user_id'])
                                ->update(array('remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS));

                            if( \PCK\Forum\ObjectThread::objectHasThread($obj) )
                            {
                                $thread = \PCK\Forum\ObjectThread::getObjectThread($obj);
                                $thread->users()->sync(array());
                            }
                        }
                        break;
                }
            }
        }
    }

    public function removeAndRejectPendingTasks()
    {
        //add sub methods to handle rejection of current pending tasks and remove from verifier list for future tasks
        $this->removeAndRejectTenderingTasks();
        $this->removeAndRejectPostContractTasks();

        //to remove user from project contract groups
        $this->contractGroupProjectUsers()->where('user_id', '=', $this->id)->delete();

        //to remove user from letter of award user permission
        \DB::table('letter_of_award_user_permissions AS p')
            ->where('p.user_id', '=', $this->id)
            ->delete();
        
        //to remove user from site managemment user permission
        \DB::table('site_management_user_permissions AS p')
            ->where('p.user_id', '=', $this->id)
            ->delete();

        \PCK\ModulePermission\ModulePermission::where('user_id', '=', $this->id)
            ->delete();
    }

    private function removeAndRejectPostContractTasks()
    {
        $this->removeAndRejectContractManagementVerifierTasks();
        $this->removeAndRejectContractManagementClaimVerifierTasks();
        $this->removeAndRejectRequestForVariationTasks();
        $this->removeAndRejectAccountCodeSettingTasks();
        $this->removeAndRejectDefectBackchargeTasks();
        $this->removeAndRejectInspectionPermissions();
    }

    private function removeAndRejectInspectionPermissions()
    {
        InspectionGroupUser::where('user_id', '=', $this->id)->delete();
        InspectionVerifierTemplate::where('user_id', '=', $this->id)->delete();
        InspectionSubmitter::where('user_id', '=', $this->id)->delete();

        $verifierRecords = Verifier::where('verifier_id', '=', $this->id)
            ->where('object_type', '=', get_class(new Inspection))
            ->whereNull('approved')
            ->get();

        foreach($verifierRecords as $record)
        {
            if( ! $record->object ) continue;

            if( ! Verifier::isBeingVerified($record->object) || ! Verifier::isAVerifier($this, $record->object) ) continue;

            if( Verifier::isCurrentVerifier($this, $record->object) )
            {
                $record->update(array('approved' => false, 'remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS, 'verified_at' => Carbon::now()));

                $record->object->getOnRejectedFunction();
            }
            else
            {
                \PCK\Verifier\Verifier::removeVerifier($record->object, $this->id);
            }
        }
    }

    private function removeAndRejectContractManagementVerifierTasks()
    {
        $records = ContractManagementVerifier::getPendingRecords($this, false);

        ContractManagementVerifier::whereIn('id', $records->filter(function($record){
            return ! $record['is_future_task'];
        })->lists('id'))->update(array('approved' => false, 'remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS, 'verified_at' => Carbon::now()));

        ContractManagementVerifier::whereIn('id', $records->filter(function($record){
            return $record['is_future_task'];
        })->lists('id'))->forceDelete();

        foreach($records as $record)
        {
            $object = ProjectContractManagementModule::getRecord($record->bsProject->mainInformation->eproject_origin_id, \PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD);

            \PCK\Verifier\Verifier::removeVerifier($object, $this->id);
        }

        $success = ContractManagementUserPermission::where('module_identifier', '=', \PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD)
            ->where('user_id', '=', $this->id)
            ->delete();
    }

    private function removeAndRejectContractManagementClaimVerifierTasks()
    {
        $records = ContractManagementClaimVerifier::getPendingRecords($this, false);
        $contractManagementClaimVerifierQueryBuilder = ContractManagementClaimVerifier::whereIn('id', $records->filter(function($record){
            return ! $record['is_future_task'];
        })->lists('id'));

        $contractManagementClaimVerifierQueryBuilder->update(array('approved' => false, 'remarks' => self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS, 'verified_at' => Carbon::now()));
        $contractManagementClaimVerifierQueryBuilder->get()->each(function($record){
            $moduleClass = ContractManagementClaimVerifier::getModuleClass($record->module_identifier);

            $claim = $moduleClass::find($record->object_id);
            $claim->onReview($record->bsProject->mainInformation->getEProjectProject(), $record->module_identifier);
        });

        ContractManagementClaimVerifier::whereIn('id', $records->filter(function($record){
            return $record['is_future_task'];
        })->lists('id'))->forceDelete();

        $verifierRecords = \PCK\Verifier\Verifier::where('verifier_id', '=', $this->id)
            ->whereNull('approved')
            ->where('object_type', '=', \PCK\ContractManagementModule\ProjectContractManagementModule::class)
            ->get();

        foreach($verifierRecords as $record)
        {
            if( ! is_null($record->object) ) \PCK\Verifier\Verifier::removeVerifier($record->object, $this->id);
        }

        ContractManagementUserPermission::whereIn('module_identifier', array(
                \PCK\Buildspace\PostContractClaim::TYPE_CLAIM_CERTIFICATE,
                \PCK\Buildspace\PostContractClaim::TYPE_VARIATION_ORDER,
                \PCK\Buildspace\PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE,
                \PCK\Buildspace\PostContractClaim::TYPE_DEPOSIT,
                \PCK\Buildspace\PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM,
                \PCK\Buildspace\PostContractClaim::TYPE_PURCHASE_ON_BEHALF,
                \PCK\Buildspace\PostContractClaim::TYPE_ADVANCED_PAYMENT,
                \PCK\Buildspace\PostContractClaim::TYPE_WORK_ON_BEHALF,
                \PCK\Buildspace\PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE,
                \PCK\Buildspace\PostContractClaim::TYPE_PENALTY,
                \PCK\Buildspace\PostContractClaim::TYPE_PERMIT,
                \PCK\Buildspace\PostContractClaim::TYPE_WATER_DEPOSIT,))
            ->where('user_id', '=', $this->id)
            ->delete();
    }

    private function removeAndRejectRequestForVariationTasks()
    {
        $requestForVariationRepository = \App::make('PCK\RequestForVariation\RequestForVariationRepository');

        $verifierRecords = \PCK\Verifier\Verifier::where('object_type', '=', \PCK\RequestForVariation\RequestForVariation::class)
            ->whereNull('approved')
            ->where('verifier_id', '=', $this->id)
            ->get();

        foreach($verifierRecords as $record)
        {
            if( ! $record->object || ! $record->object->project ) continue;

            if( \PCK\Verifier\Verifier::isCurrentVerifier($this, $record->object) )
            {
                \PCK\Verifier\Verifier::approve($record->object, false, $this);

                $requestForVariationRepository->logAction(
                    $record->object,
                    $this,
                    \PCK\RequestForVariation\RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL,
                    \PCK\RequestForVariation\RequestForVariationActionLog::ACTION_TYPE_RFV_REJECTED,
                    $this->id,
                    false,
                    self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS
                );

                $record->object->status = \PCK\RequestForVariation\RequestForVariation::STATUS_PENDING_VERIFICATION;
                $record->object->save();

                if( \PCK\Forum\ObjectThread::objectHasThread($record->object) )
                {
                    $thread = \PCK\Forum\ObjectThread::getObjectThread($record->object);
                    $thread->users()->sync(array());
                }

                \PCK\Verifier\Verifier::deleteLog($record->object);
            }
            else
            {
                \PCK\Verifier\Verifier::removeVerifier($record->object, $this->id);
            }
        }

        RequestForVariationUserPermission::where('user_id', '=', $this->id)->delete();
    }

    private function removeAndRejectAccountCodeSettingTasks()
    {
        $accountCodeSettingRepository = \App::make('PCK\AccountCodeSettings\AccountCodeSettingRepository');

        $accountCodeSettings = $accountCodeSettingRepository->getPendingAccountCodeSettings($this, true);

        foreach($accountCodeSettings as $object)
        {
            if( $object['is_future_task'] )
            {
                \PCK\Verifier\Verifier::removeVerifier($object, $this->id);
            }
            else
            {
                unset($object->is_future_task);

                \PCK\Verifier\Verifier::updateVerifierRemarks($object, self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS);

                $verifierRecord = \PCK\Verifier\Verifier::getCurrentVerifierRecord($object);

                $verifierRecord->approved = false;

                $verifierRecord->verified_at = Carbon::now();

                $verifierRecord->save();

                $object->status = \PCK\AccountCodeSettings\AccountCodeSetting::STATUS_OPEN;
                $object->save();
            }
        }

        \PCK\ModulePermission\ModulePermission::where('module_identifier', '=', \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE)
            ->where('user_id', '=', $this->id)
            ->delete();
    }

    private function removeAndRejectDefectBackchargeTasks()
    {
        $siteManagementDefectBackchargeRepository = \App::make('PCK\SiteManagement\SiteManagementDefectRepository');

        $backcharges = $siteManagementDefectBackchargeRepository->getPendingSiteManagementDefectBackcharges($this, true);

        foreach($backcharges as $backcharge)
        {
            if( $backcharge['is_future_task'] )
            {
                \PCK\Verifier\Verifier::removeVerifier($backcharge, $this->id);
            }
            else
            {
                unset($backcharge->is_future_task);

                \PCK\Verifier\Verifier::updateVerifierRemarks($backcharge, self::AUTO_REJECT_PENDING_TASK_BLOCKED_USER_LOG_REMARKS);

                $verifierRecord = \PCK\Verifier\Verifier::getCurrentVerifierRecord($backcharge);

                $verifierRecord->approved = false;

                $verifierRecord->verified_at = Carbon::now();

                $verifierRecord->save();

                $project = $backcharge->siteManagementDefect->project;

                $defect = \PCK\SiteManagement\SiteManagementDefect::find($backcharge->siteManagementDefect->id);
                $defect->status_id = \PCK\SiteManagement\SiteManagementDefect::STATUS_BACKCHARGE_REJECTED;
                $defect->save();

                $defectBackchargeDetail = \PCK\SiteManagement\SiteManagementDefectBackchargeDetail::find($backcharge->id);
                $defectBackchargeDetail->status_id = \PCK\SiteManagement\SiteManagementDefectBackchargeDetail::STATUS_BACKCHARGE_REJECTED;
                $defectBackchargeDetail->save();
            }
        }

        \PCK\SiteManagement\SiteManagementUserPermission::where('user_id', '=', $this->id)
            ->where('module_identifier', '=', \PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)
            ->delete();
    }

    public function getBuildspaceAccessFlagByStage(Project $project, $projectStatus)
    {
        $bsUser    = $this->getBsUser();
        $canAccess = false;

        switch($projectStatus)
        {
            case BsProjectUserPermission::STATUS_PROJECT_BUILDER:
                $canAccess = true;
            break; 
            case BsProjectUserPermission::STATUS_TENDERING:
                // find BU users
                $buCompany = $project->selectedCompanies()
                    ->where('contract_group_id', '=', \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::PROJECT_OWNER))
                    ->first();

                $buCompanyUserIds = $buCompany->getActiveUsers()->lists('id');

                // find tender document users
                $tenderDocumentCompanyUserIds = [];

                if( $project->contractGroupTenderDocumentPermission )
                {
                    $tenderDocumentCompany = $project->selectedCompanies()
                        ->where('contract_group_id', '=', $project->contractGroupTenderDocumentPermission->contract_group_id)
                        ->first();

                    if($tenderDocumentCompany)
                    {
                        $tenderDocumentCompanyUsers = $tenderDocumentCompany->getActiveUsers();
                        $tenderDocumentCompanyUserIds = $tenderDocumentCompanyUsers->lists('id');
                    }
                }

                // find GCD users
                $gcdCompanyUserIds = [];
                $gcdCompany = $project->selectedCompanies()
                    ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))
                    ->first();

                if($gcdCompany)
                {
                    $contractGroupRepository = \App::make('PCK\ContractGroups\ContractGroupRepository');
                    $gcdContractGroup        = $contractGroupRepository->findById($gcdCompany->pivot->contract_group_id);

                    $gcdCompanyUserIds = $project->contractGroupProjectUsers->filter(function($record) use ($gcdContractGroup) {
                        return ($record->contractGroup->id === $gcdContractGroup->id);
                    })->lists('user_id');
                }

                // order of checking is important here
                $userBuildspaceAccessCheckFuncs = [
                    // check if user has tender document access
                    function(self $user) use ($tenderDocumentCompanyUserIds)
                    {
                        return in_array($user->id, $tenderDocumentCompanyUserIds);
                    },
                    // check if user is assigned as GCD
                    function(self $user) use ($gcdCompanyUserIds)
                    {
                        return in_array($user->id, $gcdCompanyUserIds);
                    },
                    function(self $user) use ($buCompanyUserIds, $gcdCompany)
                    {
                        $isBuUser = in_array($user->id, $buCompanyUserIds);

                        if($isBuUser)
                        {
                            // if BU company has GCD, then BU user cannot access buildspace
                            // if BU company has no GCD, then BU user can access buildspace
                            return is_null($gcdCompany);
                        }
                    }
                ];

                // runs callbacks in sequential order
                // once callback returns true, terminate
                foreach($userBuildspaceAccessCheckFuncs as $func)
                {
                    if($func($this))
                    {
                        $canAccess = true;
                        break;
                    }
                }
            break;
            case BsProjectUserPermission::STATUS_POST_CONTRACT:
                $canAccess = true;
            break;
            default:
                \Log::error('User@getBuildspaceAccessByStage(), BS project status : ' . $projectStatus);
        }

        return $canAccess;
    }

    public function isConsultantManagementEditorByRole(ConsultantManagementContract $consultantManagementContract, $role)
    {
        return User::select('users.id')
                ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$consultantManagementContract->id.'
                AND consultant_management_user_roles.role = '.$role.'
                AND users.id = '.$this->id.'
                AND consultant_management_user_roles.editor IS TRUE
                AND users.confirmed IS TRUE
                AND users.account_blocked_status IS FALSE')
                ->count();
    }

    public function hasAccessToConsultantManagementByRole(ConsultantManagementContract $consultantManagementContract, $role)
    {
        $exists = User::select('users.id', 'users.name')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->join('consultant_management_contracts', 'consultant_management_user_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
            ->where('consultant_management_user_roles.role', '=', $role)
            ->where('users.id', '=', $this->id)
            ->whereRaw('users.confirmed IS TRUE AND users.account_blocked_status IS FALSE')
            ->groupBy('users.id')
            ->count();
        
        return !empty($exists);
    }

    public function hasAccessToConsultantManagementCallingRfp(ConsultantManagementContract $consultantManagementContract)
    {
        $company = Company::select('companies.id AS id')
            ->join('consultant_management_company_roles', 'consultant_management_company_roles.company_id', '=', 'companies.id')
            ->join('consultant_management_contracts', 'consultant_management_company_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
            ->whereRaw('consultant_management_company_roles.calling_rfp IS TRUE')
            ->groupBy('companies.id')
            ->first();

        return $company && in_array($company->id, $this->getAllCompanyIds());
    }

    public function isConsultantManagementCallingRfpEditor(ConsultantManagementContract $consultantManagementContract)
    {
        $companyRole = ConsultantManagementCompanyRole::select('consultant_management_company_roles.company_id AS company_id', 'consultant_management_company_roles.role')
            ->join('consultant_management_contracts', 'consultant_management_company_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
            ->whereRaw('consultant_management_company_roles.calling_rfp IS TRUE')
            ->first();

        return $companyRole && in_array($companyRole->company_id, $this->getAllCompanyIds()) && $this->isConsultantManagementEditorByRole($consultantManagementContract, $companyRole->role);
    }
    
    public function isConsultantManagementParticipantConsultant()
    {
        $companyIds = $this->getAllCompanyIds();

        if(!empty($companyIds))
        {
            $count = \DB::table('consultant_management_roles_contract_group_categories')
            ->join('contract_group_categories', 'consultant_management_roles_contract_group_categories.contract_group_category_id', '=', 'contract_group_categories.id')
            ->join('companies', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
            ->where('consultant_management_roles_contract_group_categories.role', '=', ConsultantManagementContract::ROLE_CONSULTANT)
            ->whereIn('companies.id', $companyIds)
            ->count();

            return ($count);
        }

        return false;
    }

    public function isConsultantManagementConsultantUser()
    {
        return ($this->consultantManagementConsultantUser);
    }

    public function getPurgeDateAttribute($value)
    {
        if( is_null($value) ) return null;

        return Carbon::parse($value);
    }

    public function isTemporaryAccount()
    {
        return ! is_null($this->purge_date);
    }

    public function isPermanentAccount()
    {
        return is_null($this->purge_date);
    }

    public function getConsultantManagementPendingReviews(ConsultantManagementContract $contract=null)
    {
        $pendingROCReviews              = ConsultantManagementRecommendationOfConsultant::getPendingReviewsByUser($this, $contract);
        $pendingLOCReviews              = ConsultantManagementListOfConsultant::getPendingReviewsByUser($this, $contract);
        $pendingCallingRfpReviews       = ConsultantManagementCallingRfp::getPendingReviewsByUser($this, $contract);
        $pendingOpenRfpReviews          = ConsultantManagementOpenRfp::getPendingReviewsByUser($this, $contract);
        $pendingRfpResubmissionReviews  = ConsultantManagementOpenRfp::getPendingResubmissionReviewsByUser($this, $contract);
        $pendingApprovalDocumentReviews = ApprovalDocument::getPendingReviewsByUser($this, $contract);
        $pendingLetterOfAwardReviews    = ConsultantManagementLetterOfAward::getPendingReviewsByUser($this, $contract);

        return [
            'roc'               => $pendingROCReviews,
            'loc'               => $pendingLOCReviews,
            'calling_rfp'       => $pendingCallingRfpReviews,
            'open_rfp'          => $pendingOpenRfpReviews,
            'rfp_resubmission'  => $pendingRfpResubmissionReviews,
            'approval_document' => $pendingApprovalDocumentReviews,
            'loa'               => $pendingLetterOfAwardReviews
        ];
    }

    public function canViewVendorProfile($companyId = null)
    {
        $belongsToCompany = $companyId ? in_array($companyId, $this->getAllCompanyIds()) : false;

        $involvedInProjects = $this->contractGroupProjectUsers()->exists();

        $allowedVendorManagementPermissions = [
            \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW,
            \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT,
            \PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION,
            \PCK\VendorManagement\VendorManagementUserPermission::TYPE_WATCH_LIST_VIEW,
        ];

        $userPermissions = \PCK\VendorManagement\VendorManagementUserPermission::getUserPermissions();

        $permissions = [];

        if(!empty($userPermissions))
        {
            $permissions = array_key_exists($this->id, $userPermissions) ? $userPermissions[$this->id] : [];
        }

        $hasVendorManagementPermissions = !empty(array_intersect($allowedVendorManagementPermissions, $permissions));

        return $this->isSuperAdmin() || $this->isTopManagementVerifier() || $belongsToCompany || $involvedInProjects || $hasVendorManagementPermissions || $this->hasConsultantManagementCompanyRoles();
    }
}