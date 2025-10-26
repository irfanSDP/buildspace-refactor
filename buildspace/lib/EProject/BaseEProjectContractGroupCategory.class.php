<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectContractGroupCategory', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectContractGroupCategory')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectContractGroupCategory extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('contract_group_categories');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('name', 'string', null, array(
            'type' => 'string',
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

        $this->hasMany('EProjectCompany as Companies', array(
            'local' => 'id',
            'foreign' => 'contract_group_category_id'));

        $this->hasMany('EProjectContractGroupContractGroupCategory as ContractGroups', array(
            'local' => 'id',
            'foreign' => 'contract_group_category_id'));
    }
}