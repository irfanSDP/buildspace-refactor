<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropUploadedFilesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('uploaded_files');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('uploaded_files', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('file_name');
            $table->string('original_file_name');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

}