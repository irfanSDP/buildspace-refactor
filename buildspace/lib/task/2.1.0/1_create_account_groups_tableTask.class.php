<?php

class create_account_groups_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_1_0-1-create_account_groups_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-1-create_account_groups_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-1-create_account_groups_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = AccountGroupTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-1-create_account_groups_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, name VARCHAR(255), priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX account_group_id_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_account_groups_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_account_groups_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('2_1_0-1-create_account_groups_table', "Successfully created {$tableName} table!");
    }
}
