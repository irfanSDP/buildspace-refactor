<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class InspectionListCategoryAdditionalField extends Model
{
    protected $table    = 'inspection_list_category_additional_fields';
    protected $fillable = ['inspection_list_category_id', 'name', 'value', 'priority'];

    public function inspectionListCategory()
    {
        return $this->belongsTo('PCK\Inspections\InspectionListCategory')->orderBy('priority', 'ASC');
    }

    public static function getNextFreePriority($inspectionListCategoryId)
    {
        $record = self::where('inspection_list_category_id', $inspectionListCategoryId)->orderBy('priority', 'DESC')->first();

        if(is_null($record)) return 0;
        
        return ($record->priority + 1);
    }

    public static function updatePriority($removedRecord)
    {
        $records = self::where('inspection_list_category_id', $removedRecord->inspection_list_category_id)->where('priority', '>', $removedRecord->priority)->get();

        foreach($records as $record)
        {
            $record->priority = ($record->priority - 1);
            $record->save();
        }
    }

    public static function purge($inspectionListCategoryId)
    {
        self::where('inspection_list_category_id', $inspectionListCategoryId)->delete();
    }
}

