<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\Header;

class FormOfTenderHeadersTableSeeder extends Seeder {

	public function run()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			$header = Header::where('tender_id', $formOfTender->tender_id)->first();
			$header->form_of_tender_id = $formOfTender->id;
			$header->save();
		}

		$template = FormOfTender::where('is_template', true)->first();
		$header = Header::whereNull('tender_id')->where('is_template', true)->first();

		if($header)
		{
			$header->form_of_tender_id = $template->id;
			$header->save();
		}
	}

	public function rollback()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			$header = Header::where('form_of_tender_id', $formOfTender->id)->first();
			$header->tender_id = $formOfTender->tender_id;
			$header->is_template = $formOfTender->is_template;
			$header->save();
		}

		$template = FormOfTender::where('is_template', true)->first();
		$header = Header::whereNull('tender_id')->first();
		$header->tender_id = $template->tender_id;
		$header->is_template = true;
		$header->save();
	}
}