<?php

use PCK\LetterOfAward\LetterOfAward;
use PCK\Projects\Project;
class LetterOfAwardExistingProjectsTableSeeder extends Seeder
{
	public function run()
	{
		$letterOfAwardRepository = \App::make('PCK\LetterOfAward\LetterOfAwardRepository');
		$letterOfAwardTemplate = LetterOfAward::where('is_template', true)->orderBy('id', 'ASC')->first();

		foreach(Project::all() as $project)
		{
			if($project->letterOfAward) continue;

			$letterOfAwardRepository->createEntry($project, $letterOfAwardTemplate->id);
		}
	}
}