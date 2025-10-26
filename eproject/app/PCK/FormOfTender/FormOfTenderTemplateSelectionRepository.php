<?php namespace PCK\FormOfTender;

class FormOfTenderTemplateSelectionRepository
{
    public function getAllTemplates()
    {
        $formOfTenderTemplates = FormOfTender::where('is_template', true)->orderBy('id', 'ASC')->get();
        $templates = [];
        $count = 0;

        foreach($formOfTenderTemplates as $template)
        {
            array_push($templates, [
                'indexNo'               => ++$count,
                'id'                    => $template->id,
                'name'                  => $template->name,
                'route_edit_template'   => route('form_of_tender.template.edit', [$template->id]),
                'route_update_name'     => route('form_of_tender.template.update', [$template->id]),
                'route_delete_template' => route('form_of_tender.template.delete', [$template->id]),
                'printRoute'            => route('form_of_tender.template.print', [$template->id]),
                'csrf_token'            => csrf_token(),
            ]);
        }

        return $templates;
    }

    public function updateTemplate($templateId, $inputs)
    {
        $formOfTender = FormOfTender::find($templateId);
        $formOfTender->name = $inputs['name'];
        $formOfTender->save();

        return true;
    }
}

