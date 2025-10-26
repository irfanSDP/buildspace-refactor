<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\TenderAlternativesPosition;

class FormOfTenderTenderAlternativePositionsTableSeeder extends Seeder {

	public function run()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(TenderAlternativesPosition::where('tender_id', $formOfTender->tender_id)->get() as $position)
			{
				$position->form_of_tender_id = $formOfTender->id;
				$position->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(TenderAlternativesPosition::where('is_template', true)->get() as $position)
		{
			$position->form_of_tender_id = $template->id;
			$position->save();
		}
	}

	public function rollback()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(TenderAlternativesPosition::where('form_of_tender_id', $formOfTender->id)->get() as $position)
			{
				$position->tender_id = $formOfTender->tender_id;
				$position->is_template = $formOfTender->is_template;
				$position->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(TenderAlternativesPosition::whereNull('tender_id')->get() as $position)
		{
			$position->tender_id = $template->tender_id;
			$position->is_template = true;
			$position->save();
		}
	}
}