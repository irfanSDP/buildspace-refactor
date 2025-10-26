<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\PrintSettings;

class FormOfTenderTableSeeder extends Seeder {

    public function run()
    {
        $now = \Carbon\Carbon::now();
        $template = FormOfTender::where('is_template', true)->first();

        \DB::table('form_of_tender_clauses')->insert(array(
            0 =>
                array(
                    'clause'            => 'Template Clause Item',
                    'parent_id'         => 0,
                    'sequence_number'   => 1,
                    'form_of_tender_id' => $template->id,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ),
        ));

        \DB::table('form_of_tender_headers')->insert(array(
            0 =>
                array(
                    'header_text'         => 'Template Header',
                    'form_of_tender_id'   => $template->id,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ),
        ));

        \DB::table('form_of_tender_addresses')->insert(array(
            0 =>
            array(
                'address'           => 'Template Address',
                'form_of_tender_id' => $template->id,
                'created_at'        => $now,
                'updated_at'        => $now,
            ),
        ));

        \DB::table('form_of_tender_print_settings')->insert(array(
            0 =>
                array(
                    'form_of_tender_id'   => $template->id,
                    'margin_top'          => PrintSettings::DEFAULT_MARGIN,
                    'margin_bottom'       => PrintSettings::DEFAULT_MARGIN,
                    'margin_left'         => PrintSettings::DEFAULT_MARGIN,
                    'margin_right'        => PrintSettings::DEFAULT_MARGIN,
                    'include_header_line' => PrintSettings::DEFAULT_INCLUDE_HEADER_LINE,
                    'header_spacing'      => PrintSettings::DEFAULT_HEADER_SPACING,
                    'footer_text'         => PrintSettings::DEFAULT_FOOTER_TEXT,
                    'footer_font_size'    => PrintSettings::DEFAULT_FOOTER_FONT_SIZE,
                    'font_size'           => PrintSettings::DEFAULT_FONT_SIZE,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ),
        ));
    }
}