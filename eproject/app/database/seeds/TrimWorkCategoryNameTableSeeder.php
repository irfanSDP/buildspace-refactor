<?php

class TrimWorkCategoryNameTableSeeder extends Seeder {

	public function run()
	{
		\DB::statement("UPDATE " . with(new \PCK\WorkCategories\WorkCategory())->getTable() . " SET name = TRIM(name), identifier = TRIM(identifier);");
	}

}