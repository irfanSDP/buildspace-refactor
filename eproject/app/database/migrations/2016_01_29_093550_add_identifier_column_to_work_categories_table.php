<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\WorkCategories\WorkCategory;

class AddIdentifierColumnToWorkCategoriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_categories', function (Blueprint $table)
        {
            $table->unique('name');
            $table->string('identifier', WorkCategory::IDENTIFIER_MAX_CHARS)->unique()->nullable();
        });

        $this->generateIdentifiers();

        // Set not null constraint
        DB::statement('
            alter table work_categories
            alter identifier set not null;
          ');
    }

    /**
     * Generate identifiers for existing work categories.
     */
    public function generateIdentifiers()
    {
        // Create new unique identifiers for each work category.
        $workCategories = WorkCategory::all();
        foreach($workCategories as $workCategory)
        {
            $workCategory->identifier = WorkCategory::generateIdentifier($workCategory->name);
            $workCategory->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_categories', function (Blueprint $table)
        {
            $table->dropColumn('identifier');
        });
    }

}
