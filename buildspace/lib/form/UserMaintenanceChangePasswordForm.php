<?php

class UserMaintenanceChangePasswordForm extends sfGuardUserForm
{
    public function configure()
    {
        parent::configure();

        unset($this['first_name'], $this['last_name'], $this['email_address'], $this['username'], $this['password'], $this['type'], $this['is_active'], $this['last_login'], $this['created_at'], $this['updated_at'], $this['salt'], $this['algorithm']);

        $this->setWidgets(array(
            'new_password'        => new sfWidgetFormInput(array('type' => 'password')),
            'repeat_new_password' => new sfWidgetFormInput(array('type' => 'password')),
        ));

        $this->setValidators(array(
            'new_password'        => new sfValidatorString(array('required'=>true,'trim'=>true, 'min_length'=>6, 'max_length'=>128), array('min_length'=>'Password is too short (Min %min_length%)')),
            'repeat_new_password' => new sfValidatorString(array('required'=>true)),
        ));

        $this->validatorSchema->setPostValidator(new sfValidatorSchemaCompare('new_password', sfValidatorSchemaCompare::EQUAL, 'repeat_new_password', array(), array('invalid' => 'Confirm New Password does not match.')));

        $this->widgetSchema->setNameFormat('change_password[%s]');
    }

    public function doSave($con = null)
    {
        $this->object->setPassword($this->getValue('new_password'));

        return parent::doSave($con);
    }
}