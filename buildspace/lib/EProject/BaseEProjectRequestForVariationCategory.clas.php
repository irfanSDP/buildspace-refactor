<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectRequestForVariationCategory', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectRequestForVariationCategory')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectRequestForVariationCategory extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('request_for_variation_categories');
        $this->hasColumn('id','integer', null, array(
            'type'=> 'integer',
            'primary'=> true,
            'autoincrement'=> true,
        ));
        $this->hasColumn('name', 'string', null, array(
            'type' => 'string',
            'notnull' => true,
        ));
        $this->hasColumn('description', 'string', null, array(
            'type' => 'string',
            'notnull' => false,
        ));
        $this->hasColumn('created_at', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('updated_at', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('kpi_limit', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));

        $this->option('orderBy', 'id');
        $this->option('symfony', array(
            'filter' => false,
            'form'   => false
        ));
    }

    public function setUp()
    {
        parent::setUp();
    }
}