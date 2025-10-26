<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\TenderAlternative;
use PCK\TenderAlternatives\TenderAlternativeList;

class FormOfTenderAllTenderAlternativesTableSeeder extends Seeder
{
	public function run()
	{
		$formOfTenderTemplates = FormOfTender::whereNull('tender_id')->where('is_template', true)->get();
		$formOfTenders = FormOfTender::whereNotNull('tender_id')->where('is_template', false)->get();

		$this->seedData($formOfTenderTemplates);
		$this->seedData($formOfTenders);
	}

	public function seedData($formOfTenders)
	{
		foreach($formOfTenders as $formOfTender)
		{
			foreach(TenderAlternativeList::$list as $class)
			{
				$record = TenderAlternative::where('form_of_tender_id', $formOfTender->id)
							->where('tender_alternative_class_name', '=', $class)
							->first();

				if(!$record)
				{
					$tenderAlternative = new TenderAlternative();
					$tenderAlternative->form_of_tender_id = $formOfTender->id;
					$tenderAlternative->tender_alternative_class_name = $class;
					$tenderAlternative->show = false;
					$tenderAlternative->save();
				}
			}
		}
	}
}