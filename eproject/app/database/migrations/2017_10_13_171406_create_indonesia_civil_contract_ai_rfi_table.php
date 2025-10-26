<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndonesiaCivilContractAiRfiTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indonesia_civil_contract_ai_rfi', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('indonesia_civil_contract_architect_instruction_id');
            $table->unsignedInteger('document_control_object_id');
            $table->timestamps();

            $table->foreign('indonesia_civil_contract_architect_instruction_id', 'indonesia_civil_contract_ai_rfi_ai_id_foreign')->references('id')->on('indonesia_civil_contract_architect_instructions')->onDelete('cascade');
            $table->foreign('document_control_object_id', 'indonesia_civil_contract_ai_rfi_rfi_id_foreign')->references('id')->on('document_control_objects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('indonesia_civil_contract_ai_rfi');
    }

}
