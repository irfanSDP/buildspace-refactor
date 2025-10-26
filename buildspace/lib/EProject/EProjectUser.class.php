<?php

class EProjectUser extends BaseEProjectUser
{
    public function getBuildSpaceUser()
    {
        return Doctrine_Query::create()
            ->from('sfGuardUserProfile u')
            ->where('u.eproject_user_id = ?', $this->id)
            ->fetchOne();
    }
}
