<?php namespace PCK\FormBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\Elements\Element;

class FormElementMapping extends Model
{
    protected $table = 'form_element_mappings';

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (self $model)
        {
            self::updatePriority($model);
        });
    }

    public function section()
    {
        return $this->belongsTo('PCK\FormBuilder\FormColumnSection', 'form_column_section_id');
    }

    public static function getElementMappingByElement($element)
    {
        return self::where('element_id', $element->id)->where('element_class', get_class($element))->first();
    }

    public static function createNewMapping(FormColumnSection $section, $element)
    {
        $mapping                         = new self();
        $mapping->form_column_section_id = $section->id;
        $mapping->element_id             = $element->id;
        $mapping->element_class          = get_class($element);
        $mapping->priority               = self::getNextFreePriority($section);
        $mapping->save();

        return self::find($mapping->id);
    }

    public function clone(FormColumnSection $section)
    {
        $class         = $this->element_class;
        $originElement = $class::find($this->element_id);
        $newElement    = $class::clone($originElement);

        self::createNewMapping($section, $newElement);
    }

    public static function getNextFreePriority(FormColumnSection $section)
    {
        $latestRecord = self::where('form_column_section_id', $section->id)->orderBy('priority', 'DESC')->first();

        if(is_null($latestRecord)) return 0;

        return ($latestRecord->priority + 1);
    }

    public static function updatePriority(self $removedRecord)
    {
        $query = DB::raw('UPDATE ' . (new self)->getTable() . ' SET priority = (priority - 1) WHERE form_column_section_id = ' . $removedRecord->section->id . ' AND priority > ' . $removedRecord->priority . ';');

        DB::update($query);
    }

    public static function deleteElementMapping($element)
    {
        $mapping = self::getElementMappingByElement($element);
        
        if($mapping)
        {
            $mapping->delete();
        }
    }

    public static function swap(self $draggedElementMapping, self $swappedElementMapping)
    {
        $draggedElementMappingPriority = $draggedElementMapping->priority;
        $swappedElementMappingPriority = $swappedElementMapping->priority;

        $draggedElementMapping->priority = $swappedElementMappingPriority;
        $draggedElementMapping->save();

        $swappedElementMapping->priority = $draggedElementMappingPriority;
        $swappedElementMapping->save();
    }
}

