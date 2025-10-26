<?php

class add_display_tax_amount_in_claim_certificate_print_settings_tableTaskTask extends sfBaseTask
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
  ));

    $this->namespace        = 'buildspace';
    $this->name             = '3_7_5-2_add_display_tax_amount_in_claim_certificate_print_settings_tableTask';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_7_5-2_add_display_tax_amount_in_claim_certificate_print_settings_tableTask|INFO] task does things.
Call it with:

  [php symfony 3_7_5-2_add_display_tax_amount_in_claim_certificate_print_settings_tableTask|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    // check for table existence, if not then proceed with insertion query
    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
    AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'display_tax_amount');");

    $stmt->execute();

    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $columnExists )
    {
        return $this->logSection('3_7_3-1_add_display_tax_amount_column_in_claim_certificate_print_settings_table', 'Column display_tax_amount already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
    }

    $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN display_tax_amount BOOLEAN DEFAULT 'false' NOT NULL");
    
    $stmt->execute();

    return $this->logSection('3_7_3-1_add_display_tax_amount_column_in_claim_certificate_print_settings_table', 'Successfully added display_tax_amount column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
  }
}
