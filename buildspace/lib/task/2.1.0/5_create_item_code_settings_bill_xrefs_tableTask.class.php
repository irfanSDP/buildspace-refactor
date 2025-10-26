<?php

class create_item_code_settings_bill_xrefs_tableTask extends sfBaseTask
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
        $this->name             = '2_1_0-5-create_item_code_settings_bill_xrefs_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-5-create_item_code_settings_bill_xrefs_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-5-create_item_code_settings_bill_xrefs_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // add your code here
        $tableName = 'bs_item_code_setting_bill_xrefs';

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-5-create_item_code_settings_bill_xrefs_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, item_code_setting_id BIGINT NOT NULL, bill_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX item_code_setting_bill_xref_unique_idx ON {$tableName} (item_code_setting_id, bill_id);",
            "CREATE INDEX item_code_setting_bill_xref_id_idx ON {$tableName} (id, item_code_setting_id, bill_id);",
            "CREATE INDEX item_code_setting_bill_xref_fx_idx ON {$tableName} (item_code_setting_id, bill_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_settings_bill_xrefs_fk_id FOREIGN KEY (item_code_setting_id) REFERENCES BS_item_code_settings(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_setting_bill_xrefs_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_setting_bill_xrefs_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_item_code_setting_bill_xrefs_bill_id_BS_project_structures_id FOREIGN KEY (bill_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('2_1_0-5-create_item_code_settings_bill_xrefs_table', "Successfully created {$tableName} table!");
    }
}
