<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ObjectField\ObjectField;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;

class ConsultantManagementSubsidiary extends Model
{
    protected $table = 'consultant_management_subsidiaries';

    protected $fillable = ['consultant_management_contract_id', 'subsidiary_id', 'development_type_id', 'business_case', 'gross_acreage', 'project_budget', 'total_construction_cost', 'total_landscape_cost', 'cost_per_square_feet', 'planning_permission_date', 'building_plan_date', 'launch_date', 'position'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            $r = ConsultantManagementSubsidiary::selectRaw("COALESCE(MAX(consultant_management_subsidiaries.position), 0) AS position")
            ->where('consultant_management_contract_id', '=', $model->consultant_management_contract_id)
            ->groupBy('consultant_management_contract_id')
            ->first();

            $position = ($r) ? $r->position : 0;

            $model->position = $position+1;
        });

        static::deleting(function(self $model)
        {
            $model->productTypes()->delete();
        });
    }

    public function consultantManagementContract()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementContract', 'consultant_management_contract_id');
    }

    public function subsidiary()
    {
        return $this->belongsTo('PCK\Subsidiaries\Subsidiary');
    }

    public function developmentType()
    {
        return $this->belongsTo('PCK\ConsultantManagement\DevelopmentType');
    }

    public function productTypes()
    {
        return $this->hasMany('PCK\ConsultantManagement\ConsultantManagementProductType');
    }

    public function deletable()
    {
        return true;
    }

    //override delete() to remove any uploaded attachments
    public function delete()
    {
        $object = ObjectField::findRecord($this, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF);

        if($object)
        {
            ModuleUploadedFile::deletePreviousAttachments($object);
        }

        parent::delete();
    }
}
