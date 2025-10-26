<?php

class create_post_contract_imported_standard_claim_tableTask extends sfBaseTask
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
        $this->name                = '2_3_0-4-create_post_contract_imported_standard_claim_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_3_0-4-create_post_contract_imported_standard_claim_table|INFO] task does things.
Call it with:

  [php symfony 2_3_0-4-create_post_contract_imported_standard_claim_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractImportedStandardClaimTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_3_0-4-create_post_contract_imported_standard_claim_table', 'Table '.PostContractImportedStandardClaimTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " (id BIGSERIAL, revision_id BIGINT NOT NULL, claim_type_ref_id BIGINT NOT NULL, bill_item_id BIGINT NOT NULL, current_percentage NUMERIC(18,5) DEFAULT 0, current_amount NUMERIC(18,5) DEFAULT 0, up_to_date_percentage NUMERIC(18,5) DEFAULT 0, up_to_date_amount NUMERIC(18,5) DEFAULT 0, up_to_date_qty NUMERIC(18,5) DEFAULT 0, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX post_contract_imported_standard_claim_unique_idx ON " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " (claim_type_ref_id, bill_item_id, revision_id);",
            "CREATE INDEX post_contract_imported_standard_claim_revision_id_idx ON " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " (revision_id);",
            "CREATE INDEX post_contract_imported_standard_claim_id_idx ON " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " (id);",
            "CREATE INDEX post_contract_imported_standard_claim_fk_idx ON " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " (claim_type_ref_id, bill_item_id, revision_id);",
            "ALTER TABLE " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_post_contract_imported_standard_claim_revision_id_fk FOREIGN KEY (revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_post_contract_imported_standard_claim_claim_type_ref_id_fk FOREIGN KEY (claim_type_ref_id) REFERENCES BS_post_contract_standard_claim_type_reference(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_post_contract_imported_standard_claim_bill_item_id_fk FOREIGN KEY (bill_item_id) REFERENCES BS_bill_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_3_0-4-create_post_contract_imported_standard_claim_table', 'Successfully created '.PostContractImportedStandardClaimTable::getInstance()->getTableName().' table!');
    }
}
