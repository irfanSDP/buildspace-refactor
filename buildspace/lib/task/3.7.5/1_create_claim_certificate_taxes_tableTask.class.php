<?php

class create_claim_certificate_taxes_tableTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
      // add your own options here
  ));

    $this->namespace        = 'buildspace';
    $this->name             = '3_7_5-create_claim_certificate_taxes_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [create_claim_certificate_taxes_table|INFO] task does things.
Call it with:

  [php symfony create_claim_certificate_taxes_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
      // initialize the database connection
      $databaseManager = new sfDatabaseManager($this->configuration);
      $con = $databaseManager->getDatabase($options['connection'])->getConnection();

      $tableName = ClaimCertificateTaxTable::getInstance()->getTableName();

      // check for table existence, if not then proceed with insertion query
      $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
      AND table_name = '".strtolower($tableName)."');");

      $stmt->execute();

      $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

      if ( $isTableExists )
      {
          return $this->logSection('3_7_5-create_claim_certificate_taxes_table', "Table {$tableName} already exists!");
      }

      $queries = array(
          "CREATE TABLE {$tableName} (id BIGSERIAL, tax NUMERIC(18,5) DEFAULT 0, description text, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id));",
          "CREATE INDEX claim_certificate_taxes_idx ON {$tableName} (id);",
      );

      foreach ($queries as $query )
      {
          $stmt = $con->prepare($query);
          $stmt->execute();
      }

      return $this->logSection('3_7_5-create_claim_certificate_taxes_table', "Successfully created {$tableName} table!");
  }
}
