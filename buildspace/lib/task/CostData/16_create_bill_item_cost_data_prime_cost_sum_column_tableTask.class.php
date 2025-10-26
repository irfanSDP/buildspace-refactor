<?php

class create_bill_item_cost_data_prime_cost_sum_column_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'costdata';
        $this->name                = '16-create_bill_item_cost_data_prime_cost_sum_column_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [16-create_bill_item_cost_data_prime_cost_sum_column_table|INFO] task does things.
Call it with:

  [php symfony 16-create_bill_item_cost_data_prime_cost_sum_column_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = BillItemCostDataPrimeCostSumColumnTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('16-create_bill_item_cost_data_prime_cost_sum_column_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, bill_item_id BIGINT NOT NULL, cost_data_prime_cost_sum_column_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX bill_item_cost_data_prime_cost_sum_column_unique_idx ON {$tableName} (cost_data_prime_cost_sum_column_id, bill_item_id);",
            "CREATE INDEX bill_item_cost_data_prime_cost_sum_column_id_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_sum_column_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_sum_column_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_sum_column_column_id FOREIGN KEY (cost_data_prime_cost_sum_column_id) REFERENCES BS_cost_data_prime_cost_sum_columns(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_bill_item_cost_data_prime_cost_sum_column_bill_item_id FOREIGN KEY (bill_item_id) REFERENCES BS_bill_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('16-create_bill_item_cost_data_prime_cost_sum_column_table', "Successfully created {$tableName} table!");
    }
}
