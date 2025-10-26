<?php

use Illuminate\Database\Migrations\Migration;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\LossOrAndExpenses\LossOrAndExpense;

class DropClauseItemLossOrAndExpenseTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(LossOrAndExpense::all() as $object)
        {
            $selectedClauses = DB::table('clause_item_loss_or_and_expense')->where('loss_or_and_expense_id', '=', $object->id)->lists('clause_item_id');
            AttachedClauseItem::syncClauses($object, $selectedClauses ?? array());
        }

        $originalMigration = new CreateClauseItemLossOrAndExpenseTable;
        $originalMigration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $originalMigration = new CreateClauseItemLossOrAndExpenseTable;
        $originalMigration->up();
    }

}
