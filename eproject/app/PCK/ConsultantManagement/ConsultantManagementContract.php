<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\StringOperations;
use PCK\ConsultantManagement\ConsultantManagementUserRole;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementAttachmentSetting;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\Users\User;
use PCK\Subsidiaries\Subsidiary;
use PCK\Countries\Country;
use PCK\States\State;
use PCK\Companies\Company;
use PCK\Helpers\DateTime;

class ConsultantManagementContract extends Model
{
    protected $table = 'consultant_management_contracts';

    protected $fillable = ['subsidiary_id', 'reference_no', 'title', 'description', 'address', 'country_id', 'state_id', 'modified_currency_code', 'modified_currency_name'];

    const ROLE_RECOMMENDATION_OF_CONSULTANT = 1;
    const ROLE_LIST_OF_CONSULTANT = 2;
    const ROLE_CONSULTANT = 4;

    const ROLE_RECOMMENDATION_OF_CONSULTANT_TEXT = 'Recommendation of Consultant';
    const ROLE_LIST_OF_CONSULTANT_TEXT = 'List of Consultant';
    const ROLE_CONSULTANT_TEXT = 'Consultant';

    protected static function boot()
    {
        parent::boot();

        self::saving(function(self $model)
        {
            $model->title = mb_strtoupper($model->title);
            $model->reference_no = mb_strtoupper($model->reference_no);
        });

        self::created(function(self $model)
        {
            $user = \Confide::user();
            if($user)
            {
                $adminUsers = User::select('users.id')
                ->join('companies', 'users.company_id', '=', 'companies.id')
                ->whereNotExists(function($query) use($user, $model){
                    $query->select(\DB::raw(1))
                            ->from('consultant_management_user_roles')
                            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$model->id.' AND consultant_management_user_roles.role = '.self::ROLE_RECOMMENDATION_OF_CONSULTANT.' AND consultant_management_user_roles.user_id = users.id');
                })
                ->where('companies.id', '=', $user->company_id)
                ->whereRaw('users.is_admin IS TRUE')
                ->whereRaw('users.confirmed IS TRUE')
                ->whereRaw('users.account_blocked_status IS FALSE')
                ->orderBy('users.name', 'asc')
                ->get();

                $userRoles = [];
                foreach($adminUsers as $adminUser)
                {
                    $userRoles[] = [
                        'role' => self::ROLE_RECOMMENDATION_OF_CONSULTANT,//this role is the only role can create contract
                        'user_id' => $adminUser->id,
                        'consultant_management_contract_id' => $model->id,
                        'editor' => true,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                $importedAdminUsers = User::select('users.id')
                ->join('company_imported_users', 'company_imported_users.user_id', '=', 'users.id')
                ->whereNotExists(function($query) use($user, $model){
                    $query->select(\DB::raw(1))
                            ->from('consultant_management_user_roles')
                            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$model->id.' AND consultant_management_user_roles.role = '.self::ROLE_RECOMMENDATION_OF_CONSULTANT.' AND consultant_management_user_roles.user_id = users.id');
                })
                ->where('company_imported_users.company_id', '=', $user->company_id)
                ->whereRaw('users.is_admin IS TRUE')
                ->whereRaw('users.confirmed IS TRUE')
                ->whereRaw('users.account_blocked_status IS FALSE')
                ->orderBy('users.name', 'asc')
                ->get();

                foreach($importedAdminUsers as $adminUser)
                {
                    $userRoles[] = [
                        'role' => self::ROLE_RECOMMENDATION_OF_CONSULTANT,//this role is the only role can create contract
                        'user_id' => $adminUser->id,
                        'consultant_management_contract_id' => $model->id,
                        'editor' => true,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                if(!empty($userRoles))
                {
                    ConsultantManagementUserRole::insert($userRoles);
                }

                $companyRole = new ConsultantManagementCompanyRole;
                $companyRole->role = self::ROLE_RECOMMENDATION_OF_CONSULTANT;//this role is the only role can create contract;
                $companyRole->company_id = $user->company->id;
                $companyRole->consultant_management_contract_id = $model->id;
                $companyRole->calling_rfp = true;
                $companyRole->created_by = $user->id;
                $companyRole->updated_by = $user->id;

                $companyRole->save();
            }
        });

        static::deleting(function(self $model)
        {
            $model->consultantManagementSubsidiaries()->delete();
            $model->consultantManagementVendorCategories()->delete();
            $model->userRoles()->delete();
            $model->companyRoles()->delete();
            $model->consultantManagementAttachmentSettings()->delete();
            $model->consultantManagementQuestionnaires()->delete();
        });
    }

    public function consultantManagementSubsidiaries()
    {
        return $this->hasMany(ConsultantManagementSubsidiary::class)->orderBy('position', 'asc');
    }

    public function consultantManagementVendorCategories()
    {
        return $this->hasMany(ConsultantManagementVendorCategoryRfp::class);
    }

    public function userRoles()
    {
        return $this->hasMany(ConsultantManagementUserRole::class);
    }

    public function companyRoles()
    {
        return $this->hasMany(ConsultantManagementCompanyRole::class);
    }

    public function consultantManagementAttachmentSettings()
    {
        return $this->hasMany(ConsultantManagementAttachmentSetting::class);
    }

    public function consultantManagementQuestionnaires()
    {
        return $this->hasMany(ConsultantManagementQuestionnaire::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function subsidiary()
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function getShortTitleAttribute()
    {
        return StringOperations::shorten($this->title, 60);
    }

    public function getCallingRfpCompanyRole()
    {
        return $this->companyRoles()->whereRaw('calling_rfp IS TRUE')->first();
    }

    public function editableByUser(User $user)
    {
        $isContractEditor = $user->isConsultantManagementEditorByRole($this, self::ROLE_RECOMMENDATION_OF_CONSULTANT);

        return ($isContractEditor);
    }

    public function getUsersByRoleAndCompany($role, Company $company)
    {
        $recommendationOfConsultantCompany = Company::select('companies.*')
            ->join('consultant_management_company_roles', 'consultant_management_company_roles.company_id', '=', 'companies.id')
            ->where('consultant_management_company_roles.consultant_management_contract_id', '=', $this->id)
            ->where('consultant_management_company_roles.role', '=', $role)
            ->where('consultant_management_company_roles.company_id', '=', $company->id)
            ->first();
        
        $users = [];
        $importedUsers = [];

        if($recommendationOfConsultantCompany)
        {
            $companyUsers = User::select('users.id', 'users.name', 'users.email', 'users.account_blocked_status', 'users.is_admin')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->whereNotExists(function($query) use($role){
                $query->select(\DB::raw(1))
                        ->from('consultant_management_user_roles')
                        ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$this->id.' AND consultant_management_user_roles.role = '.$role.' AND consultant_management_user_roles.user_id = users.id');
            })
            ->where('companies.id', '=', $recommendationOfConsultantCompany->id)
            ->whereRaw('users.confirmed IS TRUE')
            ->orderBy('users.name', 'asc')
            ->get();

            /*
                * there might be users that were from $companyUsers but were moved to other companies or
                * the company role for ROLE_RECOMMENDATION_OF_CONSULTANT has been changed since the contract was created.
                * consultant_management_user_roles holds users even there is any changes happened to the company role for consultant management.
                * Then it is the company admin in consultant_management_user_roles responsibility to update the user role for this consultant management contract
                */
            $users = User::select(\DB::raw("users.id, users.name, users.email, users.account_blocked_status, users.is_admin, TRUE AS viewer, consultant_management_user_roles.editor,
            consultant_management_user_roles.consultant_management_contract_id, consultant_management_user_roles.role"))
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->whereRaw('consultant_management_user_roles.role = '.$role.' AND consultant_management_user_roles.user_id = users.id')
            ->where('consultant_management_user_roles.consultant_management_contract_id', '=', $this->id)
            ->where('companies.id', '=', $recommendationOfConsultantCompany->id)
            ->whereRaw('users.confirmed IS TRUE')
            ->orderBy('users.name', 'asc')
            ->get()
            ->toArray();

            foreach($companyUsers as $user)
            {
                $users[] = [
                    'id' => $user->id,
                    'name'=> $user->name,
                    'email' => $user->email,
                    'account_blocked_status' => $user->account_blocked_status,
                    'is_admin' => $user->is_admin,
                    'viewer' => false,
                    'editor' => false,
                    'consultant_management_contract_id' => $this->id,
                    'role' => $role
                ];
            }

            $companyImportedUsers = User::select('users.id', 'users.name', 'users.email', 'users.account_blocked_status', 'users.is_admin')
            ->join('company_imported_users', 'company_imported_users.user_id', '=', 'users.id')
            ->whereNotExists(function($query) use($role){
                $query->select(\DB::raw(1))
                        ->from('consultant_management_user_roles')
                        ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$this->id.' AND consultant_management_user_roles.role = '.$role.' AND consultant_management_user_roles.user_id = users.id');
            })
            ->where('company_imported_users.company_id', '=', $recommendationOfConsultantCompany->id)
            ->whereRaw('users.confirmed IS TRUE')
            ->orderBy('users.name', 'asc')
            ->get();

            $importedUsers = User::select(\DB::raw("users.id, users.name, users.email, users.account_blocked_status, users.is_admin, TRUE AS viewer, consultant_management_user_roles.editor,
            consultant_management_user_roles.consultant_management_contract_id, consultant_management_user_roles.role"))
            ->join('company_imported_users', 'company_imported_users.user_id', '=', 'users.id')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->whereRaw('consultant_management_user_roles.role = '.$role.' AND consultant_management_user_roles.user_id = users.id')
            ->where('consultant_management_user_roles.consultant_management_contract_id', '=', $this->id)
            ->where('company_imported_users.company_id', '=', $recommendationOfConsultantCompany->id)
            ->whereRaw('users.confirmed IS TRUE')
            ->orderBy('users.name', 'asc')
            ->get()
            ->toArray();

            foreach($companyImportedUsers as $user)
            {
                $importedUsers[] = [
                    'id' => $user->id,
                    'name'=> $user->name,
                    'email' => $user->email,
                    'account_blocked_status' => $user->account_blocked_status,
                    'is_admin' => $user->is_admin,
                    'viewer' => false,
                    'editor' => false,
                    'consultant_management_contract_id' => $this->id,
                    'role' => $role
                ];
            }
        }

        return [
            'users' => $users,
            'imported_users' => $importedUsers
        ];
    }

    public function getContractTimeZoneTime($timestamp)
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
}
