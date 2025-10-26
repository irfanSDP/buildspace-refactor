<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\Companies\Company;
use PCK\Users\User;

class ConsultantManagementCompanyRoleLog extends Model
{
    protected $table = 'consultant_management_company_role_logs';
    protected $fillable = ['role', 'consultant_management_contract_id', 'company_id', 'calling_rfp'];
    
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
