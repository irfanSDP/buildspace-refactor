<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTechnicalEvaluationColumnsToTenderLotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_lot_information', function (Blueprint $table)
        {
            $table->boolean('technical_evaluation_required')->default(false);
            $table->unsignedInteger('contract_limit_id')->nullable();

            $table->foreign('contract_limit_id')->references('id')->on('contract_limits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tender_lot_information', function (Blueprint $table)
        {
            $table->dropColumn('technical_evaluation_required');
            $table->dropColumn('contract_limit_id');
        });
    }

}
