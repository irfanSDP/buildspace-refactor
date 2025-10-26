<?php

class create_location_progress_claims_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-23-create_location_progress_claims_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-23-create_location_progress_claims_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-23-create_location_progress_claims_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(LocationProgressClaimTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-23-create_location_progress_claims_table', 'Table '.LocationProgressClaimTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".LocationProgressClaimTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_revision_id BIGINT NOT NULL, location_bill_item_quantity_prorates_id BIGINT NOT NULL, current_quantity NUMERIC(18,2) DEFAULT 0, current_percentage NUMERIC(18,2) DEFAULT 0, up_to_date_quantity NUMERIC(18,2) DEFAULT 0, up_to_date_percentage NUMERIC(18,2) DEFAULT 0, remarks text, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX location_progress_claims_unique_idx ON ".LocationProgressClaimTable::getInstance()->getTableName()." (post_contract_claim_revision_id, location_bill_item_quantity_prorates_id);",
            "CREATE INDEX location_progress_claims_idx ON ".LocationProgressClaimTable::getInstance()->getTableName()." (id, post_contract_claim_revision_id, location_bill_item_quantity_prorates_id);",
            "ALTER TABLE ".LocationProgressClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_progress_claims_post_contract_claim_revision_id FOREIGN KEY (post_contract_claim_revision_id) REFERENCES ".PostContractClaimRevisionTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationProgressClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_progress_claims_location_bill_item_quantity_prorates_id FOREIGN KEY (location_bill_item_quantity_prorates_id) REFERENCES ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationProgressClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_progress_claims_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationProgressClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_progress_claims_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-23-create_location_progress_claims_table', 'Successfully created '.LocationProgressClaimTable::getInstance()->getTableName().' table!');
    }
}
