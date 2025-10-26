<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\Elements\Element;

class AdditionalElementValue extends Model
{
    protected $table = 'additional_element_values';

    public function elementValue()
    {
        return $this->belongsTo('PCK/FormBuilder/ElementValue', 'element_value_id');
    }

    public static function createOrUpdateRecord(Element $element, $value)
    {
        $savedElementValueRecord = ElementValue::getSavedElementValueRecord($element);
        $record                  = $savedElementValueRecord->additionalValue;

        if(is_null($record))
        {
            $record = new self();
            $record->element_value_id = $savedElementValueRecord->id;
        }

        $record->value = $value;
        $record->save();

        return self::find($record->id);
    }

    public static function wipeRecord(Element $element)
    {
        $savedElementValueRecord = ElementValue::getSavedElementValueRecord($element);
        $record                  = $savedElementValueRecord->additionalValue;

        if($record)
        {
            $record->delete();
        }
    }
}