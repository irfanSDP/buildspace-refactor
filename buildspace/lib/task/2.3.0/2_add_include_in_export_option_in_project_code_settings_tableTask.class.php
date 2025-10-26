<?php

class add_include_in_export_option_in_project_code_settings_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '2_3_0-2-add_include_in_export_option_in_project_code_settings_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_3_0-2-add_include_in_export_option_in_project_code_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_3_0-2-add_include_in_export_option_in_project_code_settings_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName = strtolower(ProjectCodeSettingsTable::getInstance()->getTableName());
    $columnName = 'include_in_export';

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
                    AND table_name = '".$tableName."' and column_name = '".$columnName."');");

    $stmt->execute();
        
    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if($columnExists)
    {
        $this->logSection('2_3_0-2-add_include_in_export_option_in_project_code_settings_table', 'Column ' . $columnName . ' already exists in ' . $tableName . ' table!');
    }
    else
    {
        $stmt = $con->prepare("ALTER TABLE " . $tableName . " ADD COLUMN " . $columnName . " BOOLEAN DEFAULT 'true'");

        $stmt->execute();

        $this->logSection('2_3_0-2-add_include_in_export_option_in_project_code_settings_table', 'Successfully added ' . $columnName . ' column in ' . $tableName . ' table!');
    }
  }
}
