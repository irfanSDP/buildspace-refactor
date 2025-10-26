<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Companies\Company;
use PCK\CIDBGrades\CIDBGrade;

class UpdateOldCidbGradeIdInCompaniesToLatestId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		$record              = new CIDBGrade();
		$record->grade       = 'Not Applicable';
		$record->save();

		$cidbGrades = CIDBGrade::all();
		$oldCidbGrades = Company::getCIDBGradeDescriptions();
	
		foreach($oldCidbGrades as $key => $value)
		{
			
			$cidbGrade = CIDBGrade::where("grade", $value)->first();
	
			if($cidbGrade)
			{
				Company::where("cidb_grade", $key)->update(["cidb_grade" => $cidbGrade->id]);
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		
		$cidbGrades = CIDBGrade::all();
		$oldCidbGrades = Company::getCIDBGradeDescriptions();
	
		foreach($oldCidbGrades as $key => $value)
		{
			
			$cidbGrade = CIDBGrade::where("grade", $value)->first();
	
			if($cidbGrade)
			{
				Company::where("cidb_grade", $cidbGrade->id)->update(["cidb_grade" => $key]);
			}
		}

		$record = CIDBGrade::where('grade','Not Applicable')->first();
        $record->delete();

	}

}
