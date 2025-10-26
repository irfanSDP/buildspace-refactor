<?php

class drop_priority_column_item_code_settings_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '2_5_0-5-drop_priority_column_item_code_settings_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_5_0-5-drop_priority_column_item_code_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-5-drop_priority_column_item_code_settings_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName  = strtolower(ItemCodeSettingTable::getInstance()->getTableName());
    $columnName = 'priority';

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
                AND table_name = '".$tableName."' and column_name = '".$columnName."');");

    $stmt->execute();

    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if($columnExists)
    {
      $stmt = $con->prepare("ALTER TABLE {$tableName} DROP COLUMN IF EXISTS {$columnName};");
      $stmt->execute();

      $this->logSection("2_5_0-5-drop_priority_column_item_code_settings_table", "Column {$columnName} has been dropped successfully from {$tableName} table!");

      $stmt = $con->prepare("TRUNCATE TABLE {$tableName} RESTART IDENTITY;");
      $stmt->execute();

      $this->logSection("2_5_0-5-drop_priority_column_item_code_settings_table", "Table {$tableName} truncated and identity has been restarted!");
    }
    else
    {
      $this->logSection("2_5_0-5-drop_priority_column_item_code_settings_table", "Column {$columnName} no longer exists in {$tableName} table!");
    }
  }
}
