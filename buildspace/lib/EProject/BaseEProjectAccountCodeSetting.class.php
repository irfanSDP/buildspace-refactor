<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectAccountCodeSetting', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectAccountCodeSetting')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectAccountCodeSetting extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('account_code_settings');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('apportionment_type_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('account_group_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false
        ));
        $this->hasColumn('status', 'integer', null, array(
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
    }
}

?>