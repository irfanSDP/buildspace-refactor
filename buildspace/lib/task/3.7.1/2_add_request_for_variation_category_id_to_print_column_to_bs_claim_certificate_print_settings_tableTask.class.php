<?php

class add_request_for_variation_category_id_to_print_column_to_bs_claim_certificate_print_settings_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));


    $this->namespace        = 'buildspace';
    $this->name             = '3_7_1-2_add_request_for_variation_category_id_to_print_column_to_bs_claim_certificate_print_settings_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_7_1-2_add_request_for_variation_category_id_to_print_column_to_bs_claim_certificate_print_settings_table|INFO] task does things.
Call it with:

  [php symfony 3_7_1-2_add_request_for_variation_category_id_to_print_column_to_bs_claim_certificate_print_settings_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'request_for_variation_category_id_to_print');");

    $stmt->execute();

    $remarksColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $remarksColumnExists )
    {
        $this->logSection('3_7_1-2_add_request_for_variation_category_id_to_print_column_to_bs_claim_certificate_print_settings_table', 'Column request_for_variation_category_id_to_print already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
    }
    else
    {
        $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN request_for_variation_category_id_to_print INTEGER DEFAULT NULL");

        $stmt->execute();

        $this->logSection('3_7_1-2_add_request_for_variation_category_id_to_print_column_to_bs_claim_certificate_print_settings_table', 'Successfully added request_for_variation_category_id_to_print column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
    }
  }
}
