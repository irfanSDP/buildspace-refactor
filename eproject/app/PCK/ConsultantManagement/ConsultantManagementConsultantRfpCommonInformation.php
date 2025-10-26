<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ConsultantManagementConsultantRfpCommonInformation extends Model
{
    protected $table = 'consultant_management_consultant_rfp_common_information';

    protected $fillable = ['consultant_management_consultant_rfp_id', 'name_in_loa', 'remarks', 'contact_name', 'contact_number', 'contact_email'];

    protected static function boot()
    {
        parent::boot();

        self::saving(function(self $model)
        {
            $model->name_in_loa = mb_strtoupper($model->name_in_loa);
            $model->contact_name = mb_strtoupper($model->contact_name);
        });
    }

    public function consultantManagementConsultantRfp()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementConsultantRfp', 'consultant_management_consultant_rfp_id');
    }
}