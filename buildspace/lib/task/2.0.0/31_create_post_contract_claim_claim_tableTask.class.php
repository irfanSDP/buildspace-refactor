<?php

class create_post_contract_claim_claim_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-31-create_post_contract_claim_claim_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-31-create_post_contract_claim_claim_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-31-create_post_contract_claim_claim_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractClaimClaimTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-31-create_post_contract_claim_claim_table', 'Table '.PostContractClaimClaimTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PostContractClaimClaimTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_id BIGINT NOT NULL, claim_certificate_id BIGINT, status SMALLINT DEFAULT 1 NOT NULL, revision INT DEFAULT 1 NOT NULL, is_viewing BOOLEAN DEFAULT 'true' NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX post_contract_claim_claims_revision_unique_idx ON ".PostContractClaimClaimTable::getInstance()->getTableName()." (revision, post_contract_claim_id, deleted_at);",
            "CREATE INDEX post_contract_claim_claims_id_idx ON ".PostContractClaimClaimTable::getInstance()->getTableName()." (id, post_contract_claim_id);",
            "ALTER TABLE ".PostContractClaimClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claims_post_contract_claim_id FOREIGN KEY (post_contract_claim_id) REFERENCES BS_post_contract_claims(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claims_claim_certificate_id FOREIGN KEY (claim_certificate_id) REFERENCES BS_claim_certificates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claims_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claims_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-31-create_post_contract_claim_claim_table', 'Successfully created '.PostContractClaimClaimTable::getInstance()->getTableName().' table!');
    }
}