<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationSentColumnToClaimCertificatePaymentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_certificate_payments', function(Blueprint $table)
        {
            $table->boolean('notification_sent')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_certificate_payments', function(Blueprint $table)
        {
            $table->dropColumn('notification_sent');
        });
    }

}
