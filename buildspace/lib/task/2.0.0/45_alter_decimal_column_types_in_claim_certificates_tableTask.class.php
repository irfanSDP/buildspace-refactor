<?php

class alter_decimal_column_types_in_claim_certificates_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-45-alter_decimal_column_types_in_claim_certificates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-45-alter_decimal_column_types_in_claim_certificates_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-45-alter_decimal_column_types_in_claim_certificates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = ClaimCertificateTable::getInstance()->getTableName();

        $stmt = $con->prepare("ALTER TABLE {$tableName} ALTER COLUMN release_retention_percentage TYPE NUMERIC(18,5);");

        $stmt->execute();

        $stmt = $con->prepare("ALTER TABLE {$tableName} ALTER COLUMN retention_tax_percentage TYPE NUMERIC(18,5);");

        $stmt->execute();

        $stmt = $con->prepare("ALTER TABLE {$tableName} ALTER COLUMN tax_percentage TYPE NUMERIC(18,2);");

        $stmt->execute();

        return $this->logSection('2_0_0-45-alter_decimal_column_types_in_claim_certificates_table', "Successfully altered table {$tableName}!");
    }
}
