<?php

class create_waiver_user_defined_options_tableTask extends sfBaseTask
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
    $this->name             = '3_0_0-4-create_waiver_user_defined_options_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_0_0-4-create_waiver_user_defined_options_table|INFO] task does things.
Call it with:

  [php symfony 3_0_0-4-create_waiver_user_defined_options_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName  = strtolower(WaiverUserDefinedOptionTable::getInstance()->getTableName());

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '{$tableName}');");

    $stmt->execute();

    $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $isTableExists )
    {
        return $this->logSection('3_0_0-4-create_waiver_user_defined_options_table', 'Table ' . $tableName . ' already exists!');
    }

    $queries = array(
      "CREATE TABLE BS_waiver_user_defined_options (id BIGSERIAL, project_structure_id BIGINT NOT NULL, waiver_option_type BIGINT NOT NULL, description TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
      "CREATE UNIQUE INDEX waiver_user_defined_option_unique_idx ON BS_waiver_user_defined_options (project_structure_id, waiver_option_type);",
      "CREATE INDEX waiver_user_defined_option_id_idx ON BS_waiver_user_defined_options (id, project_structure_id);",
      "CREATE INDEX waiver_user_defined_option_fx_idx ON BS_waiver_user_defined_options (project_structure_id);",
      "ALTER TABLE BS_waiver_user_defined_options ADD CONSTRAINT BS_waiver_user_defined_options_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_waiver_user_defined_options ADD CONSTRAINT BS_waiver_user_defined_options_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_waiver_user_defined_options ADD CONSTRAINT BS_waiver_user_defined_options_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
    );

    foreach ($queries as $query )
    {
        $stmt = $con->prepare($query);

        $stmt->execute();
    }

    return $this->logSection('3_0_0-4-create_waiver_user_defined_options_table', 'Successfully created ' . $tableName . ' table!');
  }
}
