<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Users\User;
use PCK\Companies\Company;

class ProcessorDeleteCompanyLog extends Model
{
    protected $table = 'processor_delete_company_logs';

    public function contractGroupCategory()
    {
        return $this->belongsTo(ContractGroupCategory::class, 'contract_group_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function logAction(Company $company)
    {
        $log                             = new self;
        $log->name                       = $company->name;
        $log->reference_no               = $company->reference_no;
        $log->contract_group_category_id = $company->contract_group_category_id;
        $log->created_by                 = \Confide::user()->id;
        $log->updated_by                 = \Confide::user()->id;
        $log->save();

        return self::find($log->id);
    }
}