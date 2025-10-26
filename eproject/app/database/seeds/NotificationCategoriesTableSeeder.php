<?php

class NotificationCategoriesTableSeeder extends Seeder {

	public function run()
	{
		$now = Carbon\Carbon::now();

		DB::table('notification_categories')->insert(
			array( 'name' => 'system', 'text' => 'System', 'created_at' => $now, 'updated_at' => $now )
		);
	}

}