<?php

class create_claim_certificates_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-8-create_claim_certificates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-8-create_claim_certificates_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-8-create_claim_certificates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificateTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-8-create_claim_certificates_table', 'Table '.ClaimCertificateTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".ClaimCertificateTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_revision_id BIGINT NOT NULL, contractor_submitted_date DATE NOT NULL, site_verified_date DATE NOT NULL, qs_received_date DATE NOT NULL, release_retention_amount NUMERIC(18,5) DEFAULT 0, person_in_charge VARCHAR(255) NOT NULL, valuation_date DATE, due_date DATE NOT NULL, budget_amount NUMERIC(18,5) DEFAULT 0, budget_due_date DATE NOT NULL, tax_percentage NUMERIC(3,2) DEFAULT 0, acc_remarks TEXT, qs_remarks TEXT, status smallint NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX claim_certificates_unique_idx ON ".ClaimCertificateTable::getInstance()->getTableName()." (post_contract_claim_revision_id);",
            "CREATE INDEX claim_certificates_id_idx ON ".ClaimCertificateTable::getInstance()->getTableName()." (id, status);",
            "CREATE INDEX claim_certificates_foreign_keys_idx ON ".ClaimCertificateTable::getInstance()->getTableName()." (post_contract_claim_revision_id);",
            "ALTER TABLE ".ClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificates_post_contract_claim_revision_idd FOREIGN KEY (post_contract_claim_revision_id) REFERENCES ".PostContractClaimRevisionTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificates_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificates_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-8-create_claim_certificates_table', 'Successfully created '.ClaimCertificateTable::getInstance()->getTableName().' table!');
    }
}