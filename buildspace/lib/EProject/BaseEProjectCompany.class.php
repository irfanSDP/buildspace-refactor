<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectCompany', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectCompany')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectCompany extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('companies');
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
        $this->hasColumn('address', 'string', null, array(
            'type' => 'string',
            'notnull' => true
        ));
        $this->hasColumn('main_contact', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('email', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('telephone_number', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('fax_number', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('reference_id', 'varchar', 16, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 16,
        ));
        $this->hasColumn('reference_no', 'varchar', 16, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 16,
        ));
        $this->hasColumn('contract_group_category_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('confirmed', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
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

        $this->hasMany('EProjectUser as Users', array(
            'local' => 'id',
            'foreign' => 'company_id'));

        $this->hasOne('EProjectContractGroupCategory as ContractGroupCategory', array(
            'local' => 'contract_group_category_id',
            'foreign' => 'id'));

        $this->hasOne('EProjectSubsidiary as Subsidiary', array(
            'local' => 'id',
            'foreign' => 'company_id'));
    }
}