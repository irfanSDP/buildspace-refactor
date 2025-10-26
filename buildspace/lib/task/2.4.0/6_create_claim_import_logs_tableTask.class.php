<?php

class create_claim_import_logs_tableTask extends sfBaseTask
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
        $this->name                = '2_4_0-6-create_claim_import_logs_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_4_0-6-create_claim_import_logs_table|INFO] task does things.
Call it with:

  [php symfony 2_4_0-6-create_claim_import_logs_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimImportLogTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_4_0-6-create_claim_import_logs_table', 'Table '.ClaimImportLogTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".ClaimImportLogTable::getInstance()->getTableName()." (id BIGSERIAL, revision_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE INDEX claim_import_logs_id_idx ON ".ClaimImportLogTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX claim_import_logs_fk_idx ON ".ClaimImportLogTable::getInstance()->getTableName()." (revision_id);",
            "ALTER TABLE ".ClaimImportLogTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_import_logs_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimImportLogTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_import_logs_revision_id_BS_project_revisions_id FOREIGN KEY (revision_id) REFERENCES BS_project_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimImportLogTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_import_logs_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_4_0-6-create_claim_import_logs_table', 'Successfully created '.ClaimImportLogTable::getInstance()->getTableName().' table!');
    }
}
