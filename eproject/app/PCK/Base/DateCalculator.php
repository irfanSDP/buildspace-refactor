<?php namespace PCK\Base;

use Carbon\Carbon;

class DateCalculator {

	public function calculateDaysBySum($date, $deadlineDays = 0)
	{
		$startDate = $this->createDateObject($date);

		return $startDate->addDays($deadlineDays)->format(\Config::get('dates.submission_date_formatting'));
	}

	/**
	 * @param $date
	 * @param $daysToComplyFromToday
	 * @return array
	 */
	public function getAffectedDaysFromInputDate($date, $daysToComplyFromToday)
	{
		$dates       = array();
		$currentDate = Carbon::parse($date);

		$dates[] = $currentDate->toDateString();

		for ($days = 1; $days <= $daysToComplyFromToday; $days++)
		{
			$dates[] = $currentDate->addDay()->toDateString();
		}

		return $dates;
	}

	/**
	 * @param $daysToComplyFromToday
	 * @return array
	 */
	public function calculateDaysFromToday($daysToComplyFromToday)
	{
		$dates       = array();
		$currentDate = Carbon::now();

		$dates[] = $this->addDatePickerFormattedDate($currentDate->toDateString());

		for ($days = 1; $days <= $daysToComplyFromToday; $days++)
		{
			$dates[] = $this->addDatePickerFormattedDate($currentDate->addDay()->toDateString());
		}

		return $dates;
	}

	/**
	 * we will calculate the dates between Start Date and End Date the days between this 2 dates
	 * and then be looped to get the dates between them so that the Date Picker in front-end
	 * can block correctly
	 *
	 * @param $startDate
	 * @param $endDate
	 * @return array
	 */
	public function generateDisableDays($startDate, $endDate)
	{
		$dates = array();

		$startDate  = $this->createDateObject($startDate);
		$endDate    = $this->createDateObject($endDate);
		$diffInDays = $this->diffInDays($startDate, $endDate);

		$dates[] = $this->addDatePickerFormattedDate($startDate->toDateString());

		for ($days = 1; $days <= $diffInDays; $days++)
		{
			$dates[] = $this->addDatePickerFormattedDate($startDate->addDay()->toDateString());
		}

		$dates[] = $this->addDatePickerFormattedDate($endDate->toDateString());

		return $dates;
	}

	public function addDatePickerFormattedDate($date)
	{
		return date('n-j-Y', strtotime($date));
	}

	/**
	 * @param $date
	 * @return static
	 */
	private function createDateObject($date)
	{
		return Carbon::parse($date);
	}

	/**
	 * @param Carbon $startDate
	 * @param Carbon $endDate
	 * @return int
	 */
	private function diffInDays(Carbon $startDate, Carbon $endDate)
	{
		return $startDate->diffInDays($endDate);
	}

}