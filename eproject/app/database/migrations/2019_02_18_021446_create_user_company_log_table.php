<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Users\User;

class CreateUserCompanyLogTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_company_log', function(Blueprint $table)
        {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index('company_id');
            $table->index('user_id');
        });

        $this->seed();
    }

    private function seed()
    {
        $actingUser = User::where('is_super_admin', '=', true)
            ->orderBy('id', 'asc')
            ->first();

        $timestamp = Carbon\Carbon::now();
        $data      = array();

        foreach(User::where('is_super_admin', '=', false)->get() as $user)
        {
            $data[] = array(
                'user_id'    => $user->id,
                'company_id' => $user->company_id,
                'created_by' => $actingUser->id,
                'created_at' => $user->created_at,
                'updated_at' => $timestamp,
            );
        }

        if( ! empty($data) ) \PCK\Users\UserCompanyLog::insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_company_log');
    }

}
