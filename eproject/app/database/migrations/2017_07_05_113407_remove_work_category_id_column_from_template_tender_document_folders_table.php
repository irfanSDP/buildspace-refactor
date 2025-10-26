<?php

use Illuminate\Database\Migrations\Migration;

class RemoveWorkCategoryIdColumnFromTemplateTenderDocumentFoldersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $migration = new AddWorkCategoryIdColumnToTemplateTenderDocumentFoldersTable;
        $migration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $migration = new AddWorkCategoryIdColumnToTemplateTenderDocumentFoldersTable;
        $migration->up();
    }

}
