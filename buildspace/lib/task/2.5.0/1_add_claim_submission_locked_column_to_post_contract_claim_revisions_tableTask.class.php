<?php

class add_claim_submission_locked_column_to_post_contract_claim_revisions_tableTask extends sfBaseTask
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
        $this->name                = '2_5_0-1-add_claim_submission_locked_column_to_post_contract_claim_revisions_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_5_0-1-add_claim_submission_locked_column_to_post_contract_claim_revisions_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-1-add_claim_submission_locked_column_to_post_contract_claim_revisions_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractClaimRevisionTable::getInstance()->getTableName())."' and column_name ='claim_submission_locked');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_5_0-1-add_claim_submission_locked_column_to_post_contract_claim_revisions_table', 'Column claim_submission_locked already exists in '.PostContractClaimRevisionTable::getInstance()->getTableName().' table!');
        }

        $queries = array(
            "ALTER TABLE ".PostContractClaimRevisionTable::getInstance()->getTableName()." ADD COLUMN claim_submission_locked BOOLEAN DEFAULT FALSE",
            "UPDATE ".PostContractClaimRevisionTable::getInstance()->getTableName()." SET claim_submission_locked = TRUE WHERE locked_status = TRUE",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_5_0-1-add_claim_submission_locked_column_to_post_contract_claim_revisions_table', 'Successfully added column claim_submission_locked in '.PostContractClaimRevisionTable::getInstance()->getTableName().' table!');
    }
}
