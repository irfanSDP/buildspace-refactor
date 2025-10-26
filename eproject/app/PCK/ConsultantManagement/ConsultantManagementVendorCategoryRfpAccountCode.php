<?php namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use PCK\Buildspace\AccountCode as BsAccountCode;

class ConsultantManagementVendorCategoryRfpAccountCode extends Model
{
    use SoftDeletingTrait;

    protected $table = 'consultant_management_vendor_categories_rfp_account_code';

    protected $fillable = ['vendor_category_rfp_id', 'account_code_id', 'created_by', 'updated_by'];

    public function bsAccountCode()
    {
        return $this->belongsTo(BsAccountCode::class, 'account_code_id');
    }
}