<?php

class LetterOfAwardPrintSettingsTableSeeder extends Seeder {

    public function run()
    {
        $letterOfAward = \PCK\LetterOfAward\LetterOfAward::where('is_template', true)->first();

        $letterOfAwardPrintSettings = new \PCK\LetterOfAward\LetterOfAwardPrintSetting;
        $letterOfAwardPrintSettings->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardPrintSettings->save();
    }
}

