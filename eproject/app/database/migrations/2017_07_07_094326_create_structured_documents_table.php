<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStructuredDocumentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('structured_documents', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('title');
            $table->text('heading');
            $table->integer('margin_top');
            $table->integer('margin_bottom');
            $table->integer('margin_left');
            $table->integer('margin_right');
            $table->string('footer_text', 20);
            $table->integer('font_size');
            $table->boolean('is_template')->default(false);
            $table->unsignedInteger('object_id');
            $table->string('object_type');
            $table->timestamps();

            $table->index('object_type');
            $table->unique(array( 'object_id', 'object_type' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('structured_documents');
    }

}
