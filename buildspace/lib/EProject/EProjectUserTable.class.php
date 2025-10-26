<?php

class EProjectUserTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectUser');
    }

    /**
     * Returns an array of all user ids in EProject.
     *
     * @return array
     */
    public static function getEProjectUserIds()
    {
        $results = Doctrine_Query::create()
            ->select('id')
            ->from('EProjectUser epu')
            ->where('epu.account_blocked_status IS FALSE')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $eProjectUserIds = array();

        foreach($results as $row)
        {
            $eProjectUserIds[] = $row['id'];
        }

        return $eProjectUserIds;
    }
}