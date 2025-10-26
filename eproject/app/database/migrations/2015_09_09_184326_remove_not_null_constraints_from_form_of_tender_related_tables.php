<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintsFromFormOfTenderRelatedTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_of_tender_clauses', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_clauses ALTER COLUMN tender_id DROP NOT NULL');
        });

        Schema::table('form_of_tender_headers', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_headers ALTER COLUMN tender_id DROP NOT NULL');
        });

        Schema::table('form_of_tender_addresses', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_addresses ALTER COLUMN tender_id DROP NOT NULL');
        });

        Schema::table('form_of_tender_print_settings', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_print_settings ALTER COLUMN tender_id DROP NOT NULL');
        });

        Schema::table('form_of_tender_logs', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_logs ALTER COLUMN tender_id DROP NOT NULL');
        });

        Schema::table('form_of_tender_tender_alternatives', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_tender_alternatives ALTER COLUMN tender_id DROP NOT NULL');
        });

        Schema::table('form_of_tender_details', function (Blueprint $table)
        {
            DB::statement('ALTER TABLE form_of_tender_details ALTER COLUMN tender_id DROP NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_of_tender_clauses', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_clauses SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_clauses ALTER COLUMN tender_id SET NOT NULL');
        });

        Schema::table('form_of_tender_headers', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_headers SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_headers ALTER COLUMN tender_id SET NOT NULL');
        });

        Schema::table('form_of_tender_addresses', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_addresses SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_addresses ALTER COLUMN tender_id SET NOT NULL');
        });

        Schema::table('form_of_tender_print_settings', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_print_settings SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_print_settings ALTER COLUMN tender_id SET NOT NULL');
        });

        Schema::table('form_of_tender_logs', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_logs SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_logs ALTER COLUMN tender_id SET NOT NULL');
        });

        Schema::table('form_of_tender_tender_alternatives', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_tender_alternatives SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_tender_alternatives ALTER COLUMN tender_id SET NOT NULL');
        });

        Schema::table('form_of_tender_details', function (Blueprint $table)
        {
            DB::statement('UPDATE form_of_tender_details SET tender_id = ? where tender_id IS NULL', array(\PCK\Tenders\Tender::first()->id));
            DB::statement('ALTER TABLE form_of_tender_details ALTER COLUMN tender_id SET NOT NULL');
        });
    }

}
