<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\Address;

class FormOfTenderAddressesTableSeeder extends Seeder
{
    public function run()
    {
        foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			$address = Address::where('tender_id', $formOfTender->tender_id)->first();
			$address->form_of_tender_id = $formOfTender->id;
			$address->save();
		}

		$template = FormOfTender::where('is_template', true)->first();
		$address = Address::whereNull('tender_id')->where('is_template', true)->first();

		if($address)
		{
			$address->form_of_tender_id = $template->id;
			$address->save();
		}
    }

    public function rollback()
    {
        foreach(FormOfTender::where('is_template', false)->get() as $formOfTender)
		{
			$address = Address::where('form_of_tender_id', $formOfTender->id)->first();
			$address->tender_id = $formOfTender->tender_id;
			$address->is_template = $formOfTender->is_template;
			$address->save();
		}

		$template = FormOfTender::where('is_template', true)->first();
		$address = Address::whereNull('tender_id')->first();
		$address->tender_id = $template->tender_id;
		$address->is_template = true;
		$address->save();
    }
}

