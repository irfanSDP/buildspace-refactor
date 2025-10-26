<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndonesiaCivilContractContractualClaimResponsesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indonesia_civil_contract_contractual_claim_responses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('subject');
            $table->text('content');
            $table->unsignedInteger('object_id');
            $table->string('object_type');
            $table->unsignedInteger('sequence');
            $table->integer('type');
            $table->decimal('proposed_value', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->index(array( 'object_id', 'object_type' ), 'indonesia_civil_contract_contractual_claim_responses_index');
            $table->unique(array( 'object_id', 'object_type', 'sequence' ), 'indonesia_civil_contract_contractual_claim_responses_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('indonesia_civil_contract_contractual_claim_responses');
    }

}
