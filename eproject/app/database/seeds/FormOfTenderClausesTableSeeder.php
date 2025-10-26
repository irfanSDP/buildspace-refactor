<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\Clause;

class FormOfTenderClausesTableSeeder extends Seeder {

	public function run()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(Clause::where('tender_id', $formOfTender->tender_id)->get() as $clause)
			{
				$clause->form_of_tender_id = $formOfTender->id;
				$clause->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(Clause::where('is_template', true)->get() as $clause)
		{
			$clause->form_of_tender_id = $template->id;
			$clause->save();
		}

	}

	public function rollback()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(Clause::where('form_of_tender_id', $formOfTender->id)->get() as $clause)
			{
				$clause->tender_id = $formOfTender->tender_id;
				$clause->is_template = $formOfTender->is_template;
				$clause->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(Clause::whereNull('tender_id')->get() as $clause)
		{
			$clause->tender_id = $template->tender_id;
			$clause->is_template = true;
			$clause->save();
		}
	}
}