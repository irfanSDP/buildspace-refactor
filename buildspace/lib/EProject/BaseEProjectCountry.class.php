<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectCountry', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectCountry')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectCountry extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('countries');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('iso', 'varchar', 3, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 3,
        ));
        $this->hasColumn('iso3', 'varchar', 3, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 3,
        ));
        $this->hasColumn('fips', 'varchar', 3, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 3,
        ));
        $this->hasColumn('country', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('continent', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('currency_code', 'varchar', 3, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 3,
        ));
        $this->hasColumn('currency_name', 'varchar', 60, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 60,
        ));
        $this->hasColumn('phone_prefix', 'varchar', 60, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 60,
        ));
        $this->hasColumn('postal_code', 'varchar', 60, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 60,
        ));
        $this->hasColumn('languages', 'varchar', 50, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 50,
        ));
        $this->hasColumn('geonameid', 'varchar', 10, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 10,
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