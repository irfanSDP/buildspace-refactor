<?php

use Illuminate\Database\Migrations\Migration;
use PCK\ArchitectInstructionMessages\ArchitectInstructionMessage;
use PCK\ClauseItems\AttachedClauseItem;

class DropArchitectInstructionMessageClauseItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(ArchitectInstructionMessage::all() as $object)
        {
            $selectedClauses = DB::table('architect_instruction_message_clause_item')->where('architect_instruction_message_id', '=', $object->id)->lists('clause_item_id');
            AttachedClauseItem::syncClauses($object, $selectedClauses ?? array());
        }

        $originalMigration = new CreateArchitectInstructionMessageClauseItemTable;
        $originalMigration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $originalMigration = new CreateArchitectInstructionMessageClauseItemTable;
        $originalMigration->up();
    }

}
