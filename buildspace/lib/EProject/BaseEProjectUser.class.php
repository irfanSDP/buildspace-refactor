<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectUser', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectUser')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectUser extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('users');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('company_id', 'integer', null, array(
            'type' => 'integer'
        ));
        $this->hasColumn('name', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('contact_number', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('username', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('email', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('password', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('confirmation_code', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('remember_token', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('confirmed', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
        ));
        $this->hasColumn('is_super_admin', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
        ));
        $this->hasColumn('is_admin', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
        ));
        $this->hasColumn('account_blocked_status', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
        ));
        $this->hasColumn('allow_access_to_buildspace', 'boolean', null, array(
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

        $this->hasOne('sfGuardUserProfile as BuildspaceUser', array(
            'local' => 'id',
            'foreign' => 'eproject_user_id'));

        $this->hasOne('EProjectCompany as Company', array(
            'local' => 'company_id',
            'foreign' => 'id'));
    }
}