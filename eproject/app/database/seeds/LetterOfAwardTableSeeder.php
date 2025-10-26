<?php

class LetterOfAwardTableSeeder extends Seeder {

    public function run()
    {
        $letterOfAward = new \PCK\LetterOfAward\LetterOfAward;
        $letterOfAward->is_template = true;
        $letterOfAward->save();
    }

}