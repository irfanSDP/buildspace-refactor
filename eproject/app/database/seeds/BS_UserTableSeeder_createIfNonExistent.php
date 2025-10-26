<?php

class BS_UserTableSeeder_createIfNonExistent extends Seeder {

    public function run()
    {
        foreach(\PCK\Users\User::all() as $user)
        {
            if( ! $user->getBsUser() ) $user->createBsUser();
        }
    }
}