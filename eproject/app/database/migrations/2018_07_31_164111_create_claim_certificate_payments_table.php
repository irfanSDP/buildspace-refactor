<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimCertificatePaymentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_certificate_payments', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('claim_certificate_id');
            $table->string('bank', 200);
            $table->string('reference', 200);
            $table->decimal('amount', 15);
            $table->timestamp('date');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->index('claim_certificate_id');
            $table->unique('reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('claim_certificate_payments');
    }

}
