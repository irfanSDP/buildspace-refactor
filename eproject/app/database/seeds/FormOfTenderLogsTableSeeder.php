<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\Log;

class FormOfTenderLogsTableSeeder extends Seeder {

	public function run()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(Log::where('tender_id', $formOfTender->tender_id)->get() as $log)
			{
				$log->form_of_tender_id = $formOfTender->id;
				$log->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(Log::where('is_template', true)->get() as $log)
		{
			$log->form_of_tender_id = $template->id;
			$log->save();
		}
	}

	public function rollback()
	{
		foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			foreach(Log::where('form_of_tender_id', $formOfTender->id)->get() as $log)
			{
				$log->tender_id = $formOfTender->tender_id;
				$log->is_template = $formOfTender->is_template;
				$log->save();
			}
		}

		$template = FormOfTender::where('is_template', true)->first();

		foreach(Log::whereNull('tender_id')->get() as $log)
		{
			$log->tender_id = $template->tender_id;
			$log->is_template = true;
			$log->save();
		}
	}
}