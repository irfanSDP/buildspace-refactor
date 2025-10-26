<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachedClauseItemsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attached_clause_items', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('attachable_id');
            $table->string('attachable_type');
            $table->char('no', 25);
            $table->text('description');
            $table->integer('priority');
            $table->unsignedInteger('origin_id');
            $table->timestamps();

            $table->index(array( 'attachable_id', 'attachable_type' ), 'attached_clause_items_index');
            $table->unique(array( 'attachable_id', 'attachable_type', 'priority' ), 'attached_clause_items_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attached_clause_items');
    }

}
