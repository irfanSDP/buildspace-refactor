<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectDailyLabourReport', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectDailyLabourReport')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectDailyLabourReport extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('daily_labour_reports');
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