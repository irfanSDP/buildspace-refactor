<?php

class create_request_for_variation_items_claim_certificates_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_1_0-6-create_request_for_variation_items_claim_certificates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_1_0-6-create_request_for_variation_items_claim_certificates_table|INFO] task does things.
Call it with:

  [php symfony 2_1_0-6-create_request_for_variation_items_claim_certificates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(RequestForVariationItemClaimCertificateTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-6-create_request_for_variation_items_claim_certificates_table', 'Table '.RequestForVariationItemClaimCertificateTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." (id BIGSERIAL, variation_order_item_id BIGINT NOT NULL, claim_certificate_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX rfv_items_claim_certs_unique_idx ON ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." (variation_order_item_id, claim_certificate_id);",
            "CREATE INDEX rfv_items_claim_certs_id_idx ON ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." (id, variation_order_item_id, claim_certificate_id);",
            "CREATE INDEX rfv_items_claim_certs_fk_idx ON ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." (variation_order_item_id, claim_certificate_id);",
            "ALTER TABLE ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_items_claim_certificates_vo_item_id_BS_variation_order_items_id FOREIGN KEY (variation_order_item_id) REFERENCES ".VariationOrderItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_items_claim_certificates_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_items_claim_certificates_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_request_for_variation_items_claim_certificates_claim_cert_id_BS_claim_certificates_id FOREIGN KEY (claim_certificate_id) REFERENCES ".ClaimCertificateTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_1_0-6-create_request_for_variation_items_claim_certificates_table', 'Successfully created '.RequestForVariationItemClaimCertificateTable::getInstance()->getTableName().' table!');
    }
}