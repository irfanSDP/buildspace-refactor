<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageTypeToUniqueConstraintInDocumentControlObjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_control_objects', function (Blueprint $table)
        {
            $table->dropUnique('document_control_objects_project_id_reference_number_unique');
            $table->unique(array( 'project_id', 'reference_number', 'message_type' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_control_objects', function (Blueprint $table)
        {
            $table->dropUnique('document_control_objects_project_id_reference_number_message_ty');
            $table->unique(array( 'project_id', 'reference_number' ));
        });
    }

}
