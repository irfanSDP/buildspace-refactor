<?php

class add_include_provisional_sum_column_to_master_cost_data_particulars_tableTask extends migrationTask
{
    protected $name = '34-add_include_provisional_sum_column_to_master_cost_data_particulars_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = MasterCostDataParticularTable::getInstance()->getTableName();

        $this->addColumn();
    }

    protected function addColumn()
    {
        $this->columnName = 'include_provisional_sum';

        if($this->columnExists($this->tableName, $this->columnName)) return $this->logSection($this->name, "Column {$this->columnName} already exists in {$this->tableName} table!");

        $queries = array(
            "ALTER TABLE {$this->tableName} ADD COLUMN {$this->columnName} BOOLEAN DEFAULT 'false' NOT NULL"
        );

        foreach ($queries as $query )
        {
            $stmt = $this->con->prepare($query);

            $stmt->execute();
        }

        $this->logSection($this->name, "Successfully added column {$this->columnName} to {$this->tableName} table!");
    }
}