<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Inspections\InspectionListCategory;

class CreateInspectionListCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_list_categories', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('inspection_list_id');
            $table->integer('parent_id')->nullable()->index();
            $table->integer('lft')->nullable()->index();
            $table->integer('rgt')->nullable()->index();
            $table->integer('depth')->nullable();
            $table->string('name')->nullable();
            $table->integer('type')->default(InspectionListCategory::TYPE_INSPECTION_CATEGORY);
            $table->unsignedInteger('priority');
            $table->timestamps();

            $table->index('inspection_list_id');

            $table->foreign('inspection_list_id')->references('id')->on('inspection_lists')->onDelete('cascade');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::drop('inspection_list_categories');
    }
}
