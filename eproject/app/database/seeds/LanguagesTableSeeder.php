<?php

use PCK\Settings\Language;

class LanguagesTableSeeder extends Seeder {

    public function run()
    {
        foreach(Language::getLanguageListing() as $languageCode => $languageName)
        {
            Language::firstOrCreate(array( 'name' => $languageName, 'code' => $languageCode ));
        }
    }

}