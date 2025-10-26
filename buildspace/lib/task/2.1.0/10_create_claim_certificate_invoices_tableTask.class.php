<?php

class create_claim_certificate_invoices_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_1_0-10-create_claim_certificate_invoices_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-10-create_claim_certificate_invoices_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-10-create_claim_certificate_invoices_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificateInvoiceTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-10-create_claim_certificate_invoices_table', "Table ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." already exists!");
        }

        $queries = array(
            "CREATE TABLE ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." (id BIGSERIAL, claim_certificate_id BIGINT NOT NULL, invoice_date DATE NOT NULL, invoice_number VARCHAR(200) NOT NULL, post_month VARCHAR(100) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX claim_certificate_invoices_unique_idx ON ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." (claim_certificate_id);",
            "CREATE INDEX claim_certificate_invoices_id_idx ON ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." (id, created_by);",
            "CREATE INDEX claim_certificate_invoices_fk_idx ON ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." (claim_certificate_id);",
            "ALTER TABLE ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_invoices_claim_certificate_id_BS_claim_certificates_id FOREIGN KEY (claim_certificate_id) REFERENCES ".ClaimCertificateTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_invoices_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_invoices_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('2_1_0-10-create_claim_certificate_invoices_table', "Successfully created ".ClaimCertificateInvoiceTable::getInstance()->getTableName()." table!");
    }
}
