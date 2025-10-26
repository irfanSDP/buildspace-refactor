<?php

class BsUserForm extends sfGuardUserForm
{
    protected $password;
    public function configure()
    {
        parent::configure();

        unset(
            $this['first_name'],
            $this['last_name'],
            $this['password'],
            $this['last_login'],
            $this['created_at'],
            $this['updated_at'],
            $this['salt'],
            $this['algorithm']
        );

        $this->widgetSchema['username']->setAttributes(array(
            'style'=>'width:282px;',
            'data-dojo-type' => 'dijit.form.ValidationTextBox'));

        $this->password = $this->getOption('password');

        $profileForm = new sfGuardUserProfileForm($this->object->Profile);

        $this->embedForm('Profile', $profileForm);

        $this->widgetSchema['groups_list']->setAttributes(array('class'=>'groups_list', 'multiple'=>true));

        $this->setValidator('groups_list', new sfValidatorDoctrineChoice(array(
                'multiple' => true,
                'model' => 'sfGuardGroup',
                'required' => true), array(
                'required' => 'User groups is required')
        ));

        $this->setValidator('username', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 200), array(
                'required'=>'Username is required',
                'max_length'=>'Username is too long (%max_length% character max)')
        ));
    }
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
