<?php

use PCK\Clauses\Clause;

class ClausesTableSeeder_addTypeColumnData extends Seeder {

    public function run()
    {
        Clause::where('name', '=', Clause::TYPE_MAIN_TEXT)->update(array( 'type' => Clause::TYPE_MAIN ));
        Clause::where('name', '=', Clause::TYPE_LOSS_AND_EXPENSES_TEXT)->update(array( 'type' => Clause::TYPE_LOSS_AND_EXPENSES ));
        Clause::where('name', '=', Clause::TYPE_ADDITIONAL_EXPENSES_TEXT)->update(array( 'type' => Clause::TYPE_ADDITIONAL_EXPENSES ));
        Clause::where('name', '=', Clause::TYPE_EXTENSION_OF_TIME_TEXT)->update(array( 'type' => Clause::TYPE_EXTENSION_OF_TIME ));
    }

}