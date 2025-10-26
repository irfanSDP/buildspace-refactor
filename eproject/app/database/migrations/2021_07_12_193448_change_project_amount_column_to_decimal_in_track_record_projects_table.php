<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProjectAmountColumnToDecimalInTrackRecordProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->renameColumn('project_amount', 'project_amount_remarks');
		});

		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->decimal('project_amount', 19, 2)->default(0);
			$table->unsignedInteger('country_id')->nullable();

			$table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
		});

		DB::statement('ALTER TABLE track_record_projects ALTER COLUMN project_amount_remarks DROP NOT NULL');

		$this->seed();

		DB::statement('ALTER TABLE track_record_projects ALTER COLUMN country_id SET NOT NULL');

		if(empty($this->notComputed))
		{
			print_r('All records computed');
			print_r(PHP_EOL);
		}
		else
		{
			print_r('Records without a recognisable currency:');
			print_r(PHP_EOL);
			print_r($this->notComputed);
			print_r(PHP_EOL);
		}
	}

	protected $notComputed = [];
	protected $countryIds = [];

	protected function seed()
	{
		$this->loadCountryIds();

		$records = \PCK\TrackRecordProject\TrackRecordProject::all();

		foreach($records as $record)
		{
			$info = $this->compute($record->project_amount_remarks);

			if(is_null($info['country_id']))
			{
				$this->notComputed[] = $record->id;

				$info['country_id'] = $this->countryIds['MY'];
			}

			DB::statement('UPDATE track_record_projects SET project_amount = ?, country_id = ? WHERE id = ?', [$info['project_amount'], $info['country_id'], $record->id]);
		}
	}

	public function loadCountryIds()
	{
		$this->countryIds['MY'] = \PCK\Countries\Country::where('iso', '=', 'MY')->first()->id;
		$this->countryIds['US'] = \PCK\Countries\Country::where('iso', '=', 'US')->first()->id;
		$this->countryIds['SG'] = \PCK\Countries\Country::where('iso', '=', 'SG')->first()->id;
		$this->countryIds['FR'] = \PCK\Countries\Country::where('iso', '=', 'FR')->first()->id;
		$this->countryIds['GB'] = \PCK\Countries\Country::where('iso', '=', 'GB')->first()->id;
		$this->countryIds['AU'] = \PCK\Countries\Country::where('iso', '=', 'AU')->first()->id;
		$this->countryIds['PH'] = \PCK\Countries\Country::where('iso', '=', 'PH')->first()->id;
		$this->countryIds['HK'] = \PCK\Countries\Country::where('iso', '=', 'HK')->first()->id;
		$this->countryIds['TH'] = \PCK\Countries\Country::where('iso', '=', 'TH')->first()->id;
		$this->countryIds['ID'] = \PCK\Countries\Country::where('iso', '=', 'ID')->first()->id;
		$this->countryIds['AE'] = \PCK\Countries\Country::where('iso', '=', 'AE')->first()->id;
		$this->countryIds['QA'] = \PCK\Countries\Country::where('iso', '=', 'QA')->first()->id;
		$this->countryIds['MZ'] = \PCK\Countries\Country::where('iso', '=', 'MZ')->first()->id;
		$this->countryIds['IN'] = \PCK\Countries\Country::where('iso', '=', 'IN')->first()->id;
		$this->countryIds['VN'] = \PCK\Countries\Country::where('iso', '=', 'VN')->first()->id;
		$this->countryIds['SD'] = \PCK\Countries\Country::where('iso', '=', 'SD')->first()->id;
		$this->countryIds['SA'] = \PCK\Countries\Country::where('iso', '=', 'SA')->first()->id;
		$this->countryIds['BN'] = \PCK\Countries\Country::where('iso', '=', 'BN')->first()->id;
		$this->countryIds['SE'] = \PCK\Countries\Country::where('iso', '=', 'SE')->first()->id;
		$this->countryIds['BD'] = \PCK\Countries\Country::where('iso', '=', 'BD')->first()->id;
		$this->countryIds['LK'] = \PCK\Countries\Country::where('iso', '=', 'LK')->first()->id;
		$this->countryIds['MV'] = \PCK\Countries\Country::where('iso', '=', 'MV')->first()->id;
	}

	public function compute($string)
	{
		$string = trim($string);

		$countryId = null;

	    if(preg_match('/^(myr|rm)/i', $string))
	    {
	    	$countryId = $this->countryIds['MY'];
	    }
	    elseif(preg_match('/^usd/i', $string))
	    {
	    	$countryId = $this->countryIds['US'];
	    }
	    elseif(preg_match('/^sgd/i', $string))
	    {
	    	$countryId = $this->countryIds['SG'];
	    }
	    elseif(preg_match('/^eur/i', $string))
	    {
	    	$countryId = $this->countryIds['FR'];
	    }
	    elseif(preg_match('/^gbp/i', $string))
	    {
	    	$countryId = $this->countryIds['GB'];
	    }
	    elseif(preg_match('/^aud/i', $string))
	    {
	    	$countryId = $this->countryIds['AU'];
	    }
	    elseif(preg_match('/^php/i', $string))
	    {
	    	$countryId = $this->countryIds['PH'];
	    }
	    elseif(preg_match('/^thb/i', $string))
	    {
	    	$countryId = $this->countryIds['TH'];
	    }
	    elseif(preg_match('/^idr/i', $string))
	    {
	    	$countryId = $this->countryIds['ID'];
	    }
	    elseif(preg_match('/^hkd/i', $string))
	    {
	    	$countryId = $this->countryIds['HK'];
	    }
	    elseif(preg_match('/^aed/i', $string))
	    {
	    	$countryId = $this->countryIds['AE'];
	    }
	    elseif(preg_match('/^qar/i', $string))
	    {
	    	$countryId = $this->countryIds['QA'];
	    }
	    elseif(preg_match('/^mzm/i', $string))
	    {
	    	$countryId = $this->countryIds['MZ'];
	    }
	    elseif(preg_match('/^inr/i', $string))
	    {
	    	$countryId = $this->countryIds['IN'];
	    }
	    elseif(preg_match('/^vnd/i', $string))
	    {
	    	$countryId = $this->countryIds['VN'];
	    }
	    elseif(preg_match('/^sdd/i', $string))
	    {
	    	$countryId = $this->countryIds['SD'];
	    }
	    elseif(preg_match('/^sar/i', $string))
	    {
	    	$countryId = $this->countryIds['SA'];
	    }
	    elseif(preg_match('/^bnd/i', $string))
	    {
	    	$countryId = $this->countryIds['BN'];
	    }
	    elseif(preg_match('/^sek/i', $string))
	    {
	    	$countryId = $this->countryIds['SE'];
	    }
	    elseif(preg_match('/^bdt/i', $string))
	    {
	    	$countryId = $this->countryIds['BD'];
	    }
	    elseif(preg_match('/^lkr/i', $string))
	    {
	    	$countryId = $this->countryIds['LK'];
	    }
	    elseif(preg_match('/^mvr/i', $string))
	    {
	    	$countryId = $this->countryIds['MV'];
	    }
	    elseif(preg_match('/^\D/i', $string))
	    {
	        // currency not included;
	    }
	    else
	    {
	    	$countryId = $this->countryIds['MY'];
	    }

	    $amount = preg_replace('/[^0-9\.]/', '', $string);

	    $amount = floatval($amount);

	    if(preg_match('/((mil(lion)?)|m)$/i', $string))
	    {
	        $amount *= 1000000;
	    }
	    if(preg_match('/bil(lion)?$/i', $string))
	    {
	        $amount *= 1000000000;
	    }

	    return ['project_amount' => $amount, 'country_id' => $countryId];
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE track_record_projects ALTER COLUMN project_amount_remarks SET NOT NULL');

		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->dropColumn('country_id');
			$table->dropColumn('project_amount');
		});

		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->renameColumn('project_amount_remarks', 'project_amount');
		});
	}

}
