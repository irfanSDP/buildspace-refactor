<?php

/**
 * ProjectManagementCalendar form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectManagementCalendarForm extends BaseProjectManagementCalendarForm
{
    public function configure()
    {
        unset($this['deleted_at'], $this['created_at'], $this['updated_at']);
    }

    public function doSave($conn = null)
    {
        $values = $this->getValues();

        if($values['event_type'] == GlobalCalendar::EVENT_TYPE_OTHER && !isset($values['is_holiday']))
        {
            $this->object->is_holiday = false;
        }
        else
        {
            $this->object->is_holiday = true;
        }

        parent::doSave($conn);
    }
}
