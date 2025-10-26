<?php

use Illuminate\Database\Migrations\Migration;
use PCK\AdditionalExpenses\AdditionalExpense;
use PCK\ClauseItems\AttachedClauseItem;

class DropAdditionalExpenseClauseItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(AdditionalExpense::all() as $object)
        {
            $selectedClauses = DB::table('additional_expense_clause_item')->where('additional_expense_id', '=', $object->id)->lists('clause_item_id');
            AttachedClauseItem::syncClauses($object, $selectedClauses ?? array());
        }

        $originalMigration = new CreateAdditionalExpenseClauseItemTable;
        $originalMigration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $originalMigration = new CreateAdditionalExpenseClauseItemTable;
        $originalMigration->up();
    }

}
