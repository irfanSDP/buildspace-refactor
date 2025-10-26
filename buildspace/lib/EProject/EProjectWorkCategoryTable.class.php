<?php

class EProjectWorkCategoryTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectWorkCategory');
    }

    public static function getWorkCategoryByName($name)
    {
        return DoctrineQuery::create()->select('*')
            ->from('EProjectWorkCategory c')
            ->where('LOWER(TRIM(c.name)) = ?', strtolower(trim($name)))
            ->andWhere('c.deleted_at IS NULL')
            ->limit(1)
            ->fetchOne();
    }

    public static function identifierIsUnique($identifier)
    {
        $query = DoctrineQuery::create()->select('w.id, w.name')->from('EProjectWorkCategory w');
        $query->where('w.identifier = ?', $identifier);

        return ( $query->count() == 0 );
    }

    public static function deleteByName($name)
    {
        $eProjectWorkCategory = EProjectWorkCategoryTable::getWorkCategoryByName($name);

        $eProjectWorkCategory->deleted_at = date("Y-m-d H:i:s");

        $eProjectWorkCategory->save();
    }
}
