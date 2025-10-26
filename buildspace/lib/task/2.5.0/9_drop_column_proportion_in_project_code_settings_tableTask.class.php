<?php

class drop_column_proportion_in_project_code_settings_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '2_5_0-9-drop_column_proportion_in_project_code_settings_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_5_0-9-drop_column_proportion_in_project_code_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-9-drop_column_proportion_in_project_code_settings_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName = strtolower(ProjectCodeSettingsTable::getInstance()->getTableName());
    $column = 'proportion';
    
    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
                AND table_name = '".$tableName."' and column_name = '".$column."');");

    $stmt->execute();

    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if($columnExists)
    {
      $stmt = $con->prepare("ALTER TABLE {$tableName} DROP COLUMN IF EXISTS {$column};");
      $stmt->execute();

      $this->logSection("2_5_0-9-drop_column_proportion_in_project_code_settings_table", "Column {$column} has been dropped successfully from {$tableName} table!");
    }
    else
    {
      $this->logSection("2_5_0-9-drop_column_proportion_in_project_code_settings_table", "Column {$column} no longer exists in {$tableName} table!");
    }
  }
}
