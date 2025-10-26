<?php

class create_imported_variation_order_claim_items_tableTask extends sfBaseTask
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
        $this->name                = '2_4_0-3-create_imported_variation_order_claim_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_4_0-3-create_imported_variation_order_claim_items_table|INFO] task does things.
Call it with:

  [php symfony 2_4_0-3-create_imported_variation_order_claim_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ImportedVariationOrderClaimItemTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_4_0-3-create_imported_variation_order_claim_items_table', 'Table '.ImportedVariationOrderClaimItemTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " (id BIGSERIAL, revision_id BIGINT NOT NULL, imported_variation_order_item_id BIGINT NOT NULL, up_to_date_quantity NUMERIC(18,2) DEFAULT 0, up_to_date_amount NUMERIC(18,5) DEFAULT 0, up_to_date_percentage NUMERIC(18,2) DEFAULT 0, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX imported_vo_claims_items_unique_idx ON " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " (revision_id, imported_variation_order_item_id);",
            "CREATE INDEX imported_vo_claim_items_id_idx ON " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " (id, revision_id);",
            "CREATE INDEX imported_vo_claim_items_fk_idx ON " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " (revision_id, imported_variation_order_item_id);",
            "ALTER TABLE " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " ADD CONSTRAINT imported_vo_claim_items_revision_id_fk FOREIGN KEY (revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " ADD CONSTRAINT imported_vo_claim_items_imported_vo_item_id_fk FOREIGN KEY (imported_variation_order_item_id) REFERENCES BS_variation_order_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_4_0-3-create_imported_variation_order_claim_items_table', 'Successfully created '.ImportedVariationOrderClaimItemTable::getInstance()->getTableName().' table!');
    }
}
