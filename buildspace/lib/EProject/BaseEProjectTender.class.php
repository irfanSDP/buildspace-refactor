<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectTender', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectTender')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectTender extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('tenders');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('count', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 0
        ));
        $this->hasColumn('current_form_type', 'smallint', null, array(
            'type' => 'smallint',
            'notnull' => true
        ));
        $this->hasColumn('tender_starting_date', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('tender_closing_date', 'date', null, array(
            'type' => 'date',
            'notnull' => true
        ));
        $this->hasColumn('retender_status', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false
        ));
        $this->hasColumn('retender_verification_status', 'smallint', null, array(
            'type' => 'smallint',
            'notnull' => true
        ));
        $this->hasColumn('open_tender_status', 'smallint', null, array(
            'type' => 'smallint',
            'notnull' => true
        ));
        $this->hasColumn('open_tender_verification_status', 'smallint', null, array(
            'type' => 'smallint',
            'notnull' => true
        ));
        $this->hasColumn('validity_period_in_days', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false
        ));
        $this->hasColumn('technical_evaluation_verification_status', 'smallint', null, array(
            'type' => 'smallint',
            'notnull' => true
        ));
        $this->hasColumn('currently_selected_tenderer_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false
        ));
        $this->hasColumn('created_by', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('updated_by', 'integer', null, array(
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

        $this->hasMany('EProjectCompanyTender as CompanyTenders', array(
            'local' => 'id',
            'foreign' => 'tender_id'));
    }
}
