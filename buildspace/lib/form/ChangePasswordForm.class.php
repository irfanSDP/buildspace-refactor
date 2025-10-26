<?php

class ChangePasswordForm extends sfGuardUserForm
{
    public function configure()
    {
        parent::configure();

        unset($this['first_name'], $this['last_name'], $this['email_address'], $this['username'], $this['password'], $this['type'], $this['is_active'], $this['last_login'], $this['created_at'], $this['updated_at'], $this['salt'], $this['algorithm']);

        $this->setWidgets(array(
            'current_password'    => new sfWidgetFormInput(array('type' => 'password')),
            'new_password'        => new sfWidgetFormInput(array('type' => 'password')),
            'repeat_new_password' => new sfWidgetFormInput(array('type' => 'password')),
        ));

        $this->setValidators(array(
            'current_password'    => new OldPasswordValidator(array('user_id'=>$this->object->getId()), array('invalid' => 'Old Password is incorrect.')),
            'new_password'        => new sfValidatorString(array('required'=>true,'trim'=>true, 'min_length'=>6, 'max_length'=>128), array('min_length'=>'Password is too short (Min %min_length%)')),
            'repeat_new_password' => new sfValidatorString(array('required'=>true)),
        ));


        $this->mergePostValidator(new sfValidatorCallback(array('callback' => array($this, 'validateAgainstDefaultPassword'))));

        $this->mergePostValidator(new sfValidatorCallback(array('callback' => array($this, 'compareNewPassword'))));

        $this->widgetSchema->setNameFormat('change_password[%s]');
    }

    public function compareNewPassword(sfValidatorCallback $validator, array $values)
    {
        if(strlen($values['new_password']) > 0 && $values['new_password'] != $values['repeat_new_password'])
        {
            $sfError = new sfValidatorError($validator, 'New password does not match');
            throw new sfValidatorErrorSchema($validator, array('new_password' => $sfError));
        }

        return $values;
    }

    public function validateAgainstDefaultPassword(sfValidatorCallback $validator, array $values)
    {
        if($values['new_password'] == sfConfig::get('app_default_user_password'))
        {
            $sfError = new sfValidatorError($validator, 'You cannot use default password as new password');
            throw new sfValidatorErrorSchema($validator, array('new_password' => $sfError));
        }

        return $values;
    }

    public function doSave($con = null)
    {
        $this->object->setPassword($this->getValue('new_password'));

        return parent::doSave($con);
    }
}