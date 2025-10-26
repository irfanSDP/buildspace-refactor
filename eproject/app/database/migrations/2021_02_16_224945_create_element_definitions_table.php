<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\FormBuilder\ElementDefinition;

class CreateElementDefinitionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('element_definitions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('element_render_identifier');
			$table->string('module_class');
			$table->timestamps();
		});

		foreach(ElementDefinition::getAllElementDefinitionCombinations() as $definitionCombination)
		{
			ElementDefinition::createElementDefinitionIfNotExists($definitionCombination['element_render_identifier'], $definitionCombination['module_class']);
		}
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('element_definitions');
	}

}
