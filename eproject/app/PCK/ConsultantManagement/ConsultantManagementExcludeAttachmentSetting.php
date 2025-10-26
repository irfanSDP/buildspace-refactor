<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementAttachmentSetting;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;

class ConsultantManagementExcludeAttachmentSetting extends Model
{
    protected $table = 'consultant_management_exclude_attachment_settings';

    protected $fillable = ['consultant_management_attachment_setting_id', 'vendor_category_rfp_id'];

    public function consultantManagementAttachmentSetting()
    {
        return $this->belongsTo(ConsultantManagementAttachmentSetting::class, 'consultant_management_attachment_setting_id');
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }
}