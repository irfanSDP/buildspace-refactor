<?php

class FormOfTenderTableSeeder_addFontSize extends Seeder {

    public function run()
    {
        \DB::table('form_of_tender_print_settings')->update(array( 'font_size' => \PCK\FormOfTender\PrintSettings::DEFAULT_FONT_SIZE, ));
    }
}