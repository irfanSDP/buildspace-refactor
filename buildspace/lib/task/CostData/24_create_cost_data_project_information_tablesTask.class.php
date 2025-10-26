<?php

class create_cost_data_project_information_tablesTask extends sfBaseTask
{
    protected $con;

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'costdata';
        $this->name                = '24-create_cost_data_project_information_tables';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [24-create_cost_data_project_information_tables|INFO] task does things.
Call it with:

  [php symfony 24-create_cost_data_project_information_tables|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $this->con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->createMasterCostDataProjectInformationTable();
        $this->createCostDataProjectInformationTable();
    }

    protected function createMasterCostDataProjectInformationTable()
    {
        $tableName = MasterCostDataProjectInformationTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $this->con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('24-create_cost_data_project_information_tables', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, master_cost_data_id BIGINT NOT NULL, parent_id BIGINT, description TEXT, priority BIGINT NOT NULL, level BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX master_cost_data_project_information_idx ON {$tableName} (master_cost_data_id, parent_id);",
            "CREATE INDEX master_cost_data_project_information_id_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_project_information_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_project_information_parent_id FOREIGN KEY (parent_id) REFERENCES BS_master_cost_data_project_information(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_project_information_master_cost_data_id FOREIGN KEY (master_cost_data_id) REFERENCES BS_master_cost_data(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_project_information_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $this->con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('24-create_cost_data_project_information_tables', "Successfully created {$tableName} table!");
    }

    protected function createCostDataProjectInformationTable()
    {
        $tableName = CostDataProjectInformationTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $this->con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('24-create_cost_data_project_information_tables', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, cost_data_id BIGINT NOT NULL, master_cost_data_project_information_id BIGINT NOT NULL, description TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX cost_data_project_information_id_idx ON {$tableName} (id);",
            "CREATE INDEX cost_data_project_information_fk_idx ON {$tableName} (cost_data_id, master_cost_data_project_information_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_project_information_master_project_information_id FOREIGN KEY (master_cost_data_project_information_id) REFERENCES BS_master_cost_data_project_information(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_project_information_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_project_information_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_cost_data_project_information_cost_data_id_BS_cost_data_id FOREIGN KEY (cost_data_id) REFERENCES BS_cost_data(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $this->con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('24-create_cost_data_project_information_tables', "Successfully created {$tableName} table!");
    }
}
