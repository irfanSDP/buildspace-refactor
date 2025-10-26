<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\FormOfTender\PrintSettings;

class AddFontSizeColumnToFormOfTenderPrintSettingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function()
        {
            Schema::table('form_of_tender_print_settings', function(Blueprint $table)
            {
                $table->integer('font_size')->nullable();
            });
            DB::table('form_of_tender_print_settings')->update(array( 'font_size' => PrintSettings::DEFAULT_FONT_SIZE ));

            $seeder = new FormOfTenderTableSeeder_addFontSize;
            $seeder->run();

            DB::statement('ALTER TABLE form_of_tender_print_settings ALTER COLUMN font_size SET NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_of_tender_print_settings', function(Blueprint $table)
        {
            $table->dropColumn('font_size');
        });
    }

}
