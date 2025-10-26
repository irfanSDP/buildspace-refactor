<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderDocumentFolders\TenderDocumentFolder;

class AddFolderTypeColumnToTenderDocumentFoldersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_document_folders', function(Blueprint $table)
        {
            $table->integer('folder_type')->default(TenderDocumentFolder::TYPE_FOLDER);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tender_document_folders', function(Blueprint $table)
        {
            $table->dropColumn('folder_type');
        });
    }

}
