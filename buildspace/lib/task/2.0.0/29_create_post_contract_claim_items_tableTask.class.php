<?php

class create_post_contract_claim_items_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-29-create_post_contract_claim_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-29-create_post_contract_claim_items_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-29-create_post_contract_claim_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractClaimItemTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-29-create_post_contract_claim_items_table', 'Table '.PostContractClaimItemTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PostContractClaimItemTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_id BIGINT, description TEXT, type INT NOT NULL, quantity NUMERIC(18,2) DEFAULT 0, uom_id BIGINT, rate NUMERIC(18,5) DEFAULT 0, sequence BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));",
            "CREATE INDEX post_contract_claim_items_type_idx ON ".PostContractClaimItemTable::getInstance()->getTableName()." (type);",
            "CREATE INDEX post_contract_claim_items_id_idx ON ".PostContractClaimItemTable::getInstance()->getTableName()." (id, root_id, lft, rgt);",
            "CREATE INDEX post_contract_claim_items_fk_idx ON ".PostContractClaimItemTable::getInstance()->getTableName()." (post_contract_claim_id, root_id);",
            "ALTER TABLE ".PostContractClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BpBi_16 FOREIGN KEY (post_contract_claim_id) REFERENCES BS_post_contract_claims(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_items_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_items_uom_id_BS_unit_of_measurements_id FOREIGN KEY (uom_id) REFERENCES BS_unit_of_measurements(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimItemTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claim_items_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-29-create_post_contract_claim_items_table', 'Successfully created '.PostContractClaimItemTable::getInstance()->getTableName().' table!');
    }
}