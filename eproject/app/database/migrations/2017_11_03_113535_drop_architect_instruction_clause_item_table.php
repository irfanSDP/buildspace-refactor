<?php

use Illuminate\Database\Migrations\Migration;
use PCK\ArchitectInstructions\ArchitectInstruction;
use PCK\ClauseItems\AttachedClauseItem;

class DropArchitectInstructionClauseItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(ArchitectInstruction::all() as $object)
        {
            $selectedClauses = DB::table('architect_instruction_clause_item')->where('architect_instruction_id', '=', $object->id)->lists('clause_item_id');
            AttachedClauseItem::syncClauses($object, $selectedClauses ?? array());
        }

        $originalMigration = new CreateArchitectInstructionClauseItemTable;
        $originalMigration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $originalMigration = new CreateArchitectInstructionClauseItemTable;
        $originalMigration->up();
    }

}
