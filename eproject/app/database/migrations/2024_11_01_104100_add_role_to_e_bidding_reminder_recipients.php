<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleToEBiddingReminderRecipients extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_bidding_email_reminder_recipients', 'role'))
        {
            Schema::table('e_bidding_email_reminder_recipients', function (Blueprint $table) {
                $table->string('role', 10)->nullable()->after('user_id');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('e_bidding_email_reminder_recipients', 'role'))
        {
            Schema::table('e_bidding_email_reminder_recipients', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
	}

}
