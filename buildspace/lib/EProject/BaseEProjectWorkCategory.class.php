<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectWorkCategory', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectWorkCategory')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectWorkCategory extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('work_categories');
        $this->hasColumn('id', 'integer', null, array(
            'type'          => 'integer',
            'primary'       => true,
            'autoincrement' => true
        ));
        $this->hasColumn('name', 'varchar', 255, array(
            'type'    => 'varchar',
            'notnull' => true,
            'length'  => 255,
        ));
        $this->hasColumn('identifier', 'varchar', 255, array(
            'type'    => 'varchar',
            'notnull' => true,
            'length'  => 10,
        ));
        $this->hasColumn('created_at', 'timestamp', null, array(
            'type'    => 'timestamp',
            'notnull' => true
        ));
        $this->hasColumn('updated_at', 'timestamp', null, array(
            'type'    => 'timestamp',
            'notnull' => true
        ));
        $this->hasColumn('deleted_at', 'timestamp', null, array(
            'type'    => 'timestamp',
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

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}