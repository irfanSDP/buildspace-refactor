<?php

class BsUserRegistrationForm extends sfGuardUserForm
{
    protected $password;
    public function configure()
    {
        parent::configure();

        $this->useFields(
            array(
                'email_address',
                'username',
                'is_active',
                'is_super_admin',
            )
        );

        $this->widgetSchema['username']->setAttributes(array('style'=>'width:282px;', 'class' => 'x-form-text x-form-field'));

        $this->password = $this->getOption('password');

        $this->setValidator('username', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 200), array(
                'required'=>'Username is required',
                'max_length'=>'Username is too long (%max_length% character max)')
        ));

//        $this->validatorSchema->setPostValidator(
//            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
//        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('u.id, u.username')->from('sfGuardUser u');
        $query->where('LOWER(u.username) = ?', strtolower($values['username']));
        $query->andWhere('u.is_active IS TRUE');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another user with that username.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('username' => $sfError));
            }
            else
            {
                $user = $query->fetchOne();
                if($this->object->getId() != $user->getId())
                {
                    throw new sfValidatorErrorSchema($validator, array('username' => $sfError));
                }
            }
        }

        $query = DoctrineQuery::create()->select('u.id, u.email_address')->from('sfGuardUser u');
        $query->where('LOWER(u.email_address) = ?', strtolower($values['Profile']['email']));
        $query->andWhere('u.is_active IS TRUE');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another user with that email address.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('username' => $sfError));
            }
            else
            {
                $user = $query->fetchOne();
                if($this->object->getId() != $user->getId())
                {
                    throw new sfValidatorErrorSchema($validator, array('username' => $sfError));
                }
            }
        }

        return $values;
    }

//    public function bind(array $taintedValues = null, array $taintedFiles = null)
//    {
//        $day = null;
//        $month = null;
//        $year = null;
//
//        if(strlen($taintedValues['Profile']['birthday_text']) > 0)
//        {
//            list($day, $month, $year) = Utilities::explodeToDate('/',$taintedValues['Profile']['birthday_text']);
//            $taintedValues['Profile']['birthday'] = array('day'=>$day,'month'=>$month,'year'=>$year);
//        }
//
//        if(strlen($taintedValues['Profile']['email']) > 0)
//        {
//            //we set sf_guard_users email_address using profile's email
//            $taintedValues['email_address'] = $taintedValues['Profile']['email'];
//        }
//
//        return parent::bind($taintedValues, $taintedFiles);
//    }

    /*
    * Override the doSave method to set default password when creating new user.
    */
    public function doSave($con = null)
    {
        if($this->object->isNew())
        {
            $this->object->setPassword($this->password);
        }

        return parent::doSave($con);
    }

}
