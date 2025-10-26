<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectContractGroup', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectContractGroup')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectContractGroup extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('contract_groups');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('contract_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('group', 'integer', null, array(
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

        $this->hasMany('EProjectContractGroupContractGroupCategory as ContractGroupCategories', array(
            'local' => 'id',
            'foreign' => 'contract_group_id'));
    }
}