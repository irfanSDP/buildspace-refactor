<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStructuredDocumentClausesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('structured_document_clauses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('content');
            $table->boolean('is_editable')->default(true);
            $table->unsignedInteger('parent_id')->nullable();
            $table->integer('priority')->default(1);
            $table->unsignedInteger('structured_document_id');
            $table->timestamps();

            $table->foreign('structured_document_id')->references('id')->on('structured_documents')->onDelete('cascade');

            $table->index('structured_document_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('structured_document_clauses');
    }

}
