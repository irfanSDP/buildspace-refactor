<?php

class drop_show_column_in_master_cost_data_particulars_table_task extends migrationTask
{
    protected $name = '36-drop_show_column_in_master_cost_data_particulars_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = MasterCostDataParticularTable::getInstance()->getTableName();

        $this->columnName = 'show';

        if(!$this->columnExists($this->tableName, $this->columnName)) return $this->logSection($this->name, "{$this->columnName} column does not exist in {$this->tableName} table!");

        $queries = array(
            "ALTER TABLE {$this->tableName} DROP COLUMN show;",
        );

        $this->runQueries($queries);

        $this->logSection($this->name, "Successfully dropped {$this->columnName} column from {$this->tableName} table!");
    }
}