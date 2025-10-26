<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndonesiaCivilContractEwLeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indonesia_civil_contract_ew_le', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('indonesia_civil_contract_ew_id');
            $table->unsignedInteger('indonesia_civil_contract_le_id');
            $table->timestamps();

            $table->foreign('indonesia_civil_contract_ew_id', 'indonesia_civil_contract_ew_le_ew_id_foreign')->references('id')->on('indonesia_civil_contract_early_warnings')->onDelete('cascade');
            $table->foreign('indonesia_civil_contract_le_id', 'indonesia_civil_contract_ew_le_le_id_foreign')->references('id')->on('indonesia_civil_contract_loss_and_expenses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('indonesia_civil_contract_ew_le');
    }

}
