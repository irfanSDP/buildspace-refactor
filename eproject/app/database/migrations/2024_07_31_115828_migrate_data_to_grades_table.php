<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\CIDBGrades\CIDBGrade;
use PCK\Companies\Company;

class MigrateDataToGradesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$cidbGrades = Company::getCIDBGradeDescriptions();
		
		foreach($cidbGrades as $cidbGrade){
			if($cidbGrade == 'Not Applicable'){
				continue;
			}
			$record              = new CIDBGrade();
            $record->grade       = $cidbGrade;
            $record->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$cidbGrades = Company::getCIDBGradeDescriptions();
		
		foreach($cidbGrades as $cidbGrade){
			if($cidbGrade == 'Not Applicable'){
				continue;
			}			
			$record = CIDBGrade::where('grade',$cidbGrade)->first();
            $record->delete();
		}
	}

}
