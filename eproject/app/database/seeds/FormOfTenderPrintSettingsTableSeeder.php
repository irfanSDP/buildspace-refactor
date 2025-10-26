<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\PrintSettings;

class FormOfTenderPrintSettingsTableSeeder extends Seeder {

	public function run()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			$printSetting = PrintSettings::where('tender_id', $formOfTender->tender_id)->first();
			$printSetting->form_of_tender_id = $formOfTender->id;
			$printSetting->save();
		}

		$template = FormOfTender::where('is_template', true)->first();
		$printSetting = PrintSettings::whereNull('tender_id')->where('is_template', true)->first();

		if($printSetting)
		{
			$printSetting->form_of_tender_id = $template->id;
			$printSetting->save();
		}
	}

	public function rollback()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			$printSetting = PrintSettings::where('form_of_tender_id', $formOfTender->id)->first();
			$printSetting->tender_id = $formOfTender->tender_id;
			$printSetting->is_template = $formOfTender->is_template;
			$printSetting->save();
		}

		$template = FormOfTender::where('is_template', true)->first();
		$printSetting = PrintSettings::whereNull('tender_id')->first();
		$printSetting->tender_id = $template->tender_id;
		$printSetting->is_template = true;
		$printSetting->save();
	}
}