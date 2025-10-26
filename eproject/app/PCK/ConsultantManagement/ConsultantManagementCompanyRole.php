<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementUserRole;

class ConsultantManagementCompanyRole extends Model
{
    protected $table = 'consultant_management_company_roles';
    protected $primaryKey = ['role', 'consultant_management_contract_id', 'company_id'];
    protected $fillable = ['role', 'consultant_management_contract_id', 'company_id', 'calling_rfp'];

    public $incrementing = false;

      /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function(self $model)
        {
            $user = \Confide::user();
            if($user)
            {
                $adminUsers = User::select('users.id')
                ->join('companies', 'users.company_id', '=', 'companies.id')
                ->whereNotExists(function($query) use($model){
                    $query->select(\DB::raw(1))
                            ->from('consultant_management_user_roles')
                            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$model->consultant_management_contract_id.' AND consultant_management_user_roles.role = '.$model->role.' AND consultant_management_user_roles.user_id = users.id');
                })
                ->where('companies.id', '=', $model->company_id)
                ->whereRaw('users.is_admin IS TRUE')
                ->whereRaw('users.confirmed IS TRUE')
                ->whereRaw('users.account_blocked_status IS FALSE')
                ->orderBy('users.name', 'asc')
                ->get();

                $userRoles = [];
                foreach($adminUsers as $adminUser)
                {
                    $userRoles[] = [
                        'role' => $model->role,
                        'user_id' => $adminUser->id,
                        'consultant_management_contract_id' => $model->consultant_management_contract_id,
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
            }
        });
    }

    public function consultantManagementContract()
    {
        return $this->belongsTo(ConsultantManagementContract::class, 'consultant_management_contract_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
