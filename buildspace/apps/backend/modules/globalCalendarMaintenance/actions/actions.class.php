<?php

/**
 * globalCalendarMaintenance actions.
 *
 * @package    buildspace
 * @subpackage globalCalendarMaintenance
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class globalCalendarMaintenanceActions extends BaseActions {

    public function executeGetAllEvents()
    {
        $events = DoctrineQuery::create()->select('c.id, c.description AS summary, c.is_holiday AS "isHoliday", c.region_id AS regionId, c.subregion_id AS subRegionId,
            c.start_date AS startTime, c.end_date AS endTime, c.event_type AS eventType, c.region_id AS calendar')
            ->from('GlobalCalendar c')
            ->addOrderBy('c.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach ( $events as $k => $event )
        {
            $events[$k]['startTime'] = date("D M d Y h:i:s", strtotime($event['startTime']));
            $events[$k]['endTime']   = date("D M d Y h:i:s", strtotime($event['endTime']));
            $events[$k]['calendar']  = 'calendar_' . $event['calendar'];
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $events
        ));
    }

    public function executeGetCalendarSetting(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $setting = Doctrine_Core::getTable('GlobalCalendarSetting')->find(1);

        try
        {
            $setting = ( !$setting ) ? new GlobalCalendarSetting() : $setting;

            $setting->save();

            $errorMsg = null;

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $setting->toArray()
        ));
    }

    public function executeGetEventsByCountryId(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $country = Doctrine_Core::getTable('Regions')->find($request->getParameter('id'));

        $events = array();

        if ( $country )
        {
            $pdo = $country->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT  c.id, c.description, c.is_holiday, c.region_id, c.subregion_id,
                c.start_date, c.end_date, c.event_type
                FROM " . GlobalCalendarTable::getInstance()->getTableName() . " c
                WHERE c.region_id = " . $country->id);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $results as $event )
            {
                array_push($events, array(
                    'id'          => $event['id'],
                    'summary'     => $event['description'],
                    'regionId'    => $event['region_id'],
                    'subRegionId' => $event['subregion_id'],
                    'isHoliday'   => $event['is_holiday'],
                    'eventType'   => (string)$event['event_type'],
                    'startTime'   => date("D M d Y h:i:s", strtotime($event['start_date']." 00:00:00")),
                    'endTime'     => date("D M d Y h:i:s", strtotime($event['end_date']." 00:01:00")),
                    'calendar'    => 'calendar_' . $event['event_type']
                ));
            }

        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $events
        ));
    }

    public function executeGetEventTypeOptions(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new GlobalCalendarForm();

        return $this->renderJson(array(
            'eventTypeOptions' => array(
                array(
                    'id'    => (string)GlobalCalendar::EVENT_TYPE_PUBLIC,
                    'label' => GlobalCalendar::EVENT_TYPE_PUBLIC_TEXT
                ),
                array(
                    'id'    => (string)GlobalCalendar::EVENT_TYPE_STATE,
                    'label' => GlobalCalendar::EVENT_TYPE_STATE_TEXT
                ),
                array(
                    'id'    => (string)GlobalCalendar::EVENT_TYPE_OTHER,
                    'label' => GlobalCalendar::EVENT_TYPE_OTHER_TEXT
                )
            ),
            'formValues'       => array(
                'global_calendar[_csrf_token]' => $form->getCSRFToken()
            )
        ));
    }

    public function executeEventUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $event = Doctrine_Core::getTable('GlobalCalendar')->find($request->getParameter('id'))
        );
    }

    public function executeEventAdd(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new GlobalCalendarForm(Doctrine_Core::getTable('GlobalCalendar')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $event = $form->save();

            $errors = null;

            $success = true;

            $values = array(
                'id'          => $event->id,
                'summary'     => $event->description,
                'regionId'    => $event->region_id,
                'subRegionId' => $event->subregion_id,
                'eventType'   => (string)$event->event_type,
                'isHoliday'   => $event->is_holiday,
                'startTime'   => date("D M d Y h:i:s", strtotime($event->start_date)),
                'endTime'     => date("D M d Y h:i:s", strtotime($event->end_date)),
                'calendar'    => 'calendar_' . $event->event_type,
                '_csrf_token' => $form->getCSRFToken()
            );
        }
        else
        {
            $errors = $form->getErrors();

            $success = false;

            $values = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values ));
    }

    public function executeSetDefaultCalendar(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $country = Doctrine_Core::getTable('Regions')->find($request->getParameter('countryId'));
        $setting = Doctrine_Core::getTable('GlobalCalendarSetting')->find(1);

        try
        {
            $setting = ( !$setting ) ? new GlobalCalendarSetting() : $setting;

            $setting->default_region_id = ( $country ) ? $country->id : null;

            $setting->save();

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeEventDelete(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $event = Doctrine_Core::getTable('GlobalCalendar')->find($request->getParameter('id'))
        );

        try
        {
            $event->delete();

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

}