<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectProject', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectProject')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectProject extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('projects');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('business_unit_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('contract_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('title', 'varchar', null, array(
            'type' => 'varchar',
            'notnull' => true
        ));
        $this->hasColumn('reference', 'varchar', 255, array(
            'type' => 'varchar',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('address', 'string', null, array(
            'type' => 'string',
            'notnull' => true
        ));
        $this->hasColumn('description', 'string', null, array(
            'type' => 'string',
            'notnull' => true
        ));
        $this->hasColumn('running_number', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 1
        ));
        $this->hasColumn('created_by', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('updated_by', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('status_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 1
        ));
        $this->hasColumn('country_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('state_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('work_category_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 1
        ));
        $this->hasColumn('modified_currency_code', 'varchar', 3, array(
            'type' => 'varchar',
            'notnull' => false,
            'length' => 3,
        ));
        $this->hasColumn('modified_currency_name', 'varchar', 60, array(
            'type' => 'varchar',
            'notnull' => false,
            'length' => 60,
        ));
        $this->hasColumn('parent_project_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('subsidiary_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('e_bidding', 'boolean', null, array(
            'type' => 'boolean',
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

        $this->hasOne('ProjectMainInformation as BuildspaceProjectMainInfo', array(
            'local' => 'id',
            'foreign' => 'eproject_origin_id'));

        $this->hasOne('EProjectWorkCategory as WorkCategory', array(
            'local' => 'work_category_id',
            'foreign' => 'id'));

        $this->hasOne('EProjectCountry as Country', array(
            'local' => 'country_id',
            'foreign' => 'id'));

        $this->hasOne('EProjectState as State', array(
            'local' => 'state_id',
            'foreign' => 'id'));

        $this->hasOne('EProjectSubsidiary as Subsidiary', array(
            'local' => 'subsidiary_id',
            'foreign' => 'id'));

        $this->hasMany('EProjectTender as Tenders', array(
            'local' => 'id',
            'foreign' => 'project_id'));

        $this->hasOne('EProjectPAM2006ProjectDetail as PAM2006ProjectDetail', array(
            'local' => 'id',
            'foreign' => 'project_id'));

        $this->hasOne('EProjectIndonesiaCivilContractInformation as IndonesiaCivilContractInformation', array(
            'local' => 'id',
            'foreign' => 'project_id'));

        $this->hasOne('EProjectAccountCodeSetting as AccountCodeSetting', array(
            'local' => 'id',
            'foreign' => 'project_id'));

        $this->hasOne('EProjectRequestForVariation as RequestForVariation', array(
            'local' => 'id',
            'foreign' => 'project_id'));

        $this->hasOne('EProjectRequestForVariationContractAndContingencySum as RequestForVariationContractAndContingencySum', array(
            'local' => 'id',
            'foreign' => 'project_id'));

        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($softdelete0);
    }
}
