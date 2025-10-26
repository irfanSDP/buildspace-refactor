<?php

class create_cost_data_prime_cost_sum_column_definitions_tableTask extends sfBaseTask
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
        $this->name                = '14-create_cost_data_prime_cost_sum_column_definitions_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [14-create_cost_data_prime_cost_sum_column_definitions_table|INFO] task does things.
Call it with:

  [php symfony 14-create_cost_data_prime_cost_sum_column_definitions_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = CostDataPrimeCostSumColumnDefinitionTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('14-create_cost_data_prime_cost_sum_column_definitions_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, cost_data_id BIGINT NOT NULL, column_name TEXT, priority BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX cost_data_prime_cost_sum_column_definition_unique_idx ON {$tableName} (cost_data_id, column_name);",
            "CREATE INDEX cost_data_prime_cost_sum_column_definition_id_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_column_definitions_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_column_definitions_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_sum_column_definitions_cost_data_id FOREIGN KEY (cost_data_id) REFERENCES BS_cost_data(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('14-create_cost_data_prime_cost_sum_column_definitions_table', "Successfully created {$tableName} table!");
    }
}
