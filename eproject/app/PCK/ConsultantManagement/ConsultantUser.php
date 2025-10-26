<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\Companies\Company;
use PCK\Users\User;

class ConsultantUser extends Model
{
    protected $table = 'consultant_management_consultant_users';
    protected $fillable = ['user_id', 'is_admin'];
    
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

    public static function createConsultantUserFromCompanyIds(Array $companyIds, User $createdBy)
    {
        if(empty($companyIds))
        {
            return false;
        }

        $existingUserCompanyIds = User::selectRaw("DISTINCT users.company_id")
        ->join("consultant_management_consultant_users", "users.id", "=", "consultant_management_consultant_users.user_id")
        ->whereIn('users.company_id', $companyIds)
        ->groupBy(\DB::raw('users.company_id'))
        ->lists('company_id');

        $filteredUserCompanyIds = !empty($existingUserCompanyIds) ? array_diff($companyIds, $existingUserCompanyIds) : $companyIds;

        if(!empty($filteredUserCompanyIds))
        {
            $adminUsers = User::selectRaw("DISTINCT users.id")
            ->whereIn('users.company_id', $filteredUserCompanyIds)
            ->whereRaw('users.is_admin IS TRUE')
            ->lists('id');

            if(!empty($adminUsers))
            {
                $existingUserIds = ConsultantUser::selectRaw("DISTINCT consultant_management_consultant_users.user_id")
                ->whereIn('consultant_management_consultant_users.user_id', $adminUsers)
                ->lists('id');

                $filteredUserIds = !empty($existingUserIds) ? array_diff($adminUsers, $existingUserIds) : $adminUsers;

                if(!empty($filteredUserIds))
                {
                    $data = [];
                    foreach($adminUsers as $id)
                    {
                        $data[] = [
                            'user_id'    => $id,
                            'is_admin'   => true,
                            'created_by' => $createdBy->id,
                            'updated_by' => $createdBy->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }

                    self::insert($data);
                }
            }
        }
    }
}
