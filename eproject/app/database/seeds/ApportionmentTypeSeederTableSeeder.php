<?php

use PCK\AccountCodeSettings\ApportionmentType;

class ApportionmentTypeSeederTableSeeder extends Seeder
{
	private $apportionmentTypeNames = [
		'Build Up Area',
		'Land Area',
		'Total Units',
		'GDV',
	];

	public function run()
	{
		foreach($this->apportionmentTypeNames as $apportionmentTypeName)
		{
			$apportionmentType = ApportionmentType::where('name', '=', $apportionmentTypeName)->first();

			if($apportionmentType) continue;

			$newApportionmentType = new ApportionmentType();
			$newApportionmentType->name = $apportionmentTypeName;
			$newApportionmentType->save();
		}
	}
}