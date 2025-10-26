<?php

use PCK\LetterOfAward\LetterOfAward;

class LetterOfAwardTemplateNameTableSeeder extends Seeder {

	public function run()
	{
		$letterOfAwardTemplates = LetterOfAward::whereNull('project_id')->where('is_template', true)->get();

		foreach($letterOfAwardTemplates as $template)
		{
			$templateDetail = DB::table('letter_of_award_template_details')->where('letter_of_award_id', $template->id)->first();

			if(is_null($templateDetail)) continue;

			$template->name = $templateDetail->name;
			$template->save();
		}
	}

	public function rollback()
	{
		$letterOfAwardTemplates = LetterOfAward::whereNull('project_id')->where('is_template', true)->get();

		foreach($letterOfAwardTemplates as $template)
		{
			DB::table('letter_of_award_template_details')->insert(array(
				'letter_of_award_id' => $template->id,
				'name'				 => $template->name,
				'created_at'		 => Carbon\Carbon::now(),
				'updated_at'		 => Carbon\Carbon::now(),
			));
		}
	}
}