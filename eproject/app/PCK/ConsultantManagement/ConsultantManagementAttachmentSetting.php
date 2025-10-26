<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementConsultantAttachment;
use PCK\ConsultantManagement\ConsultantManagementExcludeAttachmentSetting;

class ConsultantManagementAttachmentSetting extends Model
{
    protected $table = 'consultant_management_attachment_settings';

    protected $fillable = ['consultant_management_contract_id', 'title', 'mandatory'];

    public function consultantManagementContract()
    {
        return $this->belongsTo(ConsultantManagementContract::class, 'consultant_management_contract_id');
    }

    public function excludeAttachmentSettings()
    {
        return $this->hasMany(ConsultantManagementExcludeAttachmentSetting::class);
    }

    public function consultantAttachments()
    {
        return $this->hasMany(ConsultantManagementConsultantAttachment::class);
    }

    public function deletable()
    {
        return !($this->consultantAttachments->count());
    }
}