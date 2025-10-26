<?php

class LetterOfAwardClauseTableSeeder extends Seeder {

    public function run()
    {
        $letterOfAward = \PCK\LetterOfAward\LetterOfAward::where('is_template', true)->first();

        $letterOfAwardClasue = new \PCK\LetterOfAward\LetterOfAwardClause;
        $letterOfAwardClasue->letter_of_award_id = $letterOfAward ->id;
        $letterOfAwardClasue->sequence_number = 1;
        $letterOfAwardClasue->save();
    }

}

