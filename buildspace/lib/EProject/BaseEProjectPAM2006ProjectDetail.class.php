<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectPAM2006ProjectDetail', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectPAM2006ProjectDetail')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectPAM2006ProjectDetail extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('pam_2006_project_details');

        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('commencement_date', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('completion_date', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('contract_sum', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('pre_defined_location_code_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('created_at', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('updated_at', 'date', null, array(
            'type' => 'date',
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

        $this->hasOne('EProjectProject as Project', array(
            'local' => 'project_id',
            'foreign' => 'id'));

        $this->hasOne('PreDefinedLocationCode', array(
            'local' => 'pre_defined_location_code_id',
            'foreign' => 'id'));
    }
}
