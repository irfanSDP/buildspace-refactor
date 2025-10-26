<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectSiteManagementDefect', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectSiteManagementDefect')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectSiteManagementDefect extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('site_management_defects');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('project_structure_location_code_id', 'integer', null, array(
            'type' => 'integer'
        ));
        

        $this->option('orderBy', 'id');
        $this->option('symfony', array(
            'filter' => false,
            'form'   => false
        ));
    }
}