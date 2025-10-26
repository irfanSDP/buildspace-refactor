<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectSubsidiary', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectSubsidiary')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectSubsidiary extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('subsidiaries');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('name', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('identifier', 'varchar', 10 , array(
            'type' => 'string',
            'notnull' => true
        ));
        $this->hasColumn('company_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('parent_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false
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

        $this->hasOne('EProjectCompany as Company', array(
            'local' => 'company_id',
            'foreign' => 'id'));
        
        $this->hasOne('EProjectSubsidiary as Parent', array(
            'local' => 'parent_id',
            'foreign' => 'id'));
    }
}