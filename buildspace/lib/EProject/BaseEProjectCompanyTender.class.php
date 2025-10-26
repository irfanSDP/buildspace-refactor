<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectCompanyTender', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectCompanyTender')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectCompanyTender extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('company_tender');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('company_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('tender_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('rates', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('tender_amount', 'string', null, array(
            'type' => 'string',
            'notnull' => false
        ));
        $this->hasColumn('completion_period', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 0
        ));
        $this->hasColumn('submitted', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false
        ));
        $this->hasColumn('can_login', 'boolean', null, array(
            'type' => 'boolean',
            'default' => true
        ));
        $this->hasColumn('selected_contractor', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false
        ));
        $this->hasColumn('submitted_at', 'date', null, array(
            'type' => 'date',
            'notnull' => false
        ));
        $this->hasColumn('supply_of_material_amount', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('other_bill_type_amount_except_prime_cost_provisional', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('contractor_adjustment_percentage', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('original_tender_amount', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('discounted_percentage', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('discounted_amount', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'default' => 0,
        ));
        $this->hasColumn('earnest_money', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false
        ));
        $this->hasColumn('remarks', 'string', null, array(
            'type' => 'string',
            'notnull' => false
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

        $this->hasOne('EProjectCompany as Company', array(
            'local' => 'company_id',
            'foreign' => 'id'));

        $this->hasOne('EProjectTender as Tender', array(
            'local' => 'tender_id',
            'foreign' => 'id'));
    }
}
