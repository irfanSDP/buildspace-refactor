<?php namespace PCK\LetterOfAward;

class LetterOfAwardTemplateSelectionRepository
{
    public function getAllTemplates()
    {
        $letterOfAwardTemplates = LetterOfAward::where('is_template', true)->orderBy('id', 'ASC')->get();
        $templates = [];
        $count = 0;

        foreach($letterOfAwardTemplates as $template)
        {
            array_push($templates, [
                'indexNo'               => ++$count,
                'id'                    => $template->id,
                'name'                  => $template->name,
                'route_edit_template'   => route('letterOfAward.template.index', [$template->id]),
                'route_update_name'     => route('letterOfAward.template.update', [$template->id]),
                'route_delete_template' => route('letterOfAward.template.delete', [$template->id]),
                'printRoute'            => route('letterOfAward.template.process', [$template->id]),
                'csrf_token'            => csrf_token(),
            ]);
        }

        return $templates;
    }

    public function updateTemplate($templateId, $inputs)
    {
        $letterOfAward = LetterOfAward::find($templateId);
        $letterOfAward->name = $inputs['name'];
        $letterOfAward->save();

        return true;
    }
}

