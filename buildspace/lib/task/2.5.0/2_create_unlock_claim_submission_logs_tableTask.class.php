<?php

class create_unlock_claim_submission_logs_tableTask extends sfBaseTask
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
        $this->name                = '2_5_0-2-create_unlock_claim_submission_logs_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_5_0-2-create_unlock_claim_submission_logs_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-2-create_unlock_claim_submission_logs_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(UnlockClaimSubmissionLogTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_5_0-2-create_unlock_claim_submission_logs_table', 'Table '.UnlockClaimSubmissionLogTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . UnlockClaimSubmissionLogTable::getInstance()->getTableName() . " (id BIGSERIAL, revision_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE INDEX unlock_claim_submission_logs_id_idx ON " . UnlockClaimSubmissionLogTable::getInstance()->getTableName() . " (id);",
            "CREATE INDEX unlock_claim_submission_logs_fk_idx ON " . UnlockClaimSubmissionLogTable::getInstance()->getTableName() . " (revision_id);",
            "ALTER TABLE " . UnlockClaimSubmissionLogTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_unlock_claim_submission_logs_revision_id_fk FOREIGN KEY (revision_id) REFERENCES BS_project_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . UnlockClaimSubmissionLogTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_unlock_claim_submission_logs_updated_by_fk FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . UnlockClaimSubmissionLogTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_unlock_claim_submission_logs_created_by_fk FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_5_0-2-create_unlock_claim_submission_logs_table', 'Successfully created '.UnlockClaimSubmissionLogTable::getInstance()->getTableName().' table!');
    }
}
