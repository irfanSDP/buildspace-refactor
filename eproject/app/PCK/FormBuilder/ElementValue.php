<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;

class ElementValue extends Model
{
    protected $table = 'element_values';

    public function additionalValue()
    {
        return $this->hasOne('PCK\FormBuilder\AdditionalElementValue', 'element_value_id');
    }

    public static function createNewRecord($element, $value)
    {
        $record                = new self();
        $record->element_id    = $element->id;
        $record->element_class = get_class($element);
        $record->value         = trim($value);
        $record->save();

        return self::find($record->id);
    }

    public static function getSavedElementValue($element)
    {
        $savedElementValueRecord = self::getSavedElementValueRecord($element);

        return $savedElementValueRecord ? $savedElementValueRecord->value : '';
    }

    public static function getSavedElementValues($element)
    {
        $savedElementValueRecords = self::getSavedElementValueRecords($element);

        return $savedElementValueRecords->lists('value');
    }

    public static function getSavedElementValueRecord($element)
    {
        return self::where('element_id', $element->id)->where('element_class', get_class($element))->first();
    }

    public static function getSavedElementValueRecords($element)
    {
        return self::where('element_id', $element->id)->where('element_class', get_class($element))->get();
    }

    public static function purgeElementValues($element)
    {
        $elementValues = self::getSavedElementValueRecords($element);

        foreach($elementValues as $elementValue)
        {
            $elementValue->delete();
        }
    }

    // for elements with a single value
    public static function syncElementValue($element, $value)
    {
        $record = self::getSavedElementValueRecord($element);

        if($record)
        {
            $record->value = $value;
            $record->save();

            $record = self::find($record->id);
        }
        else
        {
            $record = self::createNewRecord($element, $value);
        }

        return $record;
    }


    // for elements with single or multiple values
    public static function syncMultipleElementValues($element, array $values)
    {
        self::purgeElementValues($element);

        foreach($values as $value)
        {
            $value = trim($value);

            if(is_null($value) || ($value == '')) continue;

            $record = self::createNewRecord($element, $value);
        }
    }

    public static function getDistinctElementValueRecords()
    {
        $records = self::whereIn('work_categories.id', $workCategoryIds)
                            ->select('work_categories.id', 'work_categories.name')
                            ->distinct()
                            ->orderby('work_categories.name', 'ASC')
                            ->get();
    }
}