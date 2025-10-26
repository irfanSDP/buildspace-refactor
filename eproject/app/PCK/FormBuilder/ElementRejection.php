<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;

class ElementRejection extends Model
{
    protected $table = 'element_rejections';

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public static function findRecordByElement($element)
    {
        return self::where('element_id', $element->id)->where('element_class', get_class($element))->first();
    }

    public static function updateOrCreateRecord($element, $remarks)
    {
        $record = self::findRecordByElement($element);

        if(is_null($record))
        {
            $record                = new self();
            $record->element_id    = $element->id;
            $record->element_class = get_class($element);
            $record->created_by    = \Confide::user()->id;
        }

        $record->is_amended = false;
        $record->remarks    = trim($remarks);
        $record->updated_by = \Confide::user()->id;
        $record->save();

        return self::find($record->id);
    }

    public static function markAsAmeded($element)
    {
        $record = self::findRecordByElement($element);

        if($record)
        {
            $record->is_amended = true;
            $record->save();
    
            return self::find($record->id);
        }

        return null;
    }

    public static function deleteRecord($element)
    {
        $record = self::findRecordByElement($element);

        if($record)
        {
            $record->delete();
        }
    }
}