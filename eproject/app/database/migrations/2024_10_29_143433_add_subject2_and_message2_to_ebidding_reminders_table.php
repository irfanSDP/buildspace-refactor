<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubject2AndMessage2ToEbiddingRemindersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_bidding_email_reminders', 'subject2'))
        {
            Schema::table('e_bidding_email_reminders', function (Blueprint $table) {
                $table->string('subject2')->nullable()->after('subject');
            });
        }

        if (! Schema::hasColumn('e_bidding_email_reminders', 'message2'))
        {
            Schema::table('e_bidding_email_reminders', function (Blueprint $table) {
                $table->text('message2')->nullable()->after('message');
            });
        }

        if (Schema::hasColumn('e_bidding_email_reminders', 'subject2') && Schema::hasColumn('e_bidding_email_reminders', 'message2'))
        {
            $emailReminders = DB::table('e_bidding_email_reminders')->get();

            foreach ($emailReminders as $emailReminder)
            {
                DB::table('e_bidding_email_reminders')
                    ->where('id', $emailReminder->id)
                    ->update([
                        'subject2' => trans('eBiddingReminder.subjectBidding'),
                        'message2' => trans('eBiddingReminder.messageBidding'),
                    ]);
            }
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('e_bidding_email_reminders', 'subject2'))
        {
            Schema::table('e_bidding_email_reminders', function (Blueprint $table) {
                $table->dropColumn('subject2');
            });
        }

        if (Schema::hasColumn('e_bidding_email_reminders', 'message2'))
        {
            Schema::table('e_bidding_email_reminders', function (Blueprint $table) {
                $table->dropColumn('message2');
            });
        }
	}

}
