<?php

class BQLibraryTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('BQLibrary');
    }

    public static function getParentNode($id)
    {
        $library = self::getInstance()->find($id);

        $parent = $library->node->hasParent() ? $library->node->getParent() : $library;

        return $parent;
    }


}