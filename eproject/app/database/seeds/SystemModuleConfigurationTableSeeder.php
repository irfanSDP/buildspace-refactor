<?php

use PCK\SystemModules\SystemModuleConfiguration;

class SystemModuleConfigurationTableSeeder extends Seeder {

    public function run()
    {
        SystemModuleConfiguration::initiateModules();
    }

}