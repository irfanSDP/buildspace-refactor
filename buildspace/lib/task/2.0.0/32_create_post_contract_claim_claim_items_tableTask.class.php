<?php

class create_post_contract_claim_claim_items_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-32-create_post_contract_claim_claim_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-32-create_post_contract_claim_claim_items_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-32-create_post_contract_claim_claim_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractClaimClaimItemTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-32-create_post_contract_claim_claim_items_table', 'Table '.PostContractClaimClaimItemTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PostContractClaimClaimItemTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_claim_id BIGINT NOT NULL, post_contract_claim_item_id BIGINT NOT NULL, current_quantity NUMERIC(18,2) DEFAULT 0, current_amount NUMERIC(18,5) DEFAULT 0, current_percentage NUMERIC(18,2) DEFAULT 0, up_to_date_quantity NUMERIC(18,2) DEFAULT 0, up_to_date_amount NUMERIC(18,5) DEFAULT 0, up_to_date_percentage NUMERIC(18,2) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX post_contract_claim_claim_items_unique_idx ON ".PostContractClaimClaimItemTable::getInstance()->getTableName()." (post_contract_claim_claim_id, post_contract_claim_item_id, deleted_at);",
            "CREATE INDEX post_contract_claim_claim_items_id_idx ON ".PostContractClaimClaimItemTable::getInstance()->getTableName()." (id, post_contract_claim_claim_id);",
            "CREATE INDEX post_contract_claim_claim_items_fk_idx ON ".PostContractClaimClaimItemTable::getInstance()->getTableName()." (post_contract_claim_claim_id, post_contract_claim_item_id);",
            "ALTER TABLE ".PostContractClaimClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claim_items_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claim_item_post_contract_claim_item_id FOREIGN KEY (post_contract_claim_item_id) REFERENCES BS_post_contract_claim_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claim_item_post_contract_claim_claim_id FOREIGN KEY (post_contract_claim_claim_id) REFERENCES BS_post_contract_claim_claims(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_claim_item_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-32-create_post_contract_claim_claim_items_table', 'Successfully created '.PostContractClaimClaimItemTable::getInstance()->getTableName().' table!');
    }
}