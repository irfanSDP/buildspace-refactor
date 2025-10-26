<?php

class FormOfTenderTenderIdToNullTableSeeder extends Seeder {

    public function run()
    {
        \DB::table( (new \PCK\FormOfTender\Address())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
        \DB::table( (new \PCK\FormOfTender\Clause())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
        \DB::table( (new \PCK\FormOfTender\Header())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
        \DB::table( (new \PCK\FormOfTender\Log())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
        \DB::table( (new \PCK\FormOfTender\PrintSettings())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
        \DB::table( (new \PCK\FormOfTender\TenderAlternative())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
        \DB::table( (new \PCK\FormOfTender\TenderAlternativesPosition())->getTable() )->where('is_template', '=', true)->update(array('tender_id' => null));
    }
}