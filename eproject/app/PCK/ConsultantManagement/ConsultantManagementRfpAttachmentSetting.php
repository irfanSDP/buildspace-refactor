<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;

class ConsultantManagementRfpAttachmentSetting extends Model
{
    protected $table = 'consultant_management_rfp_attachment_settings';

    protected $fillable = ['vendor_category_rfp_id', 'title', 'mandatory'];
    
    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function deletable()
    {
        return true;
    }
}