<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use PCK\Statuses\FormStatus;
use PCK\Traits\FormTrait;
use PCK\Helpers\VpeHelper;

class TemplateForm extends Model implements FormStatus {

    use FormTrait;

    protected $table = 'vendor_performance_evaluation_template_forms';

    protected $fillable = ['contract_group_category_id', 'project_status_id', 'weighted_node_id', 'original_form_id', 'revision', 'vendor_management_grade_id'];

    public function contractGroupCategory()
    {
        return $this->belongsTo('PCK\ContractGroupCategory\ContractGroupCategory');
    }

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $form)
        {
            if( is_null($form->revision) ) $form->revision = 0;
            if( is_null($form->status_id) ) $form->status_id = self::STATUS_DRAFT;
        });

        static::created(function(self $form)
        {
            if( is_null($form->original_form_id) ) $form->original_form_id = $form->id;

            $form->save();
        });

        static::updating(function(self $form)
        {
            if($form->isDirty('status_id') && $form->status_id == self::STATUS_COMPLETED)
            {
                self::where('original_form_id', '=', $form->original_form_id)->update(array('current_selected_revision' => false));

                $form->current_selected_revision = true;
            }
        });
    }

    public static function getCurrentEditingForm($originalFormId)
    {
        return self::where('original_form_id', '=', $originalFormId)
            ->orderBy('revision', 'desc')
            ->first();
    }

    public static function getTemplateForm($originalFormId)
    {
        return self::where('original_form_id', '=', $originalFormId)
            ->where('status_id', '=', self::STATUS_COMPLETED)
            ->orderBy('revision', 'desc')
            ->first();
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }
}