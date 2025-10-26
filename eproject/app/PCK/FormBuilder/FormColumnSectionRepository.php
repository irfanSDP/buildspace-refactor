<?php namespace PCK\FormBuilder;

class FormColumnSectionRepository
{
    public function store($formColumnId, $inputs)
    {
        $formColumn = FormColumn::find($formColumnId);

        FormColumnSection::createColumnSection($formColumn, $inputs['name']);
    }

    public function getColumnSectionComponents($formColumnId)
    {
        $formColumn = FormColumn::find($formColumnId);

        $data = [];

        foreach($formColumn->sections()->orderBy('priority', 'ASC')->get() as $section)
        {
            array_push($data, [
                'id'   => $section->id,
                'name' => $section->name,
            ]);
        }

        return $data;
    }

    public function swap($draggedSectionId, $swappedSectionId)
    {
        $draggedSection = FormColumnSection::find($draggedSectionId);
        $swappedSection = FormColumnSection::find($swappedSectionId);
        
        FormColumnSection::swap($draggedSection, $swappedSection);
    }

    public function updateColumnName(FormColumnSection $section, $name)
    {
        $section->name = $name;
        $section->save();
    }

    public function getSectionSelections(FormColumn $column)
    {
        $sections = [];

        foreach(FormColumnSection::where('form_column_id', '!=', $column->id)->orderBy('priority', 'ASC')->get() as $section)
        {
            if(!$section->column->dynamicForm->isTemplate()) continue;
            
            array_push($sections, [
                'id'   => $section->id,
                'name' => $section->name,
            ]);
        }

        return $sections;
    }
}

