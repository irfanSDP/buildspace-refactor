<?php

class EProjectCountryTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectCountry');
    }

    public static function getCountryByName($name)
    {
        return DoctrineQuery::create()->select('*')
            ->from('EProjectCountry c')
            ->where('LOWER(c.country) = ?', strtolower(trim($name)))
            ->limit(1)
            ->fetchOne();
    }
}