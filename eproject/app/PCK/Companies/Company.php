<?php namespace PCK\Companies;

use Illuminate\Database\Eloquent\Collection;
use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PCK\Tenders\SubmitTenderRate;
use PCK\Base\ModuleAttachmentTrait;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PCK\ObjectField\ObjectField;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\Base\Helpers;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\VendorProfile;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;
use PCK\Vendor\Vendor;
use PCK\Users\User;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\BuildingInformationModelling\BuildingInformationModellingLevel;
use PCK\CIDBCodes\CIDBCode;
use PCK\CIDBGrades\CIDBGrade;
use PCK\VendorRegistration\CompanyTemporaryDetail;
use PCK\CompanyProject\CompanyProject;
use PCK\CompanyTenderCallingTenderInformation\CompanyTenderCallingTenderInformation;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;

/**
* reference_no is what the user keys in.
* reference_id is the reference to BuildSpace.
*/
class Company extends Model {

    protected $primaryKey = 'id';

    const REFERENCE_ID_LENGTH = 16;

    const STATUS_DRAFT = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_EXPIRED = 4;
    const STATUS_DEACTIVATED = 8;

    const VENDOR_STATUS_ACTIVE = 1;
    const VENDOR_STATUS_WATCH_LIST = 2;
    const VENDOR_STATUS_NOMINATED_WATCH_LIST = 4;
    const VENDOR_STATUS_DEACTIVATED = 8;
    const VENDOR_STATUS_EXPIRED = 16;

    const COMPANY_STATUS_BUMIPUTERA     = 1;
    const COMPANY_STATUS_NON_BUMIPUTERA = 2;
    const COMPANY_STATUS_FOREIGN        = 4;
    const COMPANY_STATUS_INTERCO        = 8;

    const CIDB_GRADE_G1 = 1;
    const CIDB_GRADE_G2 = 2;
    const CIDB_GRADE_G3 = 4;
    const CIDB_GRADE_G4 = 8;
    const CIDB_GRADE_G5 = 16;
    const CIDB_GRADE_G6 = 32;
    const CIDB_GRADE_G7 = 64;
    const CIDB_GRADE_NA = 128;

