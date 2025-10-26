<?php namespace PCK\Commands;

class UpdateLanguageListService {

    public function handle()
    {
        $seeder = new \LanguagesTableSeeder;

        $seeder->run();
    }

}