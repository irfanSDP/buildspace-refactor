<?php namespace PCK\FormBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;

class FormColumnSection extends Model
{
    protected $table = 'form_column_sections';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            $model->deleteRelatedModels();
        });

        static::deleted(function (self $model)
        {
            self::updatePriority($model);
        });
    }

    public function column()
    {
        return $this->belongsTo('PCK\FormBuilder\FormColumn', 'form_column_id');
    }

    public function mappings()
    {
        return $this->hasMany('PCK\FormBuilder\FormElementMapping', 'form_column_section_id');
    }

    public static function createColumnSection(FormColumn $formColumn, $name)
    {
        $section                 = new self();
        $section->form_column_id = $formColumn->id;
        $section->name           = $name;
        $section->priority       = self::getNextFreePriority($formColumn);
        $section->save();

        return self::find($section->id);
    }

    public function clone(FormColumn $column)
    {
        $newSection = self::createColumnSection($column, $this->name);

        foreach($this->mappings()->orderBy('priority', 'ASC')->get() as $originMapping)
        {
            $originMapping->clone($newSection);
        }
    }

    public static function getNextFreePriority(FormColumn $formColumn)
    {
        $latestRecord = self::where('form_column_id', $formColumn->id)->orderBy('priority', 'DESC')->first();

        if(is_null($latestRecord)) return 0;

        return ($latestRecord->priority + 1);
    }

    public static function updatePriority(self $removedRecord)
    {
        $query = DB::raw('UPDATE ' . (new self)->getTable() . ' SET priority = (priority - 1) WHERE form_column_id = ' . $removedRecord->column->id . ' AND priority > ' . $removedRecord->priority . ';');

        DB::update($query);
    }

    public static function swap(self $draggedSection, self $swappedSection)
    {
        $draggedSectionPriority = $draggedSection->priority;
        $swappedSectionPriority = $swappedSection->priority;

        $draggedSection->priority = $swappedSectionPriority;
        $draggedSection->save();

        $swappedSection->priority = $draggedSectionPriority;
        $swappedSection->save();
    }

    public function deleteRelatedModels()
    {
        foreach($this->mappings as $mapping)
        {
            $element = null;

            if($mapping->element_class == SystemModuleElement::class)
            {
                $element = SystemModuleElement::find($mapping->element_id);
            }
            else
            {
                $element = Element::findById($mapping->element_id);
            }

            $element->delete();
        }
    }

    public function getAllFormElementIdsGroupedByType()
    {
        $records[Element::ELEMENT_TYPE_ID]             = [];
        $records[SystemModuleElement::ELEMENT_TYPE_ID] = [];

        foreach($this->mappings as $mapping)
        {
            if($mapping->element_class == SystemModuleElement::class)
            {
                array_push($records[SystemModuleElement::ELEMENT_TYPE_ID], $mapping->element_id);
            }
            else
            {
                array_push($records[Element::ELEMENT_TYPE_ID], $mapping->element_id);
            }
        }

        return $records;
    }
}