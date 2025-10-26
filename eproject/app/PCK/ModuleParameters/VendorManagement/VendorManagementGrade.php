<?php namespace PCK\ModuleParameters\VendorManagement;


use Illuminate\Database\Eloquent\Model;

class VendorManagementGrade extends Model
{
    protected $table = 'vendor_management_grades';

    public function levels()
    {
        return $this->hasMany('PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel', 'vendor_management_grade_id');
    }

    public static function getGradeTemplates()
    {
        return self::where('is_template', true)->orderBy('id', 'ASC')->get();
    }

    public static function createNewRecord($name, $isTemplate = true)
    {
        $record              = new self();
        $record->name        = $name;
        $record->is_template = $isTemplate;
        $record->created_by  = \Confide::user()->id;
        $record->updated_by  = \Confide::user()->id;
        $record->save();

        return self::find($record->id);
    }

    public function clone()
    {
        $clonedGrade = self::createNewRecord($this->name, false);

        foreach($this->levels as $originGradeLevel)
        {
            $originGradeLevel->clone($clonedGrade);
        }

        return $clonedGrade;
    }

    public function getGrade($score)
    {
        $score = round($score, 0);

        if($this->levels()->count() < 1) return null;

        $level = $this->levels()->where('score_upper_limit', '>=', $score)->orderby('score_upper_limit', 'asc')->first();

        if( ! $level )
        {
            $level = $this->levels()->orderBy('score_upper_limit', 'desc')->first();
        }

        return $level;
    }

    public function copyAndAttach($object, $deleteOriginalGrade = true, $reference_field = 'vendor_management_grade_id')
    {
        $originalGrade = $object->{$reference_field} ? self::find($object->{$reference_field}) : null;

        $clonedGrade = $this->clone();

        $object->{$reference_field} = $clonedGrade->id;
        $object->save();

        if ($originalGrade && $deleteOriginalGrade)
        {
            $originalGrade->delete();
        }
    }

    public function getLevelRanges()
    {
        $ranges = [];

        $levelIds = [];

        foreach($this->levels()->orderby('score_upper_limit', 'asc')->get() as $key => $level)
        {
            $levelIds[$key] = $level->id;

            $ranges[$level->id] = [
                'min' => $key == 0 ? 0 : $ranges[$levelIds[$key-1]]['max']+1,
                'max' => $level->score_upper_limit
            ];
        }

        return $ranges;
    }
}