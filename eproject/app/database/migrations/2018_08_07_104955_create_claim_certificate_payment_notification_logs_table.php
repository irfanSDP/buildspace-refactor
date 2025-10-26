<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimCertificatePaymentNotificationLogsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_certificate_payment_notification_logs', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('claim_certificate_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->index('claim_certificate_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('claim_certificate_payment_notification_logs');
    }

}
