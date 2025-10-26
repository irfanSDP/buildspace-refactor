<?php

class add_release_retention_percentage_column_to_claim_certificates_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-39-add_release_retention_percentage_column_to_claim_certificates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-39-add_release_retention_percentage_column_to_claim_certificates_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-39-add_release_retention_percentage_column_to_claim_certificates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificateTable::getInstance()->getTableName())."' and column_name ='release_retention_percentage');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            return $this->logSection('2_0_0-39-add_release_retention_percentage_column_to_claim_certificates_table', 'Column release_retention_percentage already exists in '.ClaimCertificateTable::getInstance()->getTableName().' table!');
        }

        $queries = array(
            "ALTER TABLE ".ClaimCertificateTable::getInstance()->getTableName()." ADD COLUMN release_retention_percentage NUMERIC(18,2) DEFAULT 0"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-39-add_release_retention_percentage_column_to_claim_certificates_table', 'Successfully added column release_retention_percentage in '.ClaimCertificateTable::getInstance()->getTableName().' table!');
    }
}
