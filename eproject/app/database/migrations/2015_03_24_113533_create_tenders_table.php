<?php

use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PCK\Helpers\CustomBlueprint;
use PCK\Helpers\CustomMigration;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class CreateTendersTable extends CustomMigration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('tenders', function (CustomBlueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id')->index();
            $table->unsignedInteger('count')->default(0);
            $table->smallInteger('current_form_type', false, true)->index()->default(Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER);
            $table->dateTime('tender_starting_date')->nullable()->index();
            $table->dateTime('tender_closing_date')->nullable()->index();
            $table->boolean('retender_status')->default('false')->index();
            $table->smallInteger('retender_verification_status', false, true)->index()->default(FormLevelStatus::IN_PROGRESS);
            $table->smallInteger('open_tender_status', false, true)->index()->default(Tender::OPEN_TENDER_STATUS_NOT_YET_OPEN);
            $table->smallInteger('open_tender_verification_status', false, true)->index()->default(FormLevelStatus::IN_PROGRESS);
            $table->signAbleColumns();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('tenders');
    }

}