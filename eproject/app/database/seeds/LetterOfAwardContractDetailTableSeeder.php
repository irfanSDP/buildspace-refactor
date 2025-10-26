<?php

class LetterOfAwardContractDetailTableSeeder extends Seeder {

    public function run()
    {
        $letterOfAward = \PCK\LetterOfAward\LetterOfAward::where('is_template', true)->first();

        $letterOfAwardContractDetail = new \PCK\LetterOfAward\LetterOfAwardContractDetail;
        $letterOfAwardContractDetail->letter_of_award_id = $letterOfAward ->id;
        $letterOfAwardContractDetail->save();
    }

}

