<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTenderStageColumnToTenderRemindersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_reminders', function(Blueprint $table)
        {
            $table->integer('tender_stage')->default(\PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER);
            $table->unique(array( 'tender_id', 'tender_stage' ));
        });

        $newTenderReminders = array();

        foreach(\PCK\TenderReminder\TenderReminder::all() as $tenderReminder)
        {
            if( $tenderReminder->tender->getTenderStage() == \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER )
            {
                $newTenderReminders[] = array(
                    'tender_id'    => $tenderReminder->tender->id,
                    'message'      => $tenderReminder->message,
                    'tender_stage' => \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER,
                    'updated_by'   => $tenderReminder->updated_by,
                    'created_at'   => $tenderReminder->updated_at,
                    'updated_at'   => $tenderReminder->updated_at,
                );
            }
        }

        if( ! empty( $newTenderReminders ) ) \PCK\TenderReminder\TenderReminder::insert($newTenderReminders);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \PCK\TenderReminder\TenderReminder::where('tender_stage', '=', \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER)->delete();

        Schema::table('tender_reminders', function(Blueprint $table)
        {
            $table->dropColumn('tender_stage');
        });
    }

}
