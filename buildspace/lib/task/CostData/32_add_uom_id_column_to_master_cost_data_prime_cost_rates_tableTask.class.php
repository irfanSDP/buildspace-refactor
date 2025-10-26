<?php

class add_uom_id_column_to_master_cost_data_prime_cost_rates_table extends migrationTask
{
    protected $name = '32-add_uom_id_column_to_master_cost_data_prime_cost_rates_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = MasterCostDataPrimeCostRateTable::getInstance()->getTableName();

        $this->addColumn();
    }

    protected function addColumn()
    {
        $this->columnName = 'uom_id';

        if($this->columnExists($this->tableName, $this->columnName)) return $this->logSection($this->name, "Column uom_id in table {$this->tableName} already exists!");

        $queries = array(
            "ALTER TABLE {$this->tableName} ADD COLUMN {$this->columnName} BIGINT",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT master_cost_data_prime_cost_rates_uom_id FOREIGN KEY ({$this->columnName}) REFERENCES BS_unit_of_measurements(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $this->con->prepare($query);

            $stmt->execute();
        }

        $this->logSection($this->name, "Successfully added column {$this->columnName} to {$this->tableName} table!");
    }
}