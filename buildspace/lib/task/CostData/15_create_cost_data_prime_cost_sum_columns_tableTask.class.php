<?php

class create_cost_data_prime_cost_sum_columns_tableTask extends sfBaseTask
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
        $this->name                = '15-create_cost_data_prime_cost_sum_columns_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [15-create_cost_data_prime_cost_sum_columns_table|INFO] task does things.
Call it with:

  [php symfony 15-create_cost_data_prime_cost_sum_columns_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = CostDataPrimeCostSumColumnTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('15-create_cost_data_prime_cost_sum_columns_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, master_cost_data_prime_cost_sum_item_id BIGINT NOT NULL, cost_data_prime_cost_sum_column_definition_id BIGINT NOT NULL, amount NUMERIC(18,5) DEFAULT 0, conversion_factor NUMERIC(18,5) DEFAULT 1, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id)); ",
            "CREATE UNIQUE INDEX cost_data_prime_cost_sum_column_unique_idx ON {$tableName} (master_cost_data_prime_cost_sum_item_id, cost_data_prime_cost_sum_column_definition_id);",
            "CREATE INDEX cost_data_prime_cost_sum_column_id_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_columns_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_columns_master_item_id FOREIGN KEY (master_cost_data_prime_cost_sum_item_id) REFERENCES BS_master_cost_data_prime_cost_sum_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_columns_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_columns_column_definition_id FOREIGN KEY (cost_data_prime_cost_sum_column_definition_id) REFERENCES BS_cost_data_prime_cost_sum_column_definitions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('15-create_cost_data_prime_cost_sum_columns_table', "Successfully created {$tableName} table!");
    }
}
