<?php

use PCK\Users\User;

class UsersTableSeeder extends Seeder {

	public function run()
	{
		$user                        = new User;
		$user->name                  = 'John Doe';
		$user->contact_number        = '0172528042';
		$user->username              = 'johndoe';
		$user->email                 = 'admin@buildspace.com';
		$user->password              = 'qweasdzxc123';
		$user->password_confirmation = 'qweasdzxc123';
		$user->confirmed             = true;
		$user->confirmation_code     = md5($user->username . time());
		$user->is_super_admin        = true;
        $user->allow_access_to_buildspace = true;

		if ( !$user->save() )
		{
			return Log::info('Unable to create user ' . $user->username, (array) $user->errors());
		}
	}

}