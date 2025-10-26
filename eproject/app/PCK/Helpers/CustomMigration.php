<?php namespace PCK\Helpers;

use Illuminate\Database\Migrations\Migration;

abstract class CustomMigration extends Migration {

	public function __construct()
	{
		$this->schema = \DB::connection()->getSchemaBuilder();

		$this->schema->blueprintResolver(function ($table, $callback)
		{
			return new CustomBlueprint($table, $callback);
		});
	}

}