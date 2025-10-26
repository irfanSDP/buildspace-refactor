<?php

class add_column_type_in_project_code_settings_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'Buildspace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '2_5_0-8-add_column_type_in_project_code_settings_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_5_0-8-add_column_type_in_project_code_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-8-add_column_type_in_project_code_settings_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con= $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName = strtolower(ProjectCodeSettingsTable::getInstance()->getTableName());
    $columnName = 'type';

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
                    AND table_name = '".$tableName."' and column_name = '".$columnName."');");

    $stmt->execute();
            
    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if($columnExists)
    {
        $this->logSection('2_5_0-8-add_column_type_in_project_code_settings_table', 'Column ' . $columnName . ' already exists in ' . $tableName . ' table!');
    }
    else
    {
        $stmt = $con->prepare("ALTER TABLE " . $tableName . " ADD COLUMN " . $columnName . " SMALLINT NOT NULL DEFAULT " . ProjectCodeSettings::TYPE_PARENT_SUBSIDIARY);

        $stmt->execute();

        $this->logSection('2_5_0-8-add_column_type_in_project_code_settings_table', 'Successfully added ' . $columnName . ' column in ' . $tableName . ' table!');
    }
  }
}
