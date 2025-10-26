<?php

use PCK\FormOfTender\FormOfTender;

class FormOfTendersTableTemplateSeeder extends Seeder
{
    public function run()
    {
        $template = new FormOfTender();
        $template->is_template = true;
        $template->name = FormOfTender::DEFAULT_NAME;
        $template->save();
    }
}

