<?php namespace PCK\Projects;

use Fenos\Notifynder\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\AssignCompaniesLogs\AssignCompaniesLog;
use PCK\Buildspace\ProjectCodeSetting;
use PCK\Companies\Company;
use PCK\CompanyProject\CompanyProject;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Helpers\DateTime;
use PCK\Helpers\StringOperations;
use PCK\ProjectRole\ProjectRole;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\Tenders\Tender;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\DailyLabourReports\ProjectLabourRate;
use PCK\Users\User;
use PCK\RequestForVariation\RequestForVariation;
use PCK\Tenders\SubmitTenderRate;
use PCK\ModulePermission\ModulePermission;
use PCK\ProjectReport\ProjectReportUserPermission;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectSectionalCompletionDate\ProjectSectionalCompletionDate;
use PCK\EBiddings\EBidding;

class Project extends Model implements StatusType {

    use SoftDeletingTrait;

    use ContractGroupRelation,
        ContractTypesProjectDetailRelationTrait,
        ContractualClaimTrait,
        ProjectScopesTrait,
        BsProjectTrait,
        DocumentsTrait,
        ProgressChecklistTrait,
        SkipStageTrait,
        DocumentControlTrait,
        ProjectRoleTrait,
        SubProjectTrait;

    protected $fillable = array(
        'business_unit_id',
        'contract_id',
        'title',
        'reference',
        'reference_suffix',
        'address',
        'description',
        'running_number',
        'subsidiary_id',
        'country_id',
        'state_id',
        'current_tender_status',
        'work_category_id',
        'status_id',
        'created_by',
        'updated_by',
        'parent_project_id',
        'open_tender',
        'e_bidding'
    );

    CONST TYPE_MAIN_PROJECT = 1;
    CONST TYPE_SUB_PACKAGE  = 2;

    protected $with = array( 'contract', 'pam2006Detail' );

    public function getTitleAttribute($value)
    {
        return trim($value);
    }

    public function getShortTitleAttribute()
    {
        return StringOperations::shorten($this->title, 100);
    }

    public function getAddressAttribute($value)
    {
        return trim($value);
    }

    public function getDescriptionAttribute($value)
    {
        return trim($value);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $project)
        {
            \Log::info("Created Project \"{$project->title}\" (id: {$project->id})");

            $project->generateMasterRootFolders();

            if( ! $project->isImport() )
            {
                $project->syncProjectToBuildspace();

                $project->grantBsProjectPermissionToUsersBsGroup();
            }

            ProjectRole::initialise($project);
            ProjectLabourRate::initialise($project);
        });

        static::deleting(function(self $project)
        {
            \Log::info("Deleting Project \"{$project->title}\" (id: {$project->id})");

            ProjectCodeSetting::flushAllRecords($project->getBsProjectMainInformation()->projectStructure);

            foreach($project->subProjects as $subProject)
            {
                $subProject->delete();
            }

            if( ! $project->deleteFromBuildspace() ) return false;
        });
    }

    public function siteManagementDefects()
    {
        return $this->hasMany('PCK\SiteManagement\SiteManagementDefect', 'project_id', 'id');
    }

    public function siteManagementSiteDiaryGeneralFormResponse()
    {
        return $this->hasMany('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse', 'project_id', 'id');
    }

    public function dailyReports()
    {
        return $this->hasMany('PCK\DailyReport\DailyReport', 'project_id', 'id');
    }

    public function instructionsToContractors()
    {
        return $this->hasMany('PCK\InstructionsToContractors\InstructionsToContractor', 'project_id', 'id');
    }

    public function openTenderPageInformation()
    {
        return $this->hasMany('PCK\Tenders\OpenTenderPageInformation');
    }

    public function projectLabourRates()
    {
        return $this->hasMany('PCK\DailyLabourReports\ProjectLabourRate', 'project_id')->orderBy('labour_type', 'asc');
    }

    public function businessUnit()
    {
        return $this->belongsTo('PCK\Companies\Company', 'business_unit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public function contract()
    {
        return $this->belongsTo('PCK\Contracts\Contract')->rememberForever();
    }

    public function threads()
    {
        return $this->hasMany('PCK\Forum\Thread');
    }

    public function selectedCompanies()
    {
        return $this->belongsToMany('PCK\Companies\Company')
            ->withPivot('contract_group_id')
            ->withTimestamps()
            ->orderBy('name', 'ASC');
    }

    public function getSelectedContractor()
    {
        if( ! $this->latestTender ) return null;

        $contractor = $this->latestTender->selectedFinalContractors()->where('selected_contractor', '=', true)->first();

        if ($contractor) {
            return $contractor;
        }

        return null;
    }

    public function contractGroupProjectUsers()
    {
        return $this->hasMany('PCK\ContractGroupProjectUsers\ContractGroupProjectUser');
    }

    public function getProjectEditors()
    {
        return $this->contractGroupProjectUsers->reject(function($user)
        {
            return ! $user->is_contract_group_project_owner;
        });
    }

    public function getDocumentFolderRoots()
    {
        return DocumentManagementFolder::getRootsByProject($this);
    }

    public function tenders()
    {
        return $this->hasMany('PCK\Tenders\Tender')->orderBy('id', 'DESC');
    }

    public function latestTender()
    {
        return $this->hasOne('PCK\Tenders\Tender')->orderBy('id', 'DESC');
    }

    public function firstTender()
    {
        return $this->hasOne('PCK\Tenders\Tender')->orderBy('count', 'ASC');
    }

    public function getCompletionPeriodMetricAttribute()
    {
        $value = ( ! is_null($this->firstTender->recommendationOfTendererInformation) ) ? $this->firstTender->recommendationOfTendererInformation->completion_period_metric : TenderRecommendationOfTendererInformation::COMPLETION_PERIOD_METRIC_TYPE_MONTHS;

        return TenderRecommendationOfTendererInformation::getCompletionPeriodMetricText($value);
    }

    public function country()
    {
        return $this->belongsTo('PCK\Countries\Country');
    }

    public function workCategory()
    {
        return $this->belongsTo('PCK\WorkCategories\WorkCategory');
    }

    public function state()
    {
        return $this->belongsTo('PCK\States\State');
    }

    public function subsidiary()
    {
        return $this->belongsTo('PCK\Subsidiaries\Subsidiary');
    }

    public function conversations()
    {
        return $this->hasMany('PCK\Conversations\Conversation');
    }

    public function contractGroupTenderDocumentPermission()
    {
        return $this->hasOne('PCK\ProjectContractGroupTenderDocumentPermissions\ProjectContractGroupTenderDocumentPermission');
    }

    public function assignCompaniesLogs()
    {
        return $this->hasMany('PCK\AssignCompaniesLogs\AssignCompaniesLog')
            ->orderBy('id', 'ASC');
    }

    public function weatherRecords()
    {
        return $this->hasMany('PCK\WeatherRecords\WeatherRecord');
    }

    public function tenderDocumentFolders()
    {
        return $this->hasMany('PCK\TenderDocumentFolders\TenderDocumentFolder');
    }

    public function parentTenderDocumentFolders()
    {
        return $this->hasMany('PCK\TenderDocumentFolders\TenderDocumentFolder')
            ->where('parent_id', '=', null);
    }

    public function technicalEvaluationSetReference()
    {
        return $this->hasOne('\PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference', 'project_id');
    }

    public function requestForVariationUserPermissionGroups()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariationUserPermissionGroup')
            ->orderBy('created_at', 'DESC');
    }

    public function requestForVariations()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariation')
            ->orderBy('created_at', 'DESC');
    }

    public function requestForVariationContractAndContingencySum()
    {
        return $this->hasOne('PCK\RequestForVariation\RequestForVariationContractAndContingencySum');
    }

    public function letterOfAward()
    {
        return $this->hasOne('PCK\LetterOfAward\LetterOfAward');
    }

    public function letterOfAwardUserPermissions()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardUserPermission');
    }

    public function emailNotifications()
    {
        return $this->hasMany('PCK\EmailNotification\EmailNotification');
    }

    public function accountCodeSetting()
    {
        return $this->hasOne('PCK\AccountCodeSettings\AccountCodeSetting');
    }

    public function inspectionLists()
    {
        return $this->hasMany('PCK\Inspections\InspectionList');
    }

    public function requestForInspections()
    {
        return $this->hasMany('PCK\Inspections\RequestForInspection');
    }

    public function inspectionGroups()
    {
        return $this->hasMany('PCK\Inspections\InspectionGroup');
    }

    public function inspectionRoles()
    {
        return $this->hasMany('PCK\Inspections\InspectionRole');
    }

    public function projectReportUserPermissions()
    {
        return $this->hasMany(ProjectReportUserPermission::class, 'project_id');
    }

    public function projectReports()
    {
        return $this->hasMany(ProjectReport::class, 'project_id');
    }

    public function sectionalCompletionDates()
    {
        return $this->hasMany(ProjectSectionalCompletionDate::class, 'project_id');
    }

    public function eBidding()
    {
        return $this->hasOne('PCK\EBiddings\EBidding', 'project_id');
    }

    public function getModifiedCurrencyCodeAttribute($value)
    {
        return empty( $value ) ? $this->country->currency_code : $value;
    }

    public function getModifiedCurrencyNameAttribute($value)
    {
        return empty( $value ) ? $this->country->currency_name : $value;
    }

    /**
     * Returns a collection of all Notifications related to the project.
     *
     * @return mixed
     */
    public function getNotifications()
    {
        return Notification::where('url', 'LIKE', '/projects/' . $this->id . '/%')
            ->orWhere('url', 'LIKE', '/projects/' . $this->id . '?%')
            ->get();
    }

    public function getProjectUsers($includeTenderers = true)
    {
        $users = new Collection();

        foreach($this->contractGroupProjectUsers as $record)
        {
            $users->add($record->user);
        }

        if( ! $includeTenderers ) return $users;

        if( ! $this->inCallingTender() ) return $users;

        foreach($this->latestTender->selectedFinalContractors as $contractor)
        {
            $users = $users->merge($contractor->getActiveUsers());
        }

        return $users;
    }

    public function getParticipants(User $user)
    {
        $includeTenderers = ! $user->hasCompanyProjectRole($this, Role::CONTRACTOR);

        $participants = $this->getProjectUsers($includeTenderers);

        if( ! $includeTenderers )
        {
            $participants = $participants->merge($user->company->getActiveUsers());
        }

        return $participants;
    }

    public function getRequestForVariationsByUser(User $user)
    {
        $ids = \DB::table('request_for_variations AS r')
            ->join('request_for_variation_user_permission_groups AS g', 'r.request_for_variation_user_permission_group_id', '=', 'g.id')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('r.id')
            ->where('g.project_id', $this->id)
            ->where('p.user_id', '=', $user->id)
            ->distinct('r.id')
            ->lists('r.id');

        if( ! empty( $ids ) )
        {
            return RequestForVariation::whereIn('id', $ids)->orderBy('created_at', 'DESC')->get();
        }

        return [];
    }

    public function getAccumulativeApprovedRfvAmount()
    {
        $pdo = \DB::getPdo();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
            FROM request_for_variations r
            WHERE r.project_id = " . $this->id . "
            AND r.status = " . RequestForVariation::STATUS_APPROVED . "
            AND r.deleted_at IS NULL
            GROUP BY r.project_id");

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
    }

    public function getAccumulativeApprovedRfvAmountByUser(User $user, $rfvIds)
    {
        $pdo = \DB::getPdo();

        $query = \DB::table('request_for_variations AS r')
            ->join('request_for_variation_user_permission_groups AS g', 'g.id', '=', 'r.request_for_variation_user_permission_group_id')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('r.id')
            ->where('g.project_id', $this->id)
            ->where('p.user_id', '=', $user->id)
            ->where('p.can_view_cost_estimate', true)
            ->distinct('r.id');

        if($rfvIds)
        {
            $query->whereIn('r.id', $rfvIds);
        }

        $ids = $query->lists('r.id');

        if( ! empty( $ids ) )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
                FROM request_for_variations r
                WHERE r.id IN (" . implode(',', $ids) . ")
                AND r.status = " . RequestForVariation::STATUS_APPROVED . "
                AND r.deleted_at IS NULL
                GROUP BY r.project_id");

            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
        }

        return null;
    }

    public function getProposedRfvAmount()
    {
        $pdo = \DB::getPdo();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
            FROM request_for_variations r
            WHERE r.project_id = " . $this->id . "
            AND r.status IN (" . RequestForVariation::STATUS_PENDING_COST_ESTIMATE . ", " . RequestForVariation::STATUS_PENDING_VERIFICATION . ", " . RequestForVariation::STATUS_VERIFIED . ", " . RequestForVariation::STATUS_PENDING_APPROVAL . ")
            AND r.deleted_at IS NULL
            GROUP BY r.project_id");

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
    }

    public function getProposedRfvAmountByUser(User $user, $rfvIds)
    {
        $pdo = \DB::getPdo();

        $query = \DB::table('request_for_variations AS r')
            ->join('request_for_variation_user_permission_groups AS g', 'g.id', '=', 'r.request_for_variation_user_permission_group_id')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('r.id')
            ->where('g.project_id', $this->id)
            ->where('p.user_id', '=', $user->id)
            ->where('p.can_view_cost_estimate', true)
            ->distinct('r.id');

            if($rfvIds)
        {
            $query->whereIn('r.id', $rfvIds);
        }

            $ids = $query->lists('r.id');

        if( ! empty( $ids ) )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
                FROM request_for_variations r
                WHERE r.id IN (" . implode(',', $ids) . ")
                AND r.status IN (" . RequestForVariation::STATUS_PENDING_COST_ESTIMATE . ", " . RequestForVariation::STATUS_PENDING_VERIFICATION . ", " . RequestForVariation::STATUS_VERIFIED . ", " . RequestForVariation::STATUS_PENDING_APPROVAL . ")
                AND r.deleted_at IS NULL
                GROUP BY r.project_id");

            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
        }

        return null;
    }

    public function getOverallRfvAmount()
    {
        $pdo = \DB::getPdo();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
            FROM request_for_variations r
            WHERE r.project_id = " . $this->id . "
            AND r.deleted_at IS NULL
            GROUP BY r.project_id");

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
    }

    public function getOverallRfvAmountByUser(User $user, $rfvIds = null)
    {
        $pdo = \DB::getPdo();

        $query = \DB::table('request_for_variations AS r')
            ->join('request_for_variation_user_permission_groups AS g', 'g.id', '=', 'r.request_for_variation_user_permission_group_id')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('r.id')
            ->where('g.project_id', $this->id)
            ->where('p.user_id', '=', $user->id)
            ->where('p.can_view_cost_estimate', true)
            ->distinct('r.id');
        
        if($rfvIds)
        {
            $query->whereIn('r.id', $rfvIds);
        }

        $ids = $query->lists('r.id');

        if( ! empty( $ids ) )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
                FROM request_for_variations r
                WHERE r.id IN (" . implode(',', $ids) . ")
                AND r.deleted_at IS NULL
                GROUP BY r.project_id");

            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
        }

        return null;
    }

    public function getVerifiedRfvAmount()
    {
        $pdo = \DB::getPdo();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
            FROM request_for_variations r
            WHERE r.project_id = " . $this->id . "
            AND r.status = " . RequestForVariation::STATUS_VERIFIED . "
            AND r.deleted_at IS NULL
            GROUP BY r.project_id");

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
    }

    public function getVerifiedRfvAmountByUser(User $user)
    {
        $pdo = \DB::getPdo();

        $ids = \DB::table('request_for_variations AS r')
            ->join('request_for_variation_user_permission_groups AS g', 'g.id', '=', 'r.request_for_variation_user_permission_group_id')
            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('r.id')
            ->where('g.project_id', $this->id)
            ->where('p.user_id', '=', $user->id)
            ->where('p.can_view_cost_estimate', true)
            ->distinct('r.id')
            ->lists('r.id');

        if( ! empty( $ids ) )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
                FROM request_for_variations r
                WHERE r.id IN (" . implode(',', $ids) . ")
                AND r.status = " . RequestForVariation::STATUS_VERIFIED . "
                AND r.deleted_at IS NULL
                GROUP BY r.project_id");

            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_COLUMN, 0);
        }

        return null;
    }

    public function isCompanyAssignedAt(Company $company, $timestamp)
    {
        $relation = CompanyProject::where('project_id', '=', $this->id)
            ->where('company_id', '=', $company->id)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))
            ->first();

        // Project owner doesn't change, and is not logged in assign companies log.
        if( $relation ) return true;

        $contractorRelation = CompanyProject::where('project_id', '=', $this->id)
            ->where('company_id', '=', $company->id)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::CONTRACTOR))
            ->first();

        if( $contractorRelation ) return true;

        $logEntry = \DB::table('assign_companies_logs as l')
            ->join('assign_company_in_detail_logs as dl', 'dl.assign_company_log_id', '=', 'l.id')
            ->where('l.project_id', '=', $this->id)
            ->where('dl.company_id', '=', $company->id)
            ->where('l.created_at', '<=', $timestamp)
            ->orderBy('l.created_at', 'desc')
            ->first();

        if( $logEntry ) return true;

        $relevantTenderIds = Tender::where('project_id', '=', $this->id)
            ->where('created_at', '<=', $timestamp)
            ->lists('id');

        $rotCompany = \DB::table('tenders as t')
            ->join('tender_rot_information as rot', 'rot.tender_id', '=', 't.id')
            ->join('company_tender_rot_information as x', 'x.tender_rot_information_id', '=', 'rot.id')
            ->whereIn('t.id', $relevantTenderIds)
            ->where('x.company_id', '=', $company->id)
            ->first();

        if( $rotCompany ) return true;

        $lotCompany = \DB::table('tenders as t')
            ->join('tender_lot_information as lot', 'lot.tender_id', '=', 't.id')
            ->join('company_tender_lot_information as x', 'x.tender_lot_information_id', '=', 'lot.id')
            ->whereIn('t.id', $relevantTenderIds)
            ->where('x.company_id', '=', $company->id)
            ->first();

        if( $lotCompany ) return true;

        $callingTenderCompany = \DB::table('tenders as t')
            ->join('tender_calling_tender_information as ct', 'ct.tender_id', '=', 't.id')
            ->join('company_tender_calling_tender_information as x', 'x.tender_calling_tender_information_id', '=', 'ct.id')
            ->whereIn('t.id', $relevantTenderIds)
            ->where('x.company_id', '=', $company->id)
            ->first();

        if( $callingTenderCompany ) return true;

        return false;
    }

    public function getProjectTimeZoneTime($timestamp)
    {
        if( empty( $timestamp ) ) return null;

        $convertedTimestamp = DateTime::getTimeZoneTime($timestamp, $this->timezone);

        if( $format = DateTime::getTimeZoneFormat($timestamp) ) $convertedTimestamp = $convertedTimestamp->format($format);

        return $convertedTimestamp;
    }

    public function getAppTimeZoneTime($timestamp)
    {
        if( empty( $timestamp ) ) return null;

        $convertedTimestamp = DateTime::getTimeZoneTime($timestamp, getenv('TIMEZONE'), $this->timezone);

        if( $format = DateTime::getTimeZoneFormat($timestamp) ) $convertedTimestamp = $convertedTimestamp->format($format);

        return $convertedTimestamp;
    }

    public function getTimezoneAttribute()
    {
        return $this->state->timezone;
    }

    public function syncBuildSpaceContractorRates()
    {
        $tender = $this->latestTender;

        $file_name = SubmitTenderRate::ratesFileName;

        foreach ( $tender->selectedFinalContractors as $contractor )
        {
            // if no file then continue with other contractor
            if ( !$contractor->pivot->rates )
            {
                continue;
            }

            $file = SubmitTenderRate::getContractorRatesUploadPath($this, $tender, $contractor) . "/{$file_name}";

            \Queue::push('PCK\QueueJobs\SyncContractorRatesIntoBuildSpace', array(
                'project_id'              => $this->id,
                'contractor_reference_id' => $contractor->reference_id,
                'filePath'                => $file
            ), Tender::QUEUE_SYNC_TO_BS_TUBE_NAME);
        }
    }

    public function canSyncBuildSpaceContractorRates()
    {
        if( $this->latestTender->isFirstTender() ) return true;

        return $this->latestTender->open_tender_status != Tender::OPEN_TENDER_STATUS_NOT_YET_OPEN;
    }

    public function contractorClaimAccessGroups()
    {
        return array(
            Role::PROJECT_OWNER,
            Role::CONTRACTOR,
            Role::GROUP_CONTRACT,
            $this->contractGroupTenderDocumentPermission->contractGroup->group,
        );
    }

    public static function getStatusText($status)
    {
        switch($status)
        {
            case self::STATUS_TYPE_DESIGN:
                return self::STATUS_TYPE_DESIGN_TEXT;
            case self::STATUS_TYPE_POST_CONTRACT:
                return self::STATUS_TYPE_POST_CONTRACT_TEXT;
            case self::STATUS_TYPE_COMPLETED:
                return self::STATUS_TYPE_COMPLETED_TEXT;
            case self::STATUS_TYPE_RECOMMENDATION_OF_TENDERER:
                return self::STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT;
            case self::STATUS_TYPE_LIST_OF_TENDERER:
                return self::STATUS_TYPE_LIST_OF_TENDERER_TEXT;
            case self::STATUS_TYPE_CALLING_TENDER:
                return self::STATUS_TYPE_CALLING_TENDER_TEXT;
            case self::STATUS_TYPE_CLOSED_TENDER:
                return self::STATUS_TYPE_CLOSED_TENDER_TEXT;
            case self::STATUS_TYPE_E_BIDDING:
                return self::STATUS_TYPE_E_BIDDING_TEXT;
            default:
                throw new \Exception('Invalid status id');
        }
    }

    public function getTopManagementVerifiersWithProjectAccess()
    {
        return ModulePermission::getUserList(ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS)->reject(function($verifier) {
            if($verifier->getAssignedCompany($this))
            {
                return ( ! $verifier->assignedToProject($this) );
            }
        });
    }

    public function isEbiddingApproved()
    {
        $eBidding = $this->eBidding;

        if ($eBidding && $eBidding->status == EBidding::STATUS_APPROVED)
        {
            return true;
        }

        return false;
    }
}
