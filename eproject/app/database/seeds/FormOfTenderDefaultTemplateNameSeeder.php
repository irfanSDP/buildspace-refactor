<?php

use PCK\FormOfTender\FormOfTender;

class FormOfTenderDefaultTemplateNameSeeder extends Seeder {

	public function run()
	{
		$formOfTenderTemplates = FormOfTender::whereNull('tender_id')->get();

		foreach($formOfTenderTemplates as $formOfTenderTemplate)
		{
			$formOfTenderTemplate->name = FormOfTender::DEFAULT_NAME;
			$formOfTenderTemplate->save();
		}
	}

}