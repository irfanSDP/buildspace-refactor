<?php namespace PCK\DigitalStar\Evaluation;

class DsCycleTemplateFormRepository
{
    public function getTitle($cycleTemplateFormId)
    {
        $record = DsCycleTemplateForm::find($cycleTemplateFormId);
        if (! $record) {
            return '';
        }
        $templateForm = $record->templateForm;
        if (! $templateForm) {
            return '';
        }
        $weightedNode = $templateForm->weightedNode;
        if (! $weightedNode) {
            return '';
        }

        return $weightedNode->name;
    }

    public function list(DsCycle $cycle)
    {
        $records = $cycle->cycleTemplateForms()->orderBy('type', 'ASC')->get();
        $data    = array();

        foreach($records as $record)
        {
            $rowData = array();
            $rowData['id'] = $record->id;
            $rowData['cycle_id'] = $record->ds_cycle_id;
            $rowData['type'] = $record->type;
            $rowData['template_id'] = $record->ds_template_form_id;
            $rowData['template_type'] = trans('digitalStar/digitalStar.' . $record->type . 'Evaluation');

            $templateTitle = $this->getTitle($record->id);
            $rowData['template_title'] = ! empty($templateTitle) ? $templateTitle : 'N/A';

            $rowData['route:assign_form'] = route('digital-star.cycle.assign-form.assign', [$record->ds_cycle_id, $record->type]);

            $data[] = $rowData;
        }

        return $data;
    }
}