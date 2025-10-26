<?php namespace PCK\Calendars;

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\Base\DateCalculator;

class CalendarRepository {

	private $calendar;

	private $dateCalculator;

	public function __construct(Calendar $calendar, DateCalculator $dateCalculator)
	{
		$this->calendar       = $calendar;
		$this->dateCalculator = $dateCalculator;
	}

	// generate list of dates to sync with front-end's date picker
	public function getEventsListing(Project $project, $daysToComplyFromToday = 0)
	{
		// will be separating in two parts first, first will get all the available holidays by country,
		// then only add additional dates and get additional days in between holidays if available
		$dates = $this->getEventsByProject($project);

		$startsAt       = Carbon::now();
		$startDate      = $startsAt->toDateString();
		$endDate        = $startsAt->addDays($daysToComplyFromToday)->toDateString();
		$additionalDays = count(array_unique($this->getEventsByProject($project, $startDate, $endDate)));
		$additionalDays = $this->getAdditionalDaysFromEndDate($project, $startDate, $daysToComplyFromToday, $additionalDays);

		// first we will calculate $daysToComplyFromToday
		if ( $daysToComplyFromToday > 0 or $additionalDays > 0 )
		{
			$returnDates = $this->dateCalculator->calculateDaysFromToday($additionalDays + $daysToComplyFromToday);

			foreach ( $returnDates as $date )
			{
				$dates[] = $date;
			}

			unset( $returnDates );
		}

		return array_unique($dates);
	}

	public function calculateFinalDate(Project $project, $startDate, $daysToComplyFromToday = 0)
	{
		$endDate = null;

		// first we will calculate $daysToComplyFromToday
		if ( $daysToComplyFromToday > 0 )
		{
			$returnDates = $this->dateCalculator->getAffectedDaysFromInputDate($startDate, $daysToComplyFromToday);
			$endDate     = end($returnDates);

			unset( $returnDates );
		}

		$cleanedUpDates      = array_unique($this->getEventsByProject($project, $startDate, $endDate));
		$cleanedUpDatesCount = count($cleanedUpDates);

		$cleanedUpDatesCount = $this->getAdditionalDaysFromEndDate($project, $startDate, $daysToComplyFromToday, $cleanedUpDatesCount);

		return $this->dateCalculator->calculateDaysBySum($startDate, $cleanedUpDatesCount + $daysToComplyFromToday);
	}

	private function getEventsByProject(Project $project, $startDate = null, $endDate = null)
	{
		$dates = array();
		$query = $this->calendar->where('calendars.country_id', $project->country_id);

		if ( $startDate and $endDate )
		{
			$queryStartDate = date('Y-m-d', strtotime($startDate));
			$queryEndDate   = date('Y-m-d', strtotime($endDate));

			$query->whereBetween('start_date', array( $queryStartDate, $queryEndDate ));
			$query->orWhereBetween('end_date', array( $queryStartDate, $queryEndDate ));
		}

		$results = $query->orderBy('start_date', 'asc')
			->get([
				'calendars.id', 'calendars.state_id', 'calendars.start_date', 'calendars.end_date', 'calendars.event_type'
			]);

		// will calculate event took how many days
		foreach ( $results as $result )
		{
			// filter out state's holiday that current the project is not in
			if ( $result->event_type == Calendar::EVENT_TYPE_STATE and $result->state_id != $project->state_id )
			{
				continue;
			}

			$returnDates = $this->dateCalculator->generateDisableDays($result['start_date'], $result['end_date']);

			foreach ( $returnDates as $date )
			{
				$dates[] = $date;
			}

			unset( $returnDates );
		}

		return $dates;
	}

	private function getAdditionalDaysFromEndDate(Project $project, $startDate, $daysToComplyFromToday, $cleanedUpDatesCount)
	{
		$thereIsNoAdditionalDate = false;

		// calculate the final dates with holidays applied
		$preFinalDate = $this->dateCalculator->calculateDaysBySum($startDate, $cleanedUpDatesCount + $daysToComplyFromToday);
		$newStartDate = $preFinalDate;
		$newEndDate   = $preFinalDate;

		// will query again using the final date as start date to get the correct final date.
		// use while loop to check additional event, if no then break out of the loop, if got then
		// continue until there is none holiday(s) left
		while ($thereIsNoAdditionalDate !== true)
		{
			$additionalFinalDays = array_unique($this->getEventsByProject($project, $newStartDate, $newEndDate));

			if ( empty( $additionalFinalDays ) )
			{
				$thereIsNoAdditionalDate = true;
			}
			else
			{
				$cleanedUpDatesCount += count($additionalFinalDays);

				$preFinalDate = $this->dateCalculator->calculateDaysBySum($startDate, $cleanedUpDatesCount + $daysToComplyFromToday);
				$newStartDate = $preFinalDate;
				$newEndDate   = $preFinalDate;
			}
		}

		return $cleanedUpDatesCount;
	}

}