    use ModuleAttachmentTrait, BsCompanyTrait, CompanyRoleTrait, UsersTrait;

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $model)
        {
            $model->reference_id = str_random(self::REFERENCE_ID_LENGTH);
        });

        self::saving(function(self $model)
        {
            $model->name = mb_strtoupper($model->name);
            $model->reference_no = mb_strtoupper($model->reference_no);

            if( strlen($model->tax_registration_no) > 0 )
            {
                $model->tax_registration_no = mb_strtoupper($model->tax_registration_no);
                $model->tax_registration_id = self::generateRawRegistrationIdentifier($model->tax_registration_no);
            }
            else
            {
                $model->tax_registration_id = null;
            }
        });

        self::saved(function(self $model)
        {
            $model->updateBSCompany();
        });

        self::deleting(function(self $model)
        {
            \Log::info("Attempting to delete company [{$model->id}:{$model->username}].");

            $model = \PCK\Companies\Company::find($model->id);

            if( $model->users->count() > 0 )
            {
                throw new \Exception('There are users registered under this company. This company can therefore not be deleted.');
            }

            if( $model->projects->count() )
            {
                throw new \Exception('This company has been assigned to projects and therefore cannot be deleted.');
            }

            // delete current deleting company's information from BuildSpace
            $model->deleteBSCompany();
        });

        self::deleted(function(self $model)
        {
            $actingUser = \Confide::user();

            if($actingUser)
            {
                \Log::info("Deleted company [{$model->id}:{$model->username}]. Action by [{$actingUser->id}:{$actingUser->username}].");
            }
            else
            {
                \Log::info("Deleted company [{$model->id}:{$model->username}]. Action by System.");
            }
        });
    }

    public function hasAlternateProposal(Tender $tender, $bsTenderAlternativeId=null)
    {
        $companyTendersRecord = \DB::table('company_tender')
            ->select('id', 'contractor_adjustment_percentage', 'contractor_adjustment_amount', 'completion_period')
            ->where('tender_id', $tender->id)
            ->where('company_id', $this->id)
            ->first();
        
        if($companyTendersRecord)
        {
            if(!is_null($bsTenderAlternativeId))
            {
                $companyTenderTenderAlternativeRecord = \DB::table('company_tender_tender_alternatives')
                ->select('contractor_adjustment_percentage', 'contractor_adjustment_amount', 'completion_period')
                ->where('company_tender_id', $companyTendersRecord->id)
                ->where('tender_alternative_id', $bsTenderAlternativeId)
                ->first();

                if($companyTenderTenderAlternativeRecord && $companyTenderTenderAlternativeRecord->contractor_adjustment_percentage != 0.0) return true;
                if($companyTenderTenderAlternativeRecord && $companyTenderTenderAlternativeRecord->contractor_adjustment_amount != 0.0) return true;
                if($companyTenderTenderAlternativeRecord && $companyTenderTenderAlternativeRecord->completion_period != 0.0) return true;
            }
            else
            {
                if($companyTendersRecord->contractor_adjustment_percentage != 0.0) return true;
                if($companyTendersRecord->contractor_adjustment_amount != 0.0) return true;
                if($companyTendersRecord->completion_period != 0.0) return true;
            }
        }
        
        return false;
    }

    public static function getCompanyStatusDescriptions($identifier = null)
    {
        $companyStatuses = [
            self::COMPANY_STATUS_BUMIPUTERA     => trans('vendorManagement.bumiputera'),
            self::COMPANY_STATUS_NON_BUMIPUTERA => trans('vendorManagement.nonBumiputera'),
            self::COMPANY_STATUS_FOREIGN        => trans('vendorManagement.foreign'),
            self::COMPANY_STATUS_INTERCO        => trans('vendorManagement.interco'),
        ];

        return is_null($identifier) ? $companyStatuses : $companyStatuses[$identifier];
    }

    public static function getCIDBGradeDescriptions($identifier = null)
    {
        $cidbGrades = [
            self::CIDB_GRADE_G1 => trans('companies.cidbGrade1'),
            self::CIDB_GRADE_G2 => trans('companies.cidbGrade2'), 
            self::CIDB_GRADE_G3 => trans('companies.cidbGrade3'), 
            self::CIDB_GRADE_G4 => trans('companies.cidbGrade4'), 
            self::CIDB_GRADE_G5 => trans('companies.cidbGrade5'),
            self::CIDB_GRADE_G6 => trans('companies.cidbGrade6'),
            self::CIDB_GRADE_G7 => trans('companies.cidbGrade7'),
            self::CIDB_GRADE_NA => trans('general.notApplicableFull'),
        ];

        return is_null($identifier) ? $cidbGrades : $cidbGrades[$identifier];
    }

    public function isContractor()
    {
        return $this->contractGroupCategory->vendor_type == ContractGroupCategory::VENDOR_TYPE_CONTRACTOR;
    }

    public function isConsultant()
    {
        return $this->contractGroupCategory->vendor_type == ContractGroupCategory::VENDOR_TYPE_CONSULTANT;
    }

    public function bimLevel()
    {
        return $this->belongsTo(BuildingInformationModellingLevel::class, 'bim_level_id');
    }

    public function vendorRegistration()
    {
        return $this->hasOne('PCK\VendorRegistration\VendorRegistration', 'company_id')->orderBy('id', 'desc');
    }

    public function vendorRegistrations()
    {
        return $this->hasMany('PCK\VendorRegistration\VendorRegistration', 'company_id')->orderBy('revision', 'desc');
    }

    public function finalVendorRegistration()
    {
        return $this->hasOne('PCK\VendorRegistration\VendorRegistration', 'company_id')->where('status', '=', VendorRegistration::STATUS_COMPLETED)->orderBy('revision', 'desc');
    }

    public function vendors()
    {
        return $this->hasMany('PCK\Vendor\Vendor', 'company_id');
    }

    public function contractGroupCategory()
    {
        return $this->belongsTo('PCK\ContractGroupCategory\ContractGroupCategory');
    }

    public function projects()
    {
        return $this->belongsToMany('PCK\Projects\Project')->withTimestamps();
    }

    public function businessEntityType()
    {
        return $this->belongsTo('PCK\BusinessEntityType\BusinessEntityType', 'business_entity_type_id');
    }

    public function ongoingProjects()
    {
        return $this->belongsToMany('PCK\Projects\Project')
            ->where('status_id', '<>', Project::STATUS_TYPE_COMPLETED)
            ->orderBy('id', 'DESC')
            ->distinct()
            ->withTimestamps();
    }

    public function completedProjects()
    {
        return $this->belongsToMany('PCK\Projects\Project')
            ->where('status_id', '=', Project::STATUS_TYPE_COMPLETED)
            ->orderBy('id', 'DESC')
            ->distinct()
            ->withTimestamps();
    }

    public function tenders()
    {
        return $this->belongsToMany('PCK\Tenders\Tender', 'company_tender', 'company_id', 'tender_id')
            ->with('project')
            ->withPivot('id', 'rates', 'tender_amount', 'completion_period', 'submitted', 'submitted_at', 'selected_contractor', 'supply_of_material_amount', 'other_bill_type_amount_except_prime_cost_provisional', 'contractor_adjustment_percentage', 'contractor_adjustment_amount', 'original_tender_amount', 'discounted_percentage', 'discounted_amount')
            ->orderBy('company_tender.id', 'DESC')
            ->withTimestamps();
    }

    public function latestParticipatedTenders()
    {
        return $this->belongsToMany('PCK\Tenders\Tender')
            ->with('project')
            ->withPivot('id', 'rates', 'tender_amount', 'completion_period', 'submitted', 'submitted_at', 'selected_contractor', 'supply_of_material_amount', 'other_bill_type_amount_except_prime_cost_provisional', 'contractor_adjustment_percentage', 'contractor_adjustment_amount', 'original_tender_amount', 'discounted_percentage', 'discounted_amount')
            ->where('retender_status', '=', false)
            ->orderBy('id', 'DESC')
            ->distinct()
            ->withTimestamps();
    }

    public function getParticipatedLatestTenders()
    {
        return Tender::select('tenders.*')
            ->join('company_tender', 'company_tender.tender_id', '=', 'tenders.id')
            ->join('projects', 'projects.id', '=', 'tenders.project_id')
            ->join(\DB::raw(
                "(
                    select t2.project_id, max(t2.count) as max_count
                    from tenders t2
                    group by t2.project_id
                ) latest_project_tenders"
            ), function($join){
                $join->on('latest_project_tenders.project_id', '=', 'tenders.project_id');
                $join->on('latest_project_tenders.max_count', '=', 'tenders.count');
            })
            ->where('company_tender.company_id', '=', $this->id)
            ->whereNull('projects.deleted_at')
            ->get();
    }

    public function getWithdrawnTenders()
    {
        return Tender::join('tender_calling_tender_information', 'tender_calling_tender_information.tender_id', '=', 'tenders.id')
            ->join('company_tender_calling_tender_information', 'company_tender_calling_tender_information.tender_calling_tender_information_id', '=', 'tender_calling_tender_information.id')
            ->where('company_tender_calling_tender_information.company_id', '=', $this->id)
            ->where('company_tender_calling_tender_information.status', '=', ContractorCommitmentStatus::TENDER_WITHDRAW)
            ->get();
    }

    public function tenderROTInformation()
    {
        return $this->belongsToMany('PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation')->withTimestamps();
    }

    public function tenderLOTInformation()
    {
        return $this->belongsToMany('PCK\TenderListOfTendererInformation\TenderListOfTendererInformation')->withTimestamps();
    }

    public function tenderCallingTenderInformation()
    {
        return $this->belongsToMany('PCK\TenderCallingTenderInformation\TenderCallingTenderInformation')->withTimestamps();
    }

    public function contractor()
    {
        return $this->hasOne('PCK\Contractors\Contractor');
    }

    public function country()
    {
        return $this->belongsTo('PCK\Countries\Country');
    }

    public function state()
    {
        return $this->belongsTo('PCK\States\State');
    }

    public function products()
    {
        return $this->hasMany('PCK\Products\Product');
    }

    // allow the Pivot table to again access to add attachments
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if( $parent instanceof Tender )
        {
            return new SubmitTenderRate($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    /**
     * Generates a string with all non-valid characters stripped
     * and converted to the desired format.
     *
     * @param $string
     *
     * @return string
     */
    public static function generateRawRegistrationIdentifier($string)
    {
        // Accepts only characters from the Latin alphabet and the Hindu-Arabic numeral system.
        $patterns     = array( '/[^a-zA-Z0-9]/' );
        $replacements = array( '' );
        $string       = preg_replace($patterns, $replacements, $string);

        $string = strtolower($string);

        return substr($string, 0, 20);
    }

    public function usersCanBeTransferred()
    {
        $allowedContractGroupIds = array(
            ContractGroup::getIdByGroup(Role::PROJECT_OWNER),
            ContractGroup::getIdByGroup( Role::PROJECT_MANAGER),
            ContractGroup::getIdByGroup( Role::GROUP_CONTRACT),
        );

        return $this->contractGroupCategory->includesContractGroups($allowedContractGroupIds);
    }

    public function getNameInProject(Project $project)
    {
        $companyName = $this->name;

        if( $this->hasProjectRole($project, Role::PROJECT_OWNER) ) $companyName = $project->subsidiary->fullName;

        return $companyName;
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

    public function vendorCategories()
    {
        return $this->belongsToMany('PCK\VendorCategory\VendorCategory');
    }

    public function isVendorCategoryRegistered($vendorCategoryId)
    {
        return $this->vendorCategories()
                    ->where("vendor_category_id", $vendorCategoryId)
                    ->get();
    }

    public function cidbCodes()
    {
        return $this->belongsToMany('PCK\CIDBCodes\CIDBCode', 'company_cidb_code', 'company_id', 'cidb_code_id');
    }

    public function vendorProfile()
    {
        return $this->hasOne('PCK\VendorRegistration\VendorProfile', 'company_id');
    }

    public function getVendorRegistrationCompanyDetails()
    {
        $companyDetails = [];

        $vendorDetailAttachmentSetting = VendorDetailAttachmentSetting::first();

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyName');
       
        
        array_push($companyDetails, [
            'label'              => trans('companies.name'),
            'values'             => [$this->name],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyName']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->name_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyAddress');

        array_push($companyDetails, [
            'label'              => trans('companies.address'),
            'values'             => [$this->address],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyAddress']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->address_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsUserType');

        array_push($companyDetails, [
            'label'              => trans('companies.contractGroupCategory'),
            'values'             => [$this->contractGroupCategory->name],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsUserType']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->contract_group_category_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsVendorCategory');
        $values      = [];
    
        foreach($this->vendorCategories as $vendorCategory)
        {
            array_push($values, $vendorCategory->name);
        }

        array_push($companyDetails, [
            'label'              => trans('vendorManagement.vendorCategory'),
            'values'             => $values,
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsVendorCategory']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->vendor_category_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyMainContact');

        array_push($companyDetails, [
            'label'              => trans('companies.mainContact'),
            'values'             => [$this->main_contact],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyMainContact']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->main_contact_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyRocNumber');

        array_push($companyDetails, [
            'label'              => trans('companies.referenceNumber'),
            'values'             => [$this->reference_no],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyRocNumber']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->reference_number_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber');

        array_push($companyDetails, [
            'label'              => trans('companies.taxRegistrationNumber'),
            'values'             => [$this->tax_registration_no],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->tax_registration_number_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyEmail');

        array_push($companyDetails, [
            'label'              => trans('companies.email'),
            'values'             => [$this->email],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyEmail']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->email_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyTelephone');

        array_push($companyDetails, [
            'label'              => trans('companies.telephone'),
            'values'             => [$this->telephone_number],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyTelephone']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->telephone_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyFax');

        array_push($companyDetails, [
            'label'              => trans('companies.fax'),
            'values'             => [$this->fax_number],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyFax']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->fax_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyCountry');

        array_push($companyDetails, [
            'label'              => trans('companies.country'),
            'values'             => [$this->country->country],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyCountry']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->country_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyState');

        array_push($companyDetails, [
            'label'              => trans('companies.state'),
            'values'             => [$this->state->name],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyState']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->state_attachments,
        ]);

        if($this->isContractor())
        {
            $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCIDBGrade');
            $cidbGrade   = $this->vendorRegistration->isFirst() ? $this->cidb_grade : CompanyTemporaryDetail::findRecord($this->vendorRegistration)->cidb_grade;
            $cidbCodes   = $this->cidbCodes;
            $cidbCodeArray = [];

            foreach($cidbCodes as $cidbCode)
            {
                $cidbCodeArray[] = $cidbCode->code . ' (' .$cidbCode->description . ')';
            }

            array_push($companyDetails, [
                'label'              => trans('companies.cidbGrade'),
                'values'             => CIDBGrade::find($cidbGrade) ? [CIDBGrade::find($cidbGrade)->grade] : [],
                'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCIDBGrade']),
                'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
                'enable_attachments' => $vendorDetailAttachmentSetting->cidb_grade_attachments,
            ]);

            array_push($companyDetails, [
                'label'              => trans('companies.cidbCode'),
                'values'             => $cidbCodeArray,
                'route_attachments'  => NULL,
                'attachments_count'  => NULL,
                'enable_attachments' => NULL,
            ]);
        }

        if($this->isConsultant())
        {
            $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsBIMLevel');
            $bimLevel    = $this->vendorRegistration->isFirst() ? $this->bimLevel->name : CompanyTemporaryDetail::findRecord($this->vendorRegistration)->bimLevel->name;

            array_push($companyDetails, [
                'label'              => trans('companies.bimLevel'),
                'values'             => [$bimLevel],
                'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsBIMLevel']),
                'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
                'enable_attachments' => $vendorDetailAttachmentSetting->bim_level_attachments,
            ]);
        }

        $objectField   = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyStatus');
        $companyStatus = $this->vendorRegistration->isFirst() ? $this->company_status : CompanyTemporaryDetail::findRecord($this->vendorRegistration)->company_status;
        $values        = $companyStatus ? self::getCompanyStatusDescriptions($companyStatus) : '-';

        array_push($companyDetails, [
            'label'              => trans('vendorManagement.companyStatus'),
            'values'             => [$values],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyStatus']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->company_status_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyBumiputeraEquity');

        array_push($companyDetails, [
            'label'              => trans('vendorManagement.bumiputeraEquity'),
            'values'             => [$this->bumiputera_equity],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyBumiputeraEquity']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->bumiputera_equity_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity');

        array_push($companyDetails, [
            'label'              => trans('vendorManagement.nonBumiputeraEquity'),
            'values'             => [$this->non_bumiputera_equity],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->non_bumiputera_equity_attachments,
        ]);

        $objectField = ObjectField::findRecord($this, 'vendorRegistrationDetailsCompanyForeignerEquity');

        array_push($companyDetails, [
            'label'              => trans('vendorManagement.foreignerEquity'),
            'values'             => [$this->foreigner_equity],
            'route_attachments'  => route('vendor.registration.details.attachements.get', [$this->id, 'vendorRegistrationDetailsCompanyForeignerEquity']),
            'attachments_count'  => is_null($objectField) ? 0 : $objectField->attachments->count(),
            'enable_attachments' => $vendorDetailAttachmentSetting->foreigner_equity_attachments,
        ]);

        return $companyDetails;
    }

    public function setTemporaryLoginAccountValidity()
    {
        $this->purge_date = VendorRegistrationAndPrequalificationModuleParameter::getTemporaryLoginAccountValidityPeriod();

        $this->save();

        foreach($this->getAllUsers() as $user)
        {
            $user->purge_date = $this->purge_date;
            $user->save();
        }
    }

    public function removeTemporaryLoginAccountValidity()
    {
        $this->purge_date = null;

        $this->save();

        foreach($this->getAllUsers() as $user)
        {
            $user->purge_date = null;
            $user->save();
        }
    }

    public function permanentize()
    {
        $this->confirmed = true;

        $this->save();
    }

    public function calculateDeactivationDate()
    {
        if(!$this->expiry_date) return null;

        switch(VendorProfileModuleParameter::getValue('grace_period_of_expired_vendor_before_moving_to_dvl_unit'))
        {
            case VendorProfileModuleParameter::DAY:
                $validityPeriodUnit = 'days';
                break;
            case VendorProfileModuleParameter::WEEK:
                $validityPeriodUnit = 'weeks';
                break;
            case VendorProfileModuleParameter::MONTH:
                $validityPeriodUnit = 'months';
                break;
            default:
                throw new \Exception("Invalid time unit");
        }

        return Helpers::getTimeFrom(Carbon::parse($this->expiry_date), VendorProfileModuleParameter::getValue('grace_period_of_expired_vendor_before_moving_to_dvl_value'), $validityPeriodUnit);
    }

    public function setExpiryDate()
    {
        switch(VendorProfileModuleParameter::getValue('validity_period_of_active_vendor_in_avl_unit'))
        {
            case VendorProfileModuleParameter::DAY:
                $validityPeriodUnit = 'days';
                break;
            case VendorProfileModuleParameter::WEEK:
                $validityPeriodUnit = 'weeks';
                break;
            case VendorProfileModuleParameter::MONTH:
                $validityPeriodUnit = 'months';
                break;
            default:
                throw new \Exception("Invalid time unit");
        }

        $now = Carbon::now();

        // If in renewal period and before expiry_date
        if(!is_null($this->getRenewalWindowStartDate()) && $this->getRenewalWindowStartDate()->isPast() && Carbon::parse($this->expiry_date)->isFuture())
        {
            $this->expiry_date = Helpers::getTimeFrom(Carbon::parse($this->expiry_date), VendorProfileModuleParameter::getValue('validity_period_of_active_vendor_in_avl_value'), $validityPeriodUnit);
        }
        else
        {
            $this->activation_date = $now;

            $this->expiry_date = Helpers::getTimeFromNow(VendorProfileModuleParameter::getValue('validity_period_of_active_vendor_in_avl_value'), $validityPeriodUnit);
        }

        $this->save();
    }

    public function generateVendorProfile()
    {
        VendorProfile::createIfNotExists($this);
    }

    // any date after $mustRenewDate is deemed as renewal period
    public function inRenewalPeriod()
    {
        $mustRenewDate = $this->getRenewalWindowStartDate();

        if(is_null($mustRenewDate)) return false;

        return $mustRenewDate->isPast();
    }

    public function getRenewalWindowStartDate()
    {
        if(is_null($this->expiry_date)) return null;

        $renewalPeriodBeforeExpiryInDays = VendorProfileModuleParameter::getValue('renewal_period_before_expiry_in_days');

        return Helpers::getTimeBefore(Carbon::parse($this->expiry_date), $renewalPeriodBeforeExpiryInDays, 'days');
    }

    public function getVendorCode()
    {
        return self::getVendorCodeFromId($this->id);
    }

    public static function getVendorCodeFromId($companyId)
    {
        $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        return $vendorCodePrefix . str_pad($companyId, $vendorCodePadLength, 0, STR_PAD_LEFT);
    }

    public function getArchivedStorage($path=null)
    {
        $baseLocation = storage_path().DIRECTORY_SEPARATOR."vendor-archived";

        if(!\File::exists($baseLocation) || !\File::isDirectory($baseLocation))
        {
            \File::makeDirectory($baseLocation, 0777, true, true);
        }

        $baseLocation = $baseLocation.DIRECTORY_SEPARATOR.$this->id;

        if(!\File::exists($baseLocation) || !\File::isDirectory($baseLocation))
        {
            \File::makeDirectory($baseLocation, 0777, true, true);
        }

        $location = ($path) ? $baseLocation.DIRECTORY_SEPARATOR.$path : $baseLocation;
        
        if(!\File::exists($location) || !\File::isDirectory($location))
        {
            \File::makeDirectory($location, 0777, true, true);
        }

        $iterator = new \DirectoryIterator($location);

        $directories = [];
        $files = [];

        $lvl = substr_count($location,"/");

        foreach ($iterator as $item)
        {
            if ($item->isDot())
            {
                continue;
            }
            
            $pathName = str_replace($baseLocation, "", $item->getPath());
            $pathName = ltrim($pathName, '/');

            if($item->isDir())
            {
                $directories[] = [
                    'id' => $lvl.'-'.$item->key(),
                    'basename' => $item->getBasename(),
                    'dirname' => $pathName,
                    'path' => $pathName,
                    'type' => 'dir',
                    'extension' => 'Folder'
                ];
            }
            else
            {
                $files[] = [
                    'id' => $lvl.'-'.$item->key(),
                    'basename' => $item->getBasename(),
                    'dirname' => $pathName,
                    'filename' => $item->getFilename(),
                    'path' => $pathName,
                    'type' => 'file',
                    'extension' => $item->getExtension(),
                    'size' => $item->getSize()
                ];
            }
        }

        return [$directories, $files];
    }

    public function hasExternalAppAttachments()
    {
        $externalAppAttachmentPath = getenv('EXTERNAL_APP_ATTACHMENT_PATH') ? getenv('EXTERNAL_APP_ATTACHMENT_PATH') : null;

        $count = Company::select('companies.id AS company_id', 'a.id')
        ->join('external_app_attachments AS a', 'a.reference_id', '=', 'companies.third_party_vendor_id')
        ->where('companies.id', '=', $this->id)
        ->count();

        return ($count && $externalAppAttachmentPath && \File::isDirectory($externalAppAttachmentPath));
    }

    public function hasExternalAppCompanyAttachments()
    {
        $externalAppCompanyAttachmentPath = getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH') ? getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH') : null;

        $count = Company::select('companies.id AS company_id', 'a.id')
        ->join('external_app_company_attachments AS a', 'a.reference_id', '=', 'companies.third_party_vendor_id')
        ->where('companies.id', '=', $this->id)
        ->count();

        return ($count && $externalAppCompanyAttachmentPath && \File::isDirectory($externalAppCompanyAttachmentPath));
    }

    public function getExternalAppAttachments()
    {
        $baseLocation = getenv('EXTERNAL_APP_ATTACHMENT_PATH') ? getenv('EXTERNAL_APP_ATTACHMENT_PATH') : null;

        $files = [];

        if(!$baseLocation or !\File::isDirectory($baseLocation))
        {
            return $files;
        }

        $attachments = Company::select('companies.id AS company_id', 'a.id', 'a.reference_id', 'a.remarks', 'a.filename', 'a.file_path')
        ->join('external_app_attachments AS a', 'a.reference_id', '=', 'companies.third_party_vendor_id')
        ->where('companies.id', '=', $this->id)
        ->orderBy('a.created_at', 'desc')
        ->get();

        foreach($attachments as $attachment)
        {
            $filePath = $baseLocation.DIRECTORY_SEPARATOR.trim($attachment->file_path);
            if(is_file($filePath))
            {
                $pathParts = pathinfo($filePath);

                $files[] = [
                    'id' => 'EXT_APP_ATTCH-'.$attachment->id,
                    'basename' => $pathParts['basename'],
                    'dirname' => $pathParts['dirname'],
                    'filename' => $attachment->filename,
                    'remarks' => $attachment->remarks,
                    'path' => $filePath,
                    'type' => 'file',
                    'extension' => $pathParts['extension'],
                    'size' => filesize($filePath)
                ];
            }
        }

        return $files;
    }

    public function getExternalAppCompanyAttachments()
    {
        $baseLocation = getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH') ? getenv('EXTERNAL_APP_COMPANY_ATTACHMENT_PATH') : null;

        $files = [];

        if(!$baseLocation or !\File::isDirectory($baseLocation))
        {
            return $files;
        }

        $attachments = Company::select('companies.id AS company_id', 'a.id', 'a.reference_id', 'a.document_type', 'a.filename', 'a.file_path' ,'a.created_at')
        ->join('external_app_company_attachments AS a', 'a.reference_id', '=', 'companies.third_party_vendor_id')
        ->where('companies.id', '=', $this->id)
        ->orderBy('a.created_at', 'desc')
        ->get();

        foreach($attachments as $attachment)
        {
            $filePath = $baseLocation.DIRECTORY_SEPARATOR.trim($attachment->file_path);
            if(is_file($filePath))
            {
                $pathParts = pathinfo($filePath);

                $files[] = [
                    'id' => 'EXT_APP_COMP_ATTCH-'.$attachment->id,
                    'basename' => $pathParts['basename'],
                    'dirname' => $pathParts['dirname'],
                    'filename' => $attachment->filename,
                    'document_type' => $attachment->document_type,
                    'path' => $filePath,
                    'type' => 'file',
                    'extension' => $pathParts['extension'],
                    'size' => filesize($filePath)
                ];
            }
        }

        return $files;
    }

    public function getStatus()
    {
        $activationDate = ($this->activation_date) ? new Carbon($this->activation_date) : null;
        $expiryDate = ($this->expiry_date) ? new Carbon($this->expiry_date) : null;
        $deactivatedAt = ($this->deactivated_at) ? new Carbon($this->deactivated_at) : null;

        if($deactivatedAt)
        {
            return self::STATUS_DEACTIVATED;
        }
        elseif(!empty($expiryDate) and $expiryDate->lte(Carbon::now()) && empty($deactivatedAt))
        {
            return self::STATUS_EXPIRED;
        }
        elseif($activationDate)
        {
            return self::STATUS_ACTIVE;
        }

        return self::STATUS_DRAFT;
    }

    public function getStatusText()
    {
        switch($this->getStatus())
        {
            case self::STATUS_DEACTIVATED:
                return trans('general.deactivated');
            case self::STATUS_EXPIRED:
                return trans('general.expired');
            case self::STATUS_ACTIVE:
                return trans('general.active');
            default:
                return trans('forms.draft');
        }
    }

    public static function getStatusDescriptions($identifier = null)
    {
        $descriptions = [
            self::STATUS_DRAFT       => trans('forms.draft'),
            self::STATUS_ACTIVE      => trans('general.active'),
            self::STATUS_EXPIRED     => trans('general.expired'),
            self::STATUS_DEACTIVATED => trans('general.deactivated'),
        ];

        return is_null($identifier) ? $descriptions : $descriptions[$identifier];
    }

    public function getVendorStatusTextAttribute()
    {
        switch($this->vendor_status)
        {
            case self::VENDOR_STATUS_ACTIVE:
                return trans('vendorManagement.activeVendorList');
            case self::VENDOR_STATUS_WATCH_LIST:
                return trans('vendorManagement.watchList');
            case self::VENDOR_STATUS_NOMINATED_WATCH_LIST:
                return trans('vendorManagement.nomineesForWatchList');
            case self::VENDOR_STATUS_DEACTIVATED:
                return trans('vendorManagement.deactivatedVendorList');
            case self::VENDOR_STATUS_EXPIRED:
                return trans('vendorManagement.expired');
            default:
                return "";
        }
    }

    public function getLatestPerformanceEvaluationAverageDeliberatedScore()
    {
        $totalDeliberatedScore = 0;

        if(empty($this->vendors->count()))
        {
            return $totalDeliberatedScore;
        }

        $averageCount = 0;

        foreach($this->vendors as $vendor)
        {
            $latestCycleScore = $vendor->getLatestPerformanceEvaluationCycleScore();

            if($latestCycleScore)
            {
                $totalDeliberatedScore += $latestCycleScore->deliberated_score;
                $averageCount++;
            }
        }

        return ($averageCount) ? ($totalDeliberatedScore / $averageCount) : 0;
    }

    public function hasLatestPerformanceEvaluationScore()
    {
        if(empty($this->vendors->count()))
        {
            return false;
        }

        foreach($this->vendors as $vendor)
        {
            $latestCycleScore = $vendor->getLatestPerformanceEvaluationCycleScore();
            
            if($latestCycleScore)
            {
                return true;
            }
        }

        return false;
    }

    public function updateVendorStatus()
    {
        return \DB::statement('UPDATE companies SET vendor_status = ? WHERE id = ?', [$this->computeVendorStatus(), $this->id]);
    }

    public function computeVendorStatus()
    {
        $vendorTypes = $this->vendors->lists('type');

        if(in_array(Vendor::TYPE_WATCH_LIST, $vendorTypes))
        {
            return self::VENDOR_STATUS_WATCH_LIST;
        }

        if(in_array(Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, $vendorTypes) || in_array(Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST, $vendorTypes))
        {
            return self::VENDOR_STATUS_NOMINATED_WATCH_LIST;
        }

        if($this->deactivated_at !== null)
        {
            return self::VENDOR_STATUS_DEACTIVATED;
        }

        if($this->expiry_date && Carbon::parse($this->expiry_date)->isPast())
        {
            return self::VENDOR_STATUS_EXPIRED;
        }

        if($this->activation_date)
        {
            return self::VENDOR_STATUS_ACTIVE;
        }

        return null;
    }

    public function flushRelatedVendorRegistrationData()
    {
        \DB::statement("DELETE FROM company_vendor_category WHERE company_id = ?", [$this->id]);

        foreach($this->vendorRegistrations as $vendorRegistration)
        {
            $vendorRegistration->flushRelatedVendorRegistrationData();
        }

        Vendor::flushRecords($this);
    }

    public function isProjectUser(Project $project, User $user)
    {
        $contractGroup = $this->getContractGroup($project);

        $contractGroupId = $contractGroup ? $contractGroup->id : null;

        return ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('user_id', '=', $user->id)
            ->where('contract_group_id', '=', $contractGroupId)
            ->exists();
    }

    public function getProjectUsers(Project $project)
    {
        $contractGroup = $this->getContractGroup($project);

        $contractGroupId = $contractGroup ? $contractGroup->id : null;

        $userIds = ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractGroupId)
            ->lists('user_id');

        $userIds = array_intersect($userIds, $this->getActiveUsers()->lists('id'));

        return User::whereIn('id', $userIds)->get();
    }

    public function isProjectEditor(Project $project, User $user)
    {
        $contractGroup = $this->getContractGroup($project);

        $contractGroupId = $contractGroup ? $contractGroup->id : null;

        return ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->where('user_id', '=', $user->id)
            ->where('contract_group_id', '=', $contractGroupId)
            ->exists();
    }

    public function getProjectEditors(Project $project)
    {
        $contractGroup = $this->getContractGroup($project);

        $contractGroupId = $contractGroup ? $contractGroup->id : null;

        $userIds = ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->where('contract_group_id', '=', $contractGroupId)
            ->lists('user_id');

        $userIds = array_intersect($userIds, $this->getActiveUsers()->lists('id'));

        return User::whereIn('id', $userIds)->get();
    }

    public function syncVendorWorkCategorySetups()
    {
        $vendorWorkCategories = [];

        foreach($this->vendors as $vendorRecord) $vendorWorkCategories[] = $vendorRecord->vendor_work_category_id;

        VendorPerformanceEvaluationSetup::whereHas('vendorPerformanceEvaluation', function($query){
            $query->whereIn('status_id', [VendorPerformanceEvaluation::STATUS_DRAFT, VendorPerformanceEvaluation::STATUS_IN_PROGRESS]);
        })
        ->where('company_id', '=', $this->id)
        ->whereNotIn('vendor_work_category_id', $vendorWorkCategories)
        ->delete();

        // Assigned projects.
        $projectIds = CompanyProject::where('company_id', '=', $this->id)->lists('project_id');

        $evaluations = VendorPerformanceEvaluation::whereIn('project_id', $projectIds)
            ->whereIn('status_id', [VendorPerformanceEvaluation::STATUS_DRAFT, VendorPerformanceEvaluation::STATUS_IN_PROGRESS])
            ->get();

        // Create new setups.
        foreach($evaluations as $evaluation)
        {
            foreach($vendorWorkCategories as $vendorWorkCategoryId)
            {
                VendorPerformanceEvaluationSetup::firstOrCreate([
                    'vendor_performance_evaluation_id' => $evaluation->id,
                    'company_id' => $this->id,
                    'vendor_work_category_id' => $vendorWorkCategoryId,
                ]);
            }
        }
    }
}