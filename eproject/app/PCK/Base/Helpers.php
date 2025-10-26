<?php namespace PCK\Base;

use PCK\Projects\Project;
use Symfony\Component\HttpFoundation\File\File;
use Carbon\Carbon;

class Helpers {

	public static function replaceUnderscoreWithSpaces($input)
	{
		foreach($input as $key => $value)
		{
			if(strpos($key, "_"))
			{
				$newKey = str_replace('_', ' ', $key);
				unset($input[$key]);
				$input[$newKey] = $value;
			}
		}

		return $input;
	}

	public static function processDateFormat($date)
	{
		return date("d-m-Y", strtotime($date));
	}

	public static function processInput($input)
	{
		foreach($input as $key => $value)
		{
			if($input[$key] == "")
			{
				$input[$key] = NULL;
			}

			if($key == "_method" || $key == "_token" || $key == "Submit" || $key == "Save" || $key == "form_type" || $key == "files")
			{
				unset($input[$key]);
			}

			if(strpos($key, "date") || $key == "date")
			{
				if(isset($input[$key]))
				{
					$input[$key] = date("Y-m-d", strtotime($input[$key]));
				}
			}
		}

		return $input;
	}

	public static function getDaysPending($object)
	{
		$now    = Carbon::now();
        $then   = Carbon::parse($object->updated_at);

		return $then->diffInDays($now);
	}

	public static function convertDateToDay($date)
	{
		$dt = strtotime($date);
		$day = date("l", $dt);

		return $day;
	}

	public static function generateTimeArray()
	{
		$hoursArray = array();

		for ( $hours = 0; $hours < 24; $hours ++ )
		{
			$value = str_pad($hours, 2, '0', STR_PAD_LEFT) . ':00';

			$hoursArray[$value] = $value;
		}

		return $hoursArray;
	}

	public static function generateTimeArrayInMinutes()
	{
		$times = array();
		for ($h = 0; $h < 24; $h++){
			for ($m = 0; $m < 60 ; $m += 5){
				$time = sprintf('%02d:%02d', $h, $m);
				$times["$time"] = $time;
			}
		}

		return $times;
	}

