<?php namespace PCK\VendorPreQualification;

use Illuminate\Database\Eloquent\Model;

class Setup extends Model {

    protected $table = 'vendor_pre_qualification_setups';

    protected $fillable = ['vendor_category_id', 'vendor_work_category_id'];

    public function vendorCategory()
    {
        return $this->belongsTo('PCK\VendorCategory\VendorCategory');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory');
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }
}