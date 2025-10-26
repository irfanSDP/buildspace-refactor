<?php

/**
 * projectManagementCalendar actions.
 *
 * @package    buildspace
 * @subpackage projectManagementCalendar
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectManagementCalendarActions extends BaseActions
{
    public function executeIndex( sfWebRequest $request )
    {

    }

    public function executeGetCalendarSetting ( sfWebRequest $request )
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $setting = Doctrine_Core::getTable('GlobalCalendarSetting')->find(1);

        try
        {
            $setting = (!$setting) ? new GlobalCalendarSetting() : $setting;

            $setting->save();

            $errorMsg = null;

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success = false;
        }

        return $this->renderJson(array(
            'success' => $success,
            'errorMsg' => $errorMsg,
            'data' => $setting->toArray()
        ));
    }

    public function executeGetEventsByProjectId( sfWebRequest $request )
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $events = array();

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT c.id, c.description, c.is_holiday, c.start_date, c.end_date, c.event_type
                FROM ".GlobalCalendarTable::getInstance()->getTableName()." c
                WHERE c.region_id = ".$project->MainInformation->region_id. " AND c.event_type <> ".GlobalCalendar::EVENT_TYPE_STATE);

        $stmt->execute();

        $publicEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($publicEvents as $event)
        {
            array_push($events, array(
                'id' => 'gc_public-'.$event['id'],
                'projectStructureId' => $project->id,
                'summary' => $event['description'],
                'eventType' => (string)$event['event_type'],
                'startTime' => date("D M
                     d Y h:i:s", strtotime($event['start_date'])),
                'endTime' => date("D M d Y h:i:s", strtotime($event['end_date'])),
                'calendar' => 'calendar_' . $event['event_type'],
                'isHoliday' => $event['is_holiday'],
                'editable' => false
            ));
        }

        $stmt = $pdo->prepare("SELECT c.id, c.description, c.is_holiday, c.start_date, c.end_date, c.event_type
                FROM ".GlobalCalendarTable::getInstance()->getTableName()." c
                WHERE c.region_id = ".$project->MainInformation->region_id."
                AND c.subregion_id = ".$project->MainInformation->subregion_id."
                AND c.event_type = ".GlobalCalendar::EVENT_TYPE_STATE);

        $stmt->execute();

        $stateEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($stateEvents as $event)
        {
            array_push($events, array(
                'id' => 'gc_state-'.$event['id'],
                'projectStructureId' => $project->id,
                'summary' => $event['description'],
                'eventType' => (string)$event['event_type'],
                'startTime' => date("D M
                     d Y h:i:s", strtotime($event['start_date'])),
                'endTime' => date("D M d Y h:i:s", strtotime($event['end_date'])),
                'calendar' => 'calendar_' . $event['event_type'],
                'isHoliday' => $event['is_holiday'],
                'editable' => false
            ));
        }

        $stmt = $pdo->prepare("SELECT c.id, c.description, c.is_holiday, c.start_date, c.end_date, c.event_type
                FROM ".ProjectManagementCalendarTable::getInstance()->getTableName()." c
                WHERE c.project_structure_id = ".$project->id);

        $stmt->execute();

        $projectEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($projectEvents as $event)
        {
            array_push($events, array(
                'id' => $event['id'],
                'projectStructureId' => $project->id,
                'summary' => $event['description'],
                'eventType' => (string)$event['event_type'],
                'startTime' => date("D M d Y h:i:s", strtotime($event['start_date'])),
                'endTime' => date("D M d Y h:i:s", strtotime($event['end_date'])),
                'calendar' => 'calendar_' . $event['event_type'],
                'isHoliday' => $event['is_holiday'],
                'editable' => true
            ));
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items' => $events
        ));
    }

    public function executeGetEventTypeOptions( sfWebRequest $request )
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new ProjectManagementCalendarForm();

        return $this->renderJson(array(
            'eventTypeOptions' => array(
                array(
                    'id' => (string)GlobalCalendar::EVENT_TYPE_PUBLIC,
                    'label' => GlobalCalendar::EVENT_TYPE_PUBLIC_TEXT
                ),
                array(
                    'id' => (string)GlobalCalendar::EVENT_TYPE_STATE,
                    'label' => GlobalCalendar::EVENT_TYPE_STATE_TEXT
                ),
                array(
                    'id' => (string)GlobalCalendar::EVENT_TYPE_OTHER,
                    'label' => GlobalCalendar::EVENT_TYPE_OTHER_TEXT
                )
            ),
            'formValues' => array(
                'project_management_calendar[_csrf_token]' => $form->getCSRFToken()
            )
        ));
    }

    public function executeEventAdd( sfWebRequest $request )
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new ProjectManagementCalendarForm(Doctrine_Core::getTable('ProjectManagementCalendar')->find($request->getParameter('id')));

        if($this->isFormValid($request, $form))
        {
            $event = $form->save();

            $errors = null;

            $success = true;

            $values = array(
                'id' => $event->id,
                'summary' => $event->description,
                'projectStructureId' => $event->project_structure_id,
                'eventType' => $event->event_type,
                'startTime' => date("D M d Y h:i:s", strtotime($event->start_date)),
                'endTime' => date("D M d Y h:i:s", strtotime($event->end_date)),
                'calendar' => 'calendar_'.$event->event_type,
                'editable' => true,
                'isHoliday' => $event->is_holiday,
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

    public function executeEventDelete( sfWebRequest $request )
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $event = Doctrine_Core::getTable('ProjectManagementCalendar')->find($request->getParameter('id'))
        );

        try
        {
            $event->delete();

            $errorMsg = null;
            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success = false;
        }

        return $this->renderJson(array(
            'success' => $success,
            'errorMsg' => $errorMsg
        ));
    }
}