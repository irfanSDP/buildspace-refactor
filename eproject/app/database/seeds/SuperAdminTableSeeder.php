<?php

use PCK\Users\User;

class SuperAdminTableSeeder extends Seeder {

    public function run()
    {
        $newAdmins = array();

        if( ! empty( getenv('super_admin_list') ) ) $newAdmins = ( explode(',', getenv('super_admin_list')) );

        foreach($newAdmins as $email)
        {
            $this->addSuperAdmin($email);
        }
    }

    private function addSuperAdmin($email)
    {
        $password = str_random(8);

        $user                             = new User;
        $user->name                       = $email;
        $user->contact_number             = str_random(8);
        $user->username                   = $email;
        $user->email                      = $email;
        $user->password                   = $password;
        $user->password_confirmation      = $password;
        $user->confirmed                  = false;
        $user->confirmation_code          = md5($user->username . time());
        $user->is_super_admin             = true;
        $user->allow_access_to_buildspace = true;

        if( ! $user->save() )
        {
            echo "Error when creating Super Admin user for {$email}" . PHP_EOL;

            return Log::info('Unable to create user ' . $user->username, (array)$user->errors());
        }

        \Event::fire('user.newlyRegistered', $user);

        echo "Super Admin user created for {$email}" . PHP_EOL;

        return true;
    }

}