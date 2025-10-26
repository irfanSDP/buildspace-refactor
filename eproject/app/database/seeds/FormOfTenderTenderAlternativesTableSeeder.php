<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\TenderAlternative;

class FormOfTenderTenderAlternativesTableSeeder extends Seeder {

	public function run()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(TenderAlternative::where('tender_id', $formOfTender->tender_id)->get() as $tenderAlternative)
			{
				$tenderAlternative->form_of_tender_id = $formOfTender->id;
				$tenderAlternative->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(TenderAlternative::where('is_template', true)->get() as $tenderAlternative)
		{
			$tenderAlternative->form_of_tender_id = $template->id;
			$tenderAlternative->save();
		}
	}

	public function rollback()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(TenderAlternative::where('form_of_tender_id', $formOfTender->id)->get() as $tenderAlternative)
			{
				$tenderAlternative->tender_id = $formOfTender->tender_id;
				$tenderAlternative->is_template = $formOfTender->is_template;
				$tenderAlternative->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(TenderAlternative::whereNull('tender_id')->get() as $tenderAlternative)
		{
			$tenderAlternative->tender_id = $template->tender_id;
			$tenderAlternative->is_template = true;
			$tenderAlternative->save();
		}
	}
}