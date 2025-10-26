<?php namespace PCK\ModuleParameters\VendorManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class VendorManagementGradeLevel extends Model
{
    protected $table = 'vendor_management_grade_levels';

    public function grade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public static function createNewRecord(VendorManagementGrade $grade, $description, $score, $definition)
    {
        $record                             = new self();
        $record->vendor_management_grade_id = $grade->id;
        $record->description                = $description;
        $record->definition                 = $definition;
        $record->score_upper_limit          = $score;
        $record->created_by                 = \Confide::user()->id;
        $record->updated_by                 = \Confide::user()->id;
        $record->save();

        return self::find($record->id);
    }

    public function clone(VendorManagementGrade $grade)
    {
        return self::createNewRecord($grade, $this->description, $this->score_upper_limit, $this->definition);
    }

    public function getScoreLowerLimitAttribute()
    {
        $record = self::where('vendor_management_grade_id', $this->vendor_management_grade_id)->where('score_upper_limit', '<', $this->score_upper_limit)->orderBy('score_upper_limit', 'DESC')->first();

        return is_null($record) ? 0 : ($record->score_upper_limit + 1);
    }
}
