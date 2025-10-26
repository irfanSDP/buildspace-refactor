<?php

class add_is_prime_cost_rate_summary_displayed_column_to_master_cost_data_particulars_tableTask extends migrationTask
{
    protected $name = '33-add_is_prime_cost_rate_summary_displayed_column_to_master_cost_data_particulars_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = MasterCostDataParticularTable::getInstance()->getTableName();

        $this->addColumn();
    }

    protected function addColumn()
    {
        $this->columnName = 'is_prime_cost_rate_summary_displayed';

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