<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectRequestForVariationContractAndContingencySum', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectRequestForVariationContractAndContingencySum')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectRequestForVariationContractAndContingencySum extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('request_for_variation_contract_and_contingency_sum');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));

        $this->hasColumn('original_contract_sum', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));

        $this->hasColumn('contingency_sum', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));

        $this->hasColumn('contract_sum_includes_contingency_sum', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));

        $this->hasColumn('user_id', 'integer', null, array(
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

    }
}
