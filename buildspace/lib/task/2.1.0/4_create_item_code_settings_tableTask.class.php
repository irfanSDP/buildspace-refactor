<?php

class create_item_code_settings_tableTask extends sfBaseTask
{
    protected function configure()
    {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_1_0-4-create_item_code_settings_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-4-create_item_code_settings_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-4-create_item_code_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // add your code here
        $tableName = ItemCodeSettingTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-4-create_item_code_settings_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, project_structure_id BIGINT NOT NULL, account_group_id BIGINT NOT NULL, account_code_id BIGINT, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX item_code_settings_id_idx ON {$tableName} (id, project_structure_id, account_code_id);",
            "CREATE INDEX item_code_settings_fx_idx ON {$tableName} (project_structure_id, account_code_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_settings_fk_id FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_settings_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_settings_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_settings_account_code_id_BS_account_codes_id FOREIGN KEY (account_code_id) REFERENCES BS_account_codes(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('2_1_0-4-create_item_code_settings_table', "Successfully created {$tableName} table!");
    }
}
