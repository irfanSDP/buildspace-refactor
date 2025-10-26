<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectProjectLabourRate', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectProjectLabourRate')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectProjectLabourRate extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_labour_rates');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('labour_type', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('normal_working_hours', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('normal_rate_per_hour', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('ot_rate_per_hour', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('project_id', 'integer', null, array(
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
        $this->hasColumn('submitted_by', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('pre_defined_location_code_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('contractor_id', 'integer', null, array(
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

        $this->hasOne('EProjectProject as Project', array(
            'local' => 'project_id',
            'foreign' => 'id'));

        $this->hasOne('PreDefinedLocationCode', array(
            'local' => 'pre_defined_location_code_id',
            'foreign' => 'id'));

        $this->hasOne('EProjectCompany as Company', array(
            'local' => 'contractor_id',
            'foreign' => 'id'));
    }
}
