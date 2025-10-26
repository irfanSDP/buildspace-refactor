<?php

use PCK\Settings\Settings;
use PCK\Users\User;

class UserSettingsTableSeeder extends Seeder {

    public function run()
    {
        foreach(User::all() as $user)
        {
            if( ! $user->settings ) Settings::initialise($user);
        }
    }

}