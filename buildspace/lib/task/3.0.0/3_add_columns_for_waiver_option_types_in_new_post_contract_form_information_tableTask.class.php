<?php

class add_columns_for_waiver_option_types_in_new_post_contract_form_information_tableTask extends sfBaseTask
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
    $this->name             = '3_0_0-3-add_columns_for_waiver_option_types_in_new_post_contract_form_information_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_0_0-3-add_columns_for_waiver_option_types_in_new_post_contract_form_information_table|INFO] task does things.
Call it with:

  [php symfony 3_0_0-3-add_columns_for_waiver_option_types_in_new_post_contract_form_information_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName = strtolower(NewPostContractFormInformationTable::getInstance()->getTableName());
    $columnNames = ['e_tender_waiver_option_type', 'e_auction_waiver_option_type'];

    foreach($columnNames as $columnName)
    {
      $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '".$tableName."' and column_name = '".$columnName."');");

      $stmt->execute();
            
      $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

      if($columnExists)
      {
          $this->logSection('3_0_0-3-add_columns_for_waiver_option_types_in_new_post_contract_form_information_table', 'Column ' . $columnName . ' already exists in ' . $tableName . ' table!');
      }
      else
      {
          $stmt = $con->prepare("ALTER TABLE " . $tableName . " ADD COLUMN " . $columnName . " SMALLINT");

          $stmt->execute();

          $this->logSection('3_0_0-3-add_columns_for_waiver_option_types_in_new_post_contract_form_information_table', 'Successfully added ' . $columnName . ' column in ' . $tableName . ' table!');
      }
    }
  }
}
