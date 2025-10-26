<?php

use Illuminate\Database\Migrations\Migration;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\ExtensionOfTimes\ExtensionOfTime;

class DropClauseItemExtensionOfTimeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(ExtensionOfTime::all() as $object)
        {
            $selectedClauses = DB::table('clause_item_extension_of_time')->where('extension_of_time_id', '=', $object->id)->lists('clause_item_id');
            AttachedClauseItem::syncClauses($object, $selectedClauses ?? array());
        }

        $originalMigration = new CreateClauseItemExtensionOfTimeTable;
        $originalMigration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $originalMigration = new CreateClauseItemExtensionOfTimeTable;
        $originalMigration->up();
    }

}
