<?php

class create_claim_certificate_payment_logs_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-15-create_claim_certificate_payment_logs_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-15-create_claim_certificate_payment_logs_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-15-create_claim_certificate_payment_logs_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePaymentLogTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-15-create_claim_certificate_payment_logs_table', 'Table '.ClaimCertificatePaymentLogTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." (id BIGSERIAL, claim_certificate_id BIGINT NOT NULL, amount NUMERIC(18,5) DEFAULT 0, remarks TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE INDEX claim_certificate_payment_log_id_idx ON ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX claim_certificate_payment_log_fk_idx ON ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." (claim_certificate_id);",
            "ALTER TABLE ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificates_payment_log_claim_certificate_id FOREIGN KEY (claim_certificate_id) REFERENCES ".ClaimCertificateTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_payment_logs_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_payment_logs_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-15-create_claim_certificate_payment_logs_table', 'Successfully created '.ClaimCertificatePaymentLogTable::getInstance()->getTableName().' table!');
    }
}