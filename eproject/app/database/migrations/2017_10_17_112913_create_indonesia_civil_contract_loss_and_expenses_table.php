<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndonesiaCivilContractLossAndExpensesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indonesia_civil_contract_loss_and_expenses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('indonesia_civil_contract_ai_id')->nullable();
            $table->string('reference');
            $table->string('subject');
            $table->text('details');
            $table->unsignedInteger('status');
            $table->decimal('claim_amount', 18, 2);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('indonesia_civil_contract_ai_id', 'indonesia_civil_contract_loss_and_expenses_ai_id_foreign')->references('id')->on('indonesia_civil_contract_architect_instructions');
            $table->unique(array( 'project_id', 'reference' ));
            $table->index('project_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('indonesia_civil_contract_loss_and_expenses');
    }

}
