<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectDefectCategoryPreDefinedLocationCode', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectDefectCategoryPreDefinedLocationCode')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectDefectCategoryPreDefinedLocationCode extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('defect_category_pre_defined_location_code');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('pre_defined_location_code_id', 'integer', null, array(
            'type' => 'integer'
        ));
        $this->hasColumn('defect_category_id', 'integer', null, array(
            'type' => 'integer'
        ));
        

        $this->option('orderBy', 'id');
        $this->option('symfony', array(
            'filter' => false,
            'form'   => false
        ));
    }
}