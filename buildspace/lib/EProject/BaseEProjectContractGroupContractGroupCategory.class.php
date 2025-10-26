<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectContractGroupContractGroupCategory', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectContractGroupContractGroupCategory')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectContractGroupContractGroupCategory extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('contract_group_contract_group_category');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('contract_group_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('contract_group_category_id', 'integer', null, array(
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

        $this->hasMany('EProjectContractGroup as ContractGroups', array(
            'local' => 'contract_group_id',
            'foreign' => 'id'));
        $this->hasMany('EProjectContractGroupCategory as ContractGroupCategories', array(
            'local' => 'contract_group_category_id',
            'foreign' => 'id'));
    }
}