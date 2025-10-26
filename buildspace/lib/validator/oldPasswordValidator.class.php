<?php

class OldPasswordValidator extends sfValidatorBase
{
    public function configure($options = array(), $messages = array())
    {
        $this->addRequiredOption('user_id');
        $this->setMessage('invalid', 'Current password is invalid.');
    }

    protected function doClean($value)
    {
        $clean = (string) $value;

        // password is ok?
        if($user = Doctrine_Core::getTable('sfGuardUser')->find($this->getOption('user_id')) and $user->checkPassword($value))
        {
            return $clean;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
}