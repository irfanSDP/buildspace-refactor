<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectCompany', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectProjectContractManagementModule')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectProjectContractManagementModule extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_contract_management_modules');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('module_identifier', 'integer', null, array(
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

        $this->hasMany('EProjectUser as Users', array(
            'local' => 'id',
            'foreign' => 'company_id'));

        $this->hasOne('EProjectContractGroupCategory as ContractGroupCategory', array(
            'local' => 'contract_group_category_id',
            'foreign' => 'id'));
    }
}