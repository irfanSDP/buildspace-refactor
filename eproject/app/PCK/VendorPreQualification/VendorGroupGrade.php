<?php namespace PCK\VendorPreQualification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class VendorGroupGrade extends Model
{
    use SoftDeletingTrait;

    protected $table = 'vendor_pre_qualification_vendor_group_grades';

    protected $fillable = ['contract_group_category_id', 'vendor_management_grade_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $object)
        {
            if($user = \Confide::user())
            {
                $object->created_at = $user->id;
            }
        });

        static::saving(function(self $object)
        {
            if($user = \Confide::user())
            {
                $object->updated_at = $user->id;
            }
        });
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public static function getGradeByGroup($contractGroupCategoryId)
    {
        $record = self::where('contract_group_category_id', '=', $contractGroupCategoryId)->first();

        if( ! $record ) return null;

        return $record->vendorManagementGrade;
    }
}