<?php

class create_company_group_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_1_0-3-create_company_group_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_1_0-3-create_company_group_table|INFO] task does things.
Call it with:

  [php symfony legacy-2_1_0-3-create_company_group_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '" . strtolower(CompanyGroupTable::getInstance()->getTableName()) . "');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('legacy-2_1_0-3-create_company_group_table', 'Table ' . CompanyGroupTable::getInstance()->getTableName() . ' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . CompanyGroupTable::getInstance()->getTableName() . " (company_id BIGINT NOT NULL, group_id BIGINT NOT NULL);",
            "CREATE UNIQUE INDEX company_group_fk_idx ON ".CompanyGroupTable::getInstance()->getTableName()." (company_id, group_id);",
            "CREATE INDEX company_group_idx ON ".CompanyGroupTable::getInstance()->getTableName()." (id, company_id, group_id);",
            "ALTER TABLE " . CompanyGroupTable::getInstance()->getTableName() . " ADD CONSTRAINT company_group_company_id_foreign FOREIGN KEY (company_id) REFERENCES " . CompanyTable::getInstance()->getTableName() . "(id) ON DELETE CASCADE;",
            "ALTER TABLE " . CompanyGroupTable::getInstance()->getTableName() . " ADD CONSTRAINT company_group_group_id_foreign FOREIGN KEY (group_id) REFERENCES " . sfGuardGroupTable::getInstance()->getTableName() . "(id) ON DELETE CASCADE",
        );

        foreach($queries as $query)
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('legacy-2_1_0-3-create_company_group_table', 'Successfully added table ' . CompanyGroupTable::getInstance()->getTableName() . '!');
    }
}