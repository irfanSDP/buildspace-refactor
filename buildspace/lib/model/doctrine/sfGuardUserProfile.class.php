<?php
class sfGuardUserProfile extends BasesfGuardUserProfile
{
    public function getPhoto()
    {
        return strlen($this->profile_photo) > 0 ? $this->profile_photo : 'default_user-icon.jpg';
    }

    public function getEProjectUser()
    {
        return Doctrine_Query::create()
            ->from('EProjectUser epu')
            ->where('epu.id = ?',$this->eproject_user_id)
            ->fetchOne();
    }
}
