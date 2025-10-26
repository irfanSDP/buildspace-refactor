<?php

class create_bill_item_cost_data_prime_cost_rate_tableTask extends migrationTask
{
    protected $name = '37-create_bill_item_cost_data_prime_cost_rate_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = BillItemCostDataPrimeCostRateTable::getInstance()->getTableName();

        $queries = array(
            "CREATE TABLE {$this->tableName} (id BIGSERIAL, bill_item_id BIGINT NOT NULL, cost_data_prime_cost_rate_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX bill_item_cost_data_prime_cost_rate_unique_idx ON {$this->tableName} (cost_data_prime_cost_rate_id, bill_item_id);",
            "CREATE INDEX bill_item_cost_data_prime_cost_rate_id_idx ON {$this->tableName} (id);",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_rate_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_rate_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_rate_prime_cost_rate_id FOREIGN KEY (cost_data_prime_cost_rate_id) REFERENCES BS_cost_data_prime_cost_rates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_rate_bill_item_id FOREIGN KEY (bill_item_id) REFERENCES BS_bill_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        $this->createTable($this->tableName, $queries);
    }
}