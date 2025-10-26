<?php

/**
 * ProjectSchedule form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectScheduleForm extends BaseProjectScheduleForm
{
    public function configure()
    {
        $this->useFields(array('title', 'description', 'exclude_saturdays', 'exclude_sundays', 'start_date', 'timezone', 'project_structure_id'));
    }

    public function doSave($con=null)
    {
        if($this->object->isNew())
        {
            if($this->getOption('sub_package_id'))
            {
                $this->object->sub_package_id = $this->getOption('sub_package_id');
                $type = ProjectSchedule::TYPE_SUB_PACKAGE;
            }
            else
            {
                $type = ProjectSchedule::TYPE_MAIN_PROJECT;
            }

            $this->object->type = $type;
        }

        parent::doSave($con);
    }
}
