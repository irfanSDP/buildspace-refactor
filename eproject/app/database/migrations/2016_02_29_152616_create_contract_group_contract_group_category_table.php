<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractGroupContractGroupCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_group_contract_group_category', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('contract_group_id');
            $table->unsignedInteger('contract_group_category_id');
			$table->timestamps();

            $table->foreign('contract_group_id')->references('id')->on('contract_groups')
                ->onDelete('cascade');

            $table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')
                ->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contract_group_contract_group_category');
	}

}
