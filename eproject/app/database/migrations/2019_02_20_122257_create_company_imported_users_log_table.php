<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Users\User;

class CreateCompanyImportedUsersLogTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_imported_users_log', function(Blueprint $table)
        {
            $table->increments('id');

            $table->unsignedInteger('company_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('created_by');
            $table->boolean('import')->default(true);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_id')->references('id')->on('users');
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

        $data = array();

        foreach(DB::table('company_imported_users')->get() as $record)
        {
            $data[] = array(
                'company_id' => $record->company_id,
                'user_id'    => $record->user_id,
                'created_by' => $actingUser->id,
                'import'     => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }

        if( ! empty($data) ) \PCK\Companies\CompanyImportedUsersLog::insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company_imported_users_log');
    }

}
