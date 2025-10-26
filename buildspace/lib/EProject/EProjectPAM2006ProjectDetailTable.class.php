<?php

class EProjectPAM2006ProjectDetailTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectPAM2006ProjectDetail');
    }
}
