<?php

class UserGroupAssignmentForm extends PluginsfGuardGroupForm
{

    public function configure()
    {
        $this->useFields(array('users_list'));
    }

    public function doSave($con = null)
    {
        $existing = $this->object->Users->getPrimaryKeys();
        $values = $this->getValue('users_list');
        if (!is_array($values))
        {
            $values = array();
        }

        $unlink = array_diff($existing, $values);
        if (count($unlink))
        {
            DoctrineQuery::create()
                ->delete('sfGuardUserGroup g')
                ->whereIn('g.user_id',  array_values($unlink))
                ->andWhere('g.group_id = ?', $this->object->id)
                ->execute();
        }

        $link = array_diff($values, $existing);
        if (count($link))
        {
            $collection = new Doctrine_Collection('sfGuardUserGroup');

            foreach($link as $userId)
            {
                $userGroup = new sfGuardUserGroup();
                $userGroup->user_id = $userId;
                $userGroup->group_id = $this->object->id;

                $collection->add($userGroup);
            }

            $collection->save($con);
        }

        parent::doSave($con);
    }
}