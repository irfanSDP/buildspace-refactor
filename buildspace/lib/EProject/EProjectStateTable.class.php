<?php

class EProjectStateTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectState');
    }

    public static function getStateByName($name)
    {
        return DoctrineQuery::create()->select('*')
            ->from('EProjectState s')
            ->where('LOWER(s.name) = ?', strtolower(trim($name)))
            ->limit(1)
            ->fetchOne();
    }
}