<?php

class create_master_cost_data_particular_assigned_groups_tableTask extends sfBaseTask
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
        $this->name                = '23-create_master_cost_data_particular_assigned_groups_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [23-create_master_cost_data_particular_assigned_groups_table|INFO] task does things.
Call it with:

  [php symfony 23-create_master_cost_data_particular_assigned_groups_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = MasterCostDataParticularAssignedGroupTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('23-create_master_cost_data_particular_assigned_groups_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE BS_master_cost_data_particular_assigned_groups (id BIGSERIAL, master_cost_data_particular_id BIGINT NOT NULL, group_name VARCHAR(50) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX master_cost_data_particular_assigned_group_unique_idx ON {$tableName} (master_cost_data_particular_id, group_name);",
            "CREATE INDEX master_cost_data_particular_assigned_group_id_idx ON {$tableName} (id);",
            "CREATE INDEX master_cost_data_particular_assigned_group_fk_idx ON {$tableName} (master_cost_data_particular_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_particular_assigned_groups_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_particular_assigned_groups_particular_id FOREIGN KEY (master_cost_data_particular_id) REFERENCES BS_master_cost_data_particulars(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_master_cost_data_particular_assigned_groups_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('23-create_master_cost_data_particular_assigned_groups_table', "Successfully created {$tableName} table!");
    }
}
