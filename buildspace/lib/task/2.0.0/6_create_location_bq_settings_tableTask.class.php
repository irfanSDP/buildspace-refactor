<?php

class create_location_bq_settings_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-6-create_location_bs_settings_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-6-create_location_bq_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-6-create_location_bq_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(LocationBQSettingTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-6-create_location_bq_settings_table', 'Table '.LocationBQSettingTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".LocationBQSettingTable::getInstance()->getTableName()." (bill_column_setting_id BIGINT, use_original_qty BOOLEAN DEFAULT 'true', created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(bill_column_setting_id));",
            "CREATE INDEX location_bq_settings_id_idx ON ".LocationBQSettingTable::getInstance()->getTableName()." (bill_column_setting_id);",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_bq_settings_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_bq_settings_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-6-create_location_bq_settings_table', 'Successfully created '.LocationBQSettingTable::getInstance()->getTableName().' table!');
    }
}