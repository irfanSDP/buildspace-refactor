<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevelopmentTypesProductTypesTable extends Migration
{
    public function up()
    {
        Schema::create('development_types', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('title', 255)->unique();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('product_types', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('title', 255)->unique();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('development_types_product_types', function(Blueprint $table)
        {
            $table->unsignedInteger('development_type_id')->index();
            $table->unsignedInteger('product_type_id')->index();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->foreign('development_type_id')->references('id')->on('development_types')->onDelete('cascade');
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->primary(['development_type_id', 'product_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('development_types_product_types');
        Schema::dropIfExists('development_types');
        Schema::dropIfExists('product_types');
    }
}
