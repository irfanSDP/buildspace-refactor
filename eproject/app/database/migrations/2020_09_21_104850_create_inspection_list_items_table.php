<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Inspections\InspectionListItem;

class CreateInspectionListItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_list_items', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('inspection_list_category_id');
            $table->integer('parent_id')->nullable()->index();
            $table->integer('lft')->nullable()->index();
            $table->integer('rgt')->nullable()->index();
            $table->integer('depth')->nullable();
            $table->text('description');
            $table->unsignedInteger('priority');
            $table->integer('type')->default(InspectionListItem::TYPE_ITEM);
            $table->timestamps();

            $table->index('inspection_list_category_id');

            $table->foreign('inspection_list_category_id')->references('id')->on('inspection_list_categories')->onDelete('cascade');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::drop('inspection_list_items');
    }
}
