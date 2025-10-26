<?php

class create_cost_data_prime_cost_rates_tableTask extends sfBaseTask
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
        $this->name                = '20-create_cost_data_prime_cost_rates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [20-create_cost_data_prime_cost_rates_table|INFO] task does things.
Call it with:

  [php symfony 20-create_cost_data_prime_cost_rates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = CostDataPrimeCostRateTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('20-create_cost_data_prime_cost_rates_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, cost_data_id BIGINT NOT NULL, master_cost_data_prime_cost_rate_id BIGINT NOT NULL, show BOOLEAN DEFAULT 'true' NOT NULL, units NUMERIC(18,5) DEFAULT 0, approved_value NUMERIC(18,5) DEFAULT 0, approved_brand TEXT, awarded_value NUMERIC(18,5) DEFAULT 0, awarded_brand TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX cost_data_prime_cost_rate_id_idx ON {$tableName} (id);",
            "CREATE INDEX cost_data_prime_cost_rate_fk_idx ON {$tableName} (cost_data_id, master_cost_data_prime_cost_rate_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_rates_master_id FOREIGN KEY (master_cost_data_prime_cost_rate_id) REFERENCES BS_master_cost_data_prime_cost_rates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_rates_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_rates_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_prime_cost_rates_cost_data_id_BS_cost_data_id FOREIGN KEY (cost_data_id) REFERENCES BS_cost_data(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('20-create_cost_data_prime_cost_rates_table', "Successfully created {$tableName} table!");
    }
}
