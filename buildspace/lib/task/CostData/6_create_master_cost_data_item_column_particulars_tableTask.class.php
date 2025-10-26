<?php

class create_master_cost_data_item_column_particulars_tableTask extends sfBaseTask
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
        $this->name                = '6-create_master_cost_data_item_column_particulars_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [6-create_master_cost_data_item_column_particulars_table|INFO] task does things.
Call it with:

  [php symfony 6-create_master_cost_data_item_column_particulars_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = MasterCostDataItemColumnParticularTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('6-create_master_cost_data_item_column_particulars_table', "Table $tableName already exists!");
        }

        $queries = array(
            "CREATE TABLE $tableName (id BIGSERIAL, master_cost_data_item_column_id BIGINT NOT NULL, master_cost_data_particular_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX master_cost_data_item_column_particular_unique_idx ON $tableName (master_cost_data_item_column_id, master_cost_data_particular_id);",
            "CREATE INDEX master_cost_data_item_column_particular_id_idx ON $tableName (id);",
            "ALTER TABLE $tableName ADD CONSTRAINT master_cost_data_item_column_particular_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE $tableName ADD CONSTRAINT master_cost_data_item_column_particular_master_cost_data_particular_id FOREIGN KEY (master_cost_data_particular_id) REFERENCES BS_master_cost_data_particulars(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE $tableName ADD CONSTRAINT master_cost_data_item_column_particular_master_cost_data_item_column_id FOREIGN KEY (master_cost_data_item_column_id) REFERENCES BS_master_cost_data_item_columns(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE $tableName ADD CONSTRAINT master_cost_data_item_column_particular_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('6-create_master_cost_data_item_column_particulars_table', "Successfully created $tableName table!");
    }
}
