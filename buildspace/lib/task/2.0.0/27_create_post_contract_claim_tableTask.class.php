<?php

class create_post_contract_claim_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-27-create_post_contract_claim_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-27-create_post_contract_claim_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-27-create_post_contract_claim_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractClaimTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-27-create_post_contract_claim_table', 'Table '.PostContractClaimTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PostContractClaimTable::getInstance()->getTableName()." (id BIGSERIAL, description TEXT, claim_certificate_id BIGINT, sequence BIGINT DEFAULT 0 NOT NULL, project_structure_id BIGINT NOT NULL, status BIGINT, type BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX post_contract_claim_unique_idx ON ".PostContractClaimTable::getInstance()->getTableName()." (sequence, project_structure_id, deleted_at);",
            "CREATE INDEX post_contract_claim_id_idx ON ".PostContractClaimTable::getInstance()->getTableName()." (id, project_structure_id, claim_certificate_id);",
            "ALTER TABLE ".PostContractClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_claim_certificate_id FOREIGN KEY (claim_certificate_id) REFERENCES BS_claim_certificates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-27-create_post_contract_claim_table', 'Successfully created '.PostContractClaimTable::getInstance()->getTableName().' table!');
    }
}