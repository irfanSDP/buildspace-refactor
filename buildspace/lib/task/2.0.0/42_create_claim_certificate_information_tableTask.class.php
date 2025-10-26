<?php

class create_claim_certificate_information_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace', 'backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-42-create_claim_certificate_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-42-create_claim_certificate_information_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-42-create_claim_certificate_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificateInformationTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            $this->populateTable($con);
            return $this->logSection('2_0_0-42-create_claim_certificate_information_table', 'Table '.ClaimCertificateInformationTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . ClaimCertificateInformationTable::getInstance()->getTableName() . " (id BIGSERIAL, claim_certificate_id BIGINT NOT NULL, paid BOOLEAN DEFAULT 'false' NOT NULL, approved_amount NUMERIC(18,5) DEFAULT 0, paid_amount NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX claim_certificate_information_unique_idx ON " . ClaimCertificateInformationTable::getInstance()->getTableName() . " (claim_certificate_id);",
            "CREATE INDEX claim_certificate_information_id_idx ON " . ClaimCertificateInformationTable::getInstance()->getTableName() . " (id);",
            "ALTER TABLE " . ClaimCertificateInformationTable::getInstance()->getTableName() . " ADD CONSTRAINT BcBi_16 FOREIGN KEY (claim_certificate_id) REFERENCES BS_claim_certificates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        $this->populateTable();

        return $this->logSection('2_0_0-42-create_claim_certificate_information_table', 'Successfully created '.ClaimCertificateInformationTable::getInstance()->getTableName().' table!');
    }

    protected function populateTable()
    {
        $this->logSection('2_0_0-42-create_claim_certificate_information_table', 'Populating table '.ClaimCertificateInformationTable::getInstance()->getTableName().'...');

        $claimCertificates = DoctrineQuery::create()->select('id')
            ->from('ClaimCertificate cc')
            ->where('cc.status = ?',ClaimCertificate::STATUS_TYPE_APPROVED)
            ->execute();
   
        foreach($claimCertificates as $claimCertificate)
        {
            $claimCertificate->recalculateClaimCertInformation();
        }
    }
}
