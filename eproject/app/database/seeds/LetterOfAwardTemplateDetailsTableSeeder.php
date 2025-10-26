<?php

use PCK\LetterOfAward\LetterOfAward;

class LetterOfAwardTemplateDetailsTableSeeder extends Seeder {

	public function run()
	{
		$letterOfAwardTemplates = LetterOfAward::whereNull('project_id')->where('is_template', true)->get();

		foreach($letterOfAwardTemplates as $template)
		{
			$templateDetail = DB::table('letter_of_award_template_details')->where('letter_of_award_id', $template->id)->first();

			if($templateDetail) continue;

			DB::table('letter_of_award_template_details')->insert(array(
				'letter_of_award_id' => $template->id,
				'name'				 => LetterOfAward::DEFAULT_NAME,
				'created_at'		 => Carbon\Carbon::now(),
				'updated_at'		 => Carbon\Carbon::now(),
			));
		}
	}

}