<?php

use PCK\Tenders\Tender;
use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\Header;
use PCK\FormOfTender\Address;
use PCK\FormOfTender\PrintSettings;
use PCK\FormOfTender\Clause;
use PCK\FormOfTender\TenderAlternative;
use PCK\TenderAlternatives\TenderAlternativeList;

class CreateFormOfTendersForAllTendersTableSeeder extends Seeder {

	public function run()
	{
		foreach(Tender::all() as $tender)
		{
			$this->createIfNotExistFormOfTender($tender);
			$this->createIfNotExistFormOfTenderHeader($tender);
			$this->createIfNotExistFormOfTenderAddress($tender);
			$this->createIfNotExistFormOfTenderPrintSettings($tender);
			$this->createIfNotExistFormOfTenderClauses($tender);
			$this->createIfNotExistFormOfTenderTendreAlternatives($tender);
		}
	}

	private function createIfNotExistFormOfTender(Tender $tender)
	{
		if($tender->formOfTender) return;

		$formOfTender = new FormOfTender();
		$formOfTender->tender_id = $tender->id;
		$formOfTender->save();
	}

	private function createIfNotExistFormOfTenderHeader(Tender $tender)
	{
		$header = Header::where('tender_id', $tender->id)->first();

		if($header) return;

		$header = new Header();
		$header->tender_id = $tender->id;
		$header->header_text = Header::DEFAULT_TEXT;
		$header->is_template = false;
		$header->save();
	}

	private function createIfNotExistFormOfTenderAddress(Tender $tender)
	{
		$address = Address::where('tender_id', $tender->id)->first();

		if($address) return;

		$address = new Address();
		$address->tender_id = $tender->id;
		$address->address = Address::DEFAULT_TEXT;
		$address->is_template = false;
		$address->save();
	}

	private function createIfNotExistFormOfTenderPrintSettings(Tender $tender)
	{
		$printSettings = PrintSettings::where('tender_id', $tender->id)->first();

		if($printSettings) return;

		$printSettings = new PrintSettings();
        $printSettings->tender_id = $tender->id;
        $printSettings->margin_top = PrintSettings::DEFAULT_MARGIN;
        $printSettings->margin_bottom = PrintSettings::DEFAULT_MARGIN;
        $printSettings->margin_left = PrintSettings::DEFAULT_MARGIN;
        $printSettings->margin_right = PrintSettings::DEFAULT_MARGIN;
        $printSettings->include_header_line = PrintSettings::DEFAULT_INCLUDE_HEADER_LINE;
        $printSettings->header_spacing = PrintSettings::DEFAULT_HEADER_SPACING;
        $printSettings->footer_text = PrintSettings::DEFAULT_FOOTER_TEXT;
        $printSettings->footer_font_size = PrintSettings::DEFAULT_FOOTER_FONT_SIZE;
		$printSettings->font_size = PrintSettings::DEFAULT_FONT_SIZE;
		$printSettings->is_template = false;
        $printSettings->save();
	}

	private function createIfNotExistFormOfTenderClauses(Tender $tender)
	{
		$clauses = Clause::where('tender_id', $tender->id)->get();

		if($clauses->count() > 0) return;

		$clause = new Clause();
        $clause->tender_id = $tender->id;
        $clause->clause = Clause::DEFEAULT_TEXT;
        $clause->parent_id = 0;
		$clause->sequence_number = 1;
		$clause->is_template = false;
        $clause->save();
	}

	private function createIfNotExistFormOfTenderTendreAlternatives(Tender $tender)
	{
		$tenderAlternatives = TenderAlternative::where('tender_id', $tender->id)->get();

		if($tenderAlternatives->count() > 0) return;

		foreach(TenderAlternativeList::$list as $className)
        {
            $tenderAlternative                                = new TenderAlternative();
            $tenderAlternative->tender_id             		  = $tender->id;
			$tenderAlternative->tender_alternative_class_name = $className;
			$tenderAlternative->show						  = true;
			$tenderAlternative->is_template 				  = false;
            $tenderAlternative->save();
        }
	}
}