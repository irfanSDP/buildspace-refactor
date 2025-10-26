<?php

class drop_bs_item_code_setting_bill_xrefs_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '2_5_0-4-drop_bs_item_code_setting_bill_xrefs_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_5_0-4-drop_bs_item_code_setting_bill_xrefs_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-4-drop_bs_item_code_setting_bill_xrefs_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    // $tableName  = strtolower(ItemCodeSettingBillXrefTable::getInstance()->getTableName());
    $tableName = 'bs_item_code_setting_bill_xrefs';

    // check for table existence
    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
    AND table_name = '".strtolower($tableName)."');");

    $stmt->execute();

    $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( !$isTableExists )
    {
        return $this->logSection('2_5_0-4-drop_bs_item_code_setting_bill_xrefs_table', "Table {$tableName} does not exist!");
    }

    $queries = array(
      "DROP TABLE {$tableName};",
    );

    foreach ($queries as $query )
    {
        $stmt = $con->prepare($query);

        $stmt->execute();
    }

    return $this->logSection('2_5_0-4-drop_bs_item_code_setting_bill_xrefs_table', "Successfully dropped {$tableName} table!");
  }
}
