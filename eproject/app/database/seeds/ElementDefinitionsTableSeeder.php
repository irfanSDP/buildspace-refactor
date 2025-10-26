<?php

use PCK\FormBuilder\ElementDefinition;

class ElementDefinitionsTableSeeder extends Seeder
{
	public function run()
	{
		foreach(ElementDefinition::getAllElementDefinitionCombinations() as $definitionCombination)
		{
			ElementDefinition::createElementDefinitionIfNotExists($definitionCombination['element_render_identifier'], $definitionCombination['module_class']);
		}
	}
}