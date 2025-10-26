<?php

class create_variation_order_claim_certificates_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-20-create_variation_order_claim_certificates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-20-create_variation_order_claim_certificates_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-20-create_variation_order_claim_certificates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderClaimCertificateTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-20-create_variation_order_claim_certificates_table', 'Table '.VariationOrderClaimCertificateTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." (variation_order_id BIGINT, claim_certificate_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(variation_order_id));",
            "CREATE UNIQUE INDEX variation_order_claim_certificates_unique_idx ON ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." (variation_order_id, claim_certificate_id);",
            "CREATE INDEX variation_order_claim_certificates_fk_idx ON ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." (variation_order_id, claim_certificate_id);",
            "ALTER TABLE ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_variation_order_claim_certificates_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_variation_order_claim_certificates_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_variation_order_claim_certificates_claim_certificate_id FOREIGN KEY (claim_certificate_id) REFERENCES ".ClaimCertificateTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-20-create_variation_order_claim_certificates_table', 'Successfully created '.VariationOrderClaimCertificateTable::getInstance()->getTableName().' table!');
    }
}
