<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectRequestForVariation', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectRequestForVariation')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectRequestForVariation extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('request_for_variations');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('rfv_number', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('ai_number', 'varchar', null, array(
            'type' => 'varchar',
            'notnull' => false
        ));
        $this->hasColumn('description', 'string', null, array(
            'type' => 'string',
            'notnull' => true
        ));
        $this->hasColumn('reasons_for_variation', 'string', null, array(
            'type' => 'string',
            'notnull' => true
        ));
        $this->hasColumn('request_for_variation_category_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('nett_omission_addition', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));
        $this->hasColumn('initiated_by', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('time_implication', 'string', null, array(
            'type' => 'string',
            'notnull' => false
        ));
        $this->hasColumn('status', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('permission_module_in_charge', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false
        ));
        $this->hasColumn('accumulative_approved_rfv_amount', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));
        $this->hasColumn('proposed_rfv_amount', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));
        $this->hasColumn('request_for_variation_user_permission_group_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
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

        $this->hasOne('VariationOrder as BuildspaceVariationOrder', array(
            'local' => 'id',
            'foreign' => 'eproject_rfv_id'));

    }
}
