<?php

class sfGuardGroupForm extends PluginsfGuardGroupForm
{

    public function configure()
    {
        parent::configure();

        unset($this['projects_list'], $this['tendering_projects_list'], $this['users_list']);

        // if current group id is allocate for super admin, then unset the ability to set menu's permission
        if ( ! $this->getObject()->isNew() AND $this->getObject()->id == 1 )
        {
            $this->validatorSchema->setOption('allow_extra_fields', true);

            unset($this['menus_list']);
        }

        $this->validatorSchema->setPostValidator(
            new sfValidatorDoctrineUnique(array('model' => 'sfGuardGroup', 'column' => array('name')), array('invalid' => 'Current entered group name has been used.'))
        );
    }

}