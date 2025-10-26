<?php

class create_retention_sum_codes_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_4_0-9-create_retention_sum_codes_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_4_0-9-create_create_retention_sum_codes_table|INFO] task does things.
Call it with:

  [php symfony 2_4_0-9-create_retention_sum_codes_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(RetentionSumCodeTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_4_0-9-create_retention_sum_codes_table', 'Table '.RetentionSumCodeTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".RetentionSumCodeTable::getInstance()->getTableName()." (id BIGSERIAL, code VARCHAR(50) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX retention_sum_codes_code_unique_idx ON ".RetentionSumCodeTable::getInstance()->getTableName()." (code, deleted_at);",
            "CREATE INDEX retention_sum_codes_id_idx ON ".RetentionSumCodeTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".RetentionSumCodeTable::getInstance()->getTableName()." ADD CONSTRAINT retention_sum_codes_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".RetentionSumCodeTable::getInstance()->getTableName()." ADD CONSTRAINT retention_sum_codes_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "INSERT INTO ".RetentionSumCodeTable::getInstance()->getTableName()." (code, created_at, updated_at) VALUES ('RET001', NOW(), NOW())"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }
        
        return $this->logSection('2_4_0-9-create_retention_sum_codes_table', 'Successfully created '.RetentionSumCodeTable::getInstance()->getTableName().' table!');
    }
}
