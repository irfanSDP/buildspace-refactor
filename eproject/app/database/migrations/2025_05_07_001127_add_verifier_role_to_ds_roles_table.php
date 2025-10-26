<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerifierRoleToDsRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasColumn('ds_roles', 'slug'))
        {
            $dateTime = new DateTime;

            DB::table('ds_roles')->insert(
                [
                    ['slug' => 'company-verifier', 'description' => 'Company Verifier', 'created_at' => $dateTime, 'updated_at' => $dateTime],
                    ['slug' => 'project-verifier', 'description' => 'Project Verifier', 'created_at' => $dateTime, 'updated_at' => $dateTime],
                ]
            );
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('ds_roles', 'slug'))
        {
            DB::table('ds_roles')->where('slug', 'company-verifier')->delete();
            DB::table('ds_roles')->where('slug', 'project-verifier')->delete();
        }
	}

}
