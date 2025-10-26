<?php

use PCK\Clauses\Clause;

class ClauseItemsTableSeeder_renameClauses extends Seeder {

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        Clause::where('name', '=', 'Main')->update(array( 'name' => Clause::TYPE_MAIN_TEXT ));
        Clause::where('name', '=', 'L & E')->update(array( 'name' => Clause::TYPE_LOSS_AND_EXPENSES_TEXT ));
        Clause::where('name', '=', 'Additional Expenses')->update(array( 'name' => Clause::TYPE_ADDITIONAL_EXPENSES_TEXT ));
        Clause::where('name', '=', 'Extension of Time')->update(array( 'name' => Clause::TYPE_EXTENSION_OF_TIME_TEXT ));
    }

}
