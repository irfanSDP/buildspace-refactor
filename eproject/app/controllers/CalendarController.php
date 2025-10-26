<?php

use PCK\Countries\Country;
use PCK\Forms\CalendarForm;
use PCK\Calendars\Calendar;
use PCK\CalendarSettings\CalendarSetting;

class CalendarController extends BaseController {

    private $calendarForm;

    public function __construct(CalendarForm $calendarForm)
    {
        $this->calendarForm = $calendarForm;
    }

    public function index()
    {
        return View::make('calendar.index');
    }

    public function listEvents()
    {
        $events = array();

        $defaultCountry = CalendarSetting::select('country_id AS id')->first();

        $defaultCountry = ( $defaultCountry ) ? $defaultCountry->toArray() : Country::select('id')->first()->toArray();

        $countryId = Input::get('countryId') ?: $defaultCountry['id'];
        $stateId   = Input::get('stateId');
        $eventType = Input::get('eventType');

        $query = DB::table('calendars')
            ->leftJoin('countries', 'calendars.country_id', '=', 'countries.id')
            ->leftJoin('states', 'calendars.state_id', '=', 'states.id')
            ->select('calendars.id', 'calendars.name', 'calendars.description', 'calendars.start_date', 'calendars.end_date',
                'calendars.event_type', 'calendars.state_id', 'countries.country AS country_name', 'states.name AS state_name');

        if( $countryId )
        {
            $query->where('calendars.country_id', $countryId);
        }

        if( $stateId )
        {
            $query->where('calendars.state_id', $stateId);
        }

        if( $eventType )
        {
            $query->where('calendars.event_type', $eventType);
        }

        $results = $query->orderBy('start_date', 'asc')->get();

        $count = 0;

        foreach($results as $result)
        {
            $events[] = array(
                ++$count,
                $result->name,
                $result->description,
                Calendar::getEventTypeText($result->event_type),
                trim($result->country_name),
                trim($result->state_name),
                date('d M Y', strtotime($result->start_date)),
                date('d M Y', strtotime($result->end_date))
            );
        }

        return Response::json(array(
            'draw'            => 2,
            'recordsTotal'    => count($events),
            'recordsFiltered' => count($events),
            'data'            => $events
        ));
    }

    public function getEvents()
    {
        $events = array();

        $defaultCountry = CalendarSetting::select('country_id AS id')->first();

        $defaultCountry = ( $defaultCountry ) ? $defaultCountry->toArray() : Country::select('id')->first()->toArray();

        $countryId = Input::get('countryId') ?: $defaultCountry['id'];
        $stateId   = Input::get('stateId');
        $eventType = Input::get('eventType');

        $dateFrom = ( Input::get('start') ) ? date('Y-m-d', strtotime(Input::get('start'))) : null;
        $dateTo   = ( Input::get('end') ) ? date('Y-m-d', strtotime(Input::get('end'))) : null;

        $query = Calendar::select(
            'id', 'name', 'description', 'start_date', 'end_date',
            'event_type', 'country_id', 'state_id');

        if( $dateFrom && $dateTo )
        {
            $query->whereBetween('start_date', array( $dateFrom, $dateTo ));
        }

        if( $countryId )
        {
            $query->where('country_id', $countryId);
        }

        if( $stateId )
        {
            $query->where('state_id', $stateId);
        }

        if( $eventType )
        {
            $query->where('event_type', $eventType);
        }

        $results = $query->get();

        foreach($results as $result)
        {
            $events[] = array(
                'id'          => $result['id'],
                'title'       => $result['name'],
                'description' => $result['description'],
                'start'       => date('D M d Y H:i:s O', strtotime($result['start_date'])),
                'end'         => date('D M d Y H:i:s O', strtotime($result['end_date'])),
                'eventType'   => $result['event_type'],
                'countryId'   => $result['country_id'],
                'stateId'     => $result['state_id'],
                'className'   => Calendar::getEventColor($result['event_type']),
                'allday'      => true
            );
        }

        return Response::json($events);
    }

    public function setDefaultCountry($countryId)
    {
        $msg = null;

        try
        {
            $setting             = CalendarSetting::all()->first();
            $setting             = $setting ?: new CalendarSetting();
            $setting->country_id = $countryId;

            $setting->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();

            $success = false;
        }

        return Response::json(compact('success', 'msg'));
    }

    public function update()
    {
        $input = Input::all();
        $msg   = null;

        $event              = Calendar::findOrNew($input['id']);
        $event->name        = $input['name'];
        $event->description = $input['description'];
        $event->end_date    = date('Y-m-d', strtotime($input['end_date']));
        $event->start_date  = date('Y-m-d', strtotime($input['start_date']));
        $event->event_type  = $input['event_type'];
        $event->state_id    = ( $input['state_id'] > 0 ) ? $input['state_id'] : null;
        $event->country_id  = $input['country_id'];

        try
        {
            $event->save();

            $success = true;

        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();

            $success = false;
        }

        return Response::json(array(
            'success' => $success,
            'msg'     => $msg,
            'data'    => $event->toArray(),
        ));
    }

    public function delete($id)
    {
        $msg     = null;
        $success = true;

        try
        {
            $record = Calendar::findOrFail($id);

            $record->delete();
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();

            $success = false;
        }

        return Response::json(compact('success', 'msg', 'id'));
    }

}