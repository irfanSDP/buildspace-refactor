<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectState', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectState')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectState extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('states');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('country_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('name', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('timezone', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
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

        $this->hasOne('EProjectCountry as Country', array(
            'local' => 'country_id',
            'foreign' => 'id'));
    }
}