<?php

class create_item_code_setting_objects_tableTask extends sfBaseTask
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
    $this->name             = '2_5_0-6-create_item_code_setting_objects_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_5_0-6-create_item_code_setting_objects_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-6-create_item_code_setting_objects_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName  = strtolower(ItemCodeSettingObjectTable::getInstance()->getTableName());

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '{$tableName}');");

    $stmt->execute();

    $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $isTableExists )
    {
        return $this->logSection('2_5_0-6-create_item_code_setting_objects_table', 'Table ' . $tableName . ' already exists!');
    }

    $queries = array(
      "CREATE TABLE BS_item_code_setting_objects (id BIGSERIAL, project_structure_id BIGINT NOT NULL, object_id BIGINT NOT NULL, object_type VARCHAR(255), created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
      "CREATE INDEX item_code_setting_object_id_idx ON BS_item_code_setting_objects (id, project_structure_id);",
      "CREATE INDEX item_code_setting_object_fx_idx ON BS_item_code_setting_objects (project_structure_id);",
      "ALTER TABLE BS_item_code_setting_objects ADD CONSTRAINT BS_item_code_setting_objects_project_structure_id_fk FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_item_code_setting_objects ADD CONSTRAINT BS_item_code_setting_objects_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_item_code_setting_objects ADD CONSTRAINT BS_item_code_setting_objects_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
    );

    foreach ($queries as $query )
    {
        $stmt = $con->prepare($query);

        $stmt->execute();
    }

    return $this->logSection('2_5_0-6-create_item_code_setting_objects_table', 'Successfully created ' . $tableName . ' table!');
  }
}
