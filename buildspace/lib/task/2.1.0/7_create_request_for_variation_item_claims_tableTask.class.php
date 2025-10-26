<?php

class create_request_for_variation_item_claims_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_1_0-7-create_request_for_variation_item_claims_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_1_0-7-create_request_for_variation_item_claims_table|INFO] task does things.
Call it with:

  [php symfony 2_1_0-7-create_request_for_variation_item_claims_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(RequestForVariationItemClaimTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-7-create_request_for_variation_item_claims_table', 'Table '.RequestForVariationItemClaimTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".RequestForVariationItemClaimTable::getInstance()->getTableName()." (id BIGSERIAL, variation_order_item_id BIGINT NOT NULL, percentage NUMERIC(18,5) DEFAULT 0, amount NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX rfv_item_claims_unique_idx ON ".RequestForVariationItemClaimTable::getInstance()->getTableName()." (variation_order_item_id);",
            "CREATE INDEX rfv_item_claims_id_idx ON ".RequestForVariationItemClaimTable::getInstance()->getTableName()." (id, variation_order_item_id);",
            "CREATE INDEX rfv_item_claims_fk_idx ON ".RequestForVariationItemClaimTable::getInstance()->getTableName()." (variation_order_item_id);",
            "ALTER TABLE ".RequestForVariationItemClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_item_claims_vo_item_id_BS_variation_order_items_id FOREIGN KEY (variation_order_item_id) REFERENCES ".VariationOrderItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".RequestForVariationItemClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_item_claims_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".RequestForVariationItemClaimTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_item_claims_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_1_0-7-create_request_for_variation_item_claims_table', 'Successfully created '.RequestForVariationItemClaimTable::getInstance()->getTableName().' table!');
    }
}