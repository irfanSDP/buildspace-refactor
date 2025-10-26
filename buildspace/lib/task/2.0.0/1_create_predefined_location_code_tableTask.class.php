<?php

class create_predefined_location_code_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-1-create_predefined_location_code_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-1-create_predefined_location_code_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-1-create_predefined_location_code_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PreDefinedLocationCodeTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-1-create_predefined_location_code_table', 'Table '.PreDefinedLocationCodeTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PreDefinedLocationCodeTable::getInstance()->getTableName()." (id BIGSERIAL, name VARCHAR(255), priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));",
            "CREATE INDEX pre_defined_location_code_id_idx ON ".PreDefinedLocationCodeTable::getInstance()->getTableName()." (id, root_id, lft, rgt);",
            "ALTER TABLE ".PreDefinedLocationCodeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_pre_defined_location_codes_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PreDefinedLocationCodeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_pre_defined_location_codes_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-1-create_predefined_location_code_table', 'Successfully created '.PreDefinedLocationCodeTable::getInstance()->getTableName().' table!');
    }
}