<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire;

class ConsultantManagementConsultantReplyAttachment extends Model
{
    protected $table = 'consultant_management_consultant_reply_attachments';

    protected $fillable = ['consultant_management_questionnaire_id', 'consultant_management_consultant_questionnaire_id'];

    protected static function boot()
    {
        parent::boot();
    }
    
    public function consultantManagementQuestionnaire()
    {
        return $this->belongsTo(ConsultantManagementQuestionnaire::class, 'consultant_management_questionnaire_id');
    }

    public function consultantManagementConsultantQuestionnaire()
    {
        return $this->belongsTo(ConsultantManagementConsultantQuestionnaire::class, 'consultant_management_consultant_questionnaire_id');
    }

    public function deletable()
    {
        return true;
    }
}