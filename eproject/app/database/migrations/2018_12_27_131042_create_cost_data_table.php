<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('buildspace_origin_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('buildspace_origin_id');
        });

        if( Schema::hasTable(with(new \PCK\Buildspace\CostData)->getTable()) )
        {
            $seeder = new CostDataTableSeeder;
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_data');
    }

}
