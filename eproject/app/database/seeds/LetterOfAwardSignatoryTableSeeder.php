<?php

class LetterOfAwardSignatoryTableSeeder extends Seeder {

    public function run()
    {
        $letterOfAward = \PCK\LetterOfAward\LetterOfAward::where('is_template', true)->first();

        $letterOfAwardSignatory = new \PCK\LetterOfAward\LetterOfAwardSignatory;
        $letterOfAwardSignatory->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardSignatory->save();
    }
}

