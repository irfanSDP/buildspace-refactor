<?php namespace PCK\FormBuilder;

use PCK\FormBuilder\Elements\ElementRepository;

class FormColumnRepository
{
    private $elementRepository;

    public function __construct(ElementRepository $elementRepository)
    {
        $this->elementRepository = $elementRepository;
    }

    public function getColumnContents(FormColumn $column, $designMode = false)
    {
        $data = [];

        foreach($column->sections()->orderBy('priority', 'ASC')->get() as $section)
        {
            $temp['id']       = $section->id;
            $temp['name']     = $section->name;
            $temp['contents'] = $this->elementRepository->getElementsBySection($section);

            if($designMode)
            {
                $temp['route_update_section'] = route('form.column.section.update', [$section->id]);
                $temp['route_delete_section'] = route('form.column.section.delete', [$section->id]);
                $temp['route_new_element']    = route('form.column.section.element.store', [$section->id]);
                $temp['route_swap_element']   = route('form.column.section.element.swap', [$section->id]);
            }
            else
            {
                $temp['section_submit_url'] = route('vendor.form.section.submit', [$section->id]);
            }

            $data[] = $temp;
        }
        
        return $data;
    }

    public function createNewColumn(DynamicForm $form, $inputs)
    {
        $name = $inputs['name'];

        return FormColumn::createNewColumn($form, $name);
    }

    public function updateColumnName(FormColumn $column, $name)
    {
        $column->name = $name;
        $column->save();
    }

    public function swap($draggedColumnId, $swappedColumnId)
    {
        $draggedColumn = FormColumn::find($draggedColumnId);
        $swappedColumn = FormColumn::find($swappedColumnId);
        
        FormColumn::swap($draggedColumn, $swappedColumn);
    }

    public function getColumnSelections(DynamicForm $form)
    {
        $columns = [];

        foreach(FormColumn::where('dynamic_form_id', '!=', $form->id)->orderBy('id', 'ASC')->get() as $column)
        {
            if(!$column->dynamicForm->isTemplate()) continue;

            array_push($columns, [
                'id'   => $column->id,
                'name' => $column->name,
            ]);
        }

        return $columns;
    }
}