	public static function formatBytes($bytes, $force_unit = null, $format = null, $si = true)
	{
		// Format string
		$format = ( $format === null ) ? '%01.2f %s' : (string) $format;

		// IEC prefixes (binary)
		if ( $si === false OR strpos($force_unit, 'i') !== false )
		{
			$units = array( 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' );
			$mod   = 1024;
		}
		// SI prefixes (decimal)
		else
		{
			$units = array( 'B', 'kB', 'MB', 'GB', 'TB', 'PB' );
			$mod   = 1000;
		}

		// Determine unit to use
		if ( ( $power = array_search((string) $force_unit, $units) ) === false )
		{
			$power = ( $bytes > 0 ) ? floor(log($bytes, $mod)) : 0;
		}

		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}

	public static function generateTabLink($id, $formTabName)
	{
		return '#' . str_replace('%id%', $id, $formTabName);
	}

	/*
	 * ----------------------------------
	 * update batch
	 * ----------------------------------
	 *
	 * multiple update in one query
	 *
	 * tableName( required | string )
	 * multipleData ( required | array of array )
	 */
	public static function updateBatch($tableName, array $multipleData)
	{
		if ( $tableName && !empty( $multipleData ) )
		{
			// column or fields to update
			$updateColumn    = array_keys($multipleData[0]);
			$referenceColumn = $updateColumn[0]; //e.g id

			unset( $updateColumn[0] );

			$whereIn = '';

			$q = 'UPDATE ' . $tableName . ' SET ';

			foreach ( $updateColumn as $uColumn )
			{
				$q .= $uColumn . ' = CASE ';

				foreach ( $multipleData as $data )
				{
					$q .= 'WHEN ' . $referenceColumn . ' = ' . $data[$referenceColumn] . " THEN '" . $data[$uColumn] . "' ";
				}

				$q .= 'ELSE ' . $uColumn . ' END, ';
			}

			foreach ( $multipleData as $data )
			{
				$whereIn .= "'" . $data[$referenceColumn] . "', ";
			}

			$q = rtrim($q, ', ') . ' WHERE ' . $referenceColumn . ' IN (' . rtrim($whereIn, ', ') . ')';

			// Update
			return \DB::update(\DB::raw($q));
		}

		return false;
	}

	public static function deleteDir($dirPath)
	{
		if ( is_dir($dirPath) )
		{
			array_map('PCK\Base\Helpers::deleteDir', array_diff(glob("$dirPath/{,.}*", GLOB_BRACE), array( "$dirPath/.", "$dirPath/.." )));

			rmdir($dirPath);
		}
		else
		{
			unlink($dirPath);
		}
	}

	public static function archivedFile(File $file, Project $project, $filename)
	{
		$destinationPath = storage_path().'/archived/project-'.$project->id;
		if(!\File::exists($destinationPath))
		{
			\File::makeDirectory($destinationPath, 0775, true);
		}

		\File::copy($file, $destinationPath.'/'.$filename.'-'.date('YmdHis').'.'.$file->getExtension());
	}

	public static function getTimeFrom($startTime, $howMany, $unit)
	{
		switch($unit)
		{
			case 'days':
				$timeFromStartTime = $startTime->addDays($howMany);
				break;
			case 'weeks':
				$timeFromStartTime = $startTime->addWeeks($howMany);
				break;
			case 'months':
				$timeFromStartTime = $startTime->addMonths($howMany);
				break;
			default:
				throw new \Exception("Invalid time unit");
		}

		return $timeFromStartTime;
	}

	public static function getTimeBefore($startTime, $howMany, $unit)
	{
		switch($unit)
		{
            case 'day':
			case 'days':
            case 'day(s)':
				$timeBeforeStartTime = $startTime->subDays($howMany);
				break;

            case 'week':
			case 'weeks':
            case 'week(s)':
				$timeBeforeStartTime = $startTime->subWeeks($howMany);
				break;

            case 'month':
			case 'months':
            case 'month(s)':
				$timeBeforeStartTime = $startTime->subMonths($howMany);
				break;

            case 'year':
            case 'years':
            case 'year(s)':
                $timeBeforeStartTime = $startTime->subYears($howMany);
                break;

			default:
				throw new \Exception("Invalid time unit");
		}

		return $timeBeforeStartTime;
	}

	public static function getTimeFromNow($howMany, $unit)
	{
		$now = \Carbon\Carbon::now();

		return self::getTimeFrom($now, $howMany, $unit);
	}

	public static function getTimeBeforeNow($howMany, $unit)
	{
		$now = \Carbon\Carbon::now();

		return self::getTimeBefore($now, $howMany, $unit);
	}

	public static function getYearMonthDayDiffString(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
	{
		$difference = $startDate->diff($endDate);

		$years  = $difference->y;
		$months = $difference->m;
		$days   = $difference->d;

		$format = "";

		if($years != 0)
		{
			$format .= $years . " " . trans('general.years') . ", ";
		}

		if($months != 0)
		{
			$format .= $months . " " . trans('general.months') . ", ";
		}

		if($days != 0)
		{
			$format .= $days . " " . trans('general.days') . " ";
		}

		return $format;
	}

    public static function divide($dividend, $divisor)
    {
        if( $divisor == 0 ) return 0;

        return $dividend / $divisor;
    }

    public static function arrayBatch($arr, $batchSize, $closure)
    {
        $batch = [];
        foreach($arr as $i)
        {
            $batch[] = $i;
            // See if we have the right amount in the batch
            if(count($batch) === $batchSize)
            {
                // Pass the batch into the Closure
                $closure($batch);
                // Reset the batch
                $batch = [];
            }
        }
        // See if we have any leftover ids to process
        if(count($batch)) $closure($batch);
    }
}