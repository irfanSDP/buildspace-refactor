<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTenderTenderAlternativesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('company_tender_tender_alternatives', function(Blueprint $table) {
            $table->integer('company_tender_id')->unsigned();
            $table->integer('tender_alternative_id')->unsigned();
            $table->decimal('tender_amount', 19, 2)->default(0);
            $table->decimal('other_bill_type_amount_except_prime_cost_provisional', 19, 2)->default(0);
            $table->decimal('supply_of_material_amount', 19, 2)->default(0);
            $table->decimal('original_tender_amount', 19, 2)->default(0);
            $table->decimal('discounted_percentage')->default(0);
            $table->decimal('discounted_amount', 19, 2)->default(0);
            $table->decimal('completion_period', 19, 2)->default(0);
            $table->decimal('contractor_adjustment_amount', 19, 2)->default(0);
            $table->decimal('contractor_adjustment_percentage')->default(0);
            $table->boolean('earnest_money')->default(false);
            $table->string('remarks')->nullable();

            $table->timestamps();

            $table->foreign('company_tender_id')->references('id')->on('company_tender')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['company_tender_id', 'tender_alternative_id']);
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company_tender_tender_alternatives');
    }

}
