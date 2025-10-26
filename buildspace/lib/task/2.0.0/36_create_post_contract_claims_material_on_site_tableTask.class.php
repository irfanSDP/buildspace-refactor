<?php

class create_post_contract_claims_material_on_site_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-36-create_post_contract_claims_material_on_site_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-36-create_post_contract_claims_material_on_site_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-36-create_post_contract_claims_material_on_site_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractClaimMaterialOnSiteTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-36-create_post_contract_claims_material_on_site_table', 'Table '.PostContractClaimMaterialOnSiteTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_item_id BIGINT NOT NULL, reduction_percentage NUMERIC(18,2) DEFAULT 0, reduction_amount NUMERIC(18,5) DEFAULT 0, final_amount NUMERIC(18,2) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX post_contract_claims_material_on_site_unique_idx ON ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." (post_contract_claim_item_id, deleted_at);",
            "CREATE INDEX post_contract_claims_material_on_site_id_idx ON ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_material_on_site_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_material_on_site_post_contract_claim_item_id FOREIGN KEY (post_contract_claim_item_id) REFERENCES BS_post_contract_claim_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." ADD CONSTRAINT BS_post_contract_claims_material_on_site_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-36-create_post_contract_claims_material_on_site_table', 'Successfully created '.PostContractClaimMaterialOnSiteTable::getInstance()->getTableName().' table!');
    }
}