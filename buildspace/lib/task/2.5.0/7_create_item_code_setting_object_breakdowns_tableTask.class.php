<?php

class create_item_code_setting_object_breakdowns_tableTask extends sfBaseTask
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
    $this->name             = '2_5_0-7-create_item_code_setting_object_breakdowns_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [2_5_0-7-create_item_code_setting_object_breakdowns_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-7-create_item_code_setting_object_breakdowns_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    // add your code here
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $tableName  = strtolower(ItemCodeSettingObjectBreakdownTable::getInstance()->getTableName());

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '{$tableName}');");

    $stmt->execute();

    $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $isTableExists )
    {
        return $this->logSection('2_5_0-7-create_item_code_setting_object_breakdowns_table', 'Table ' . $tableName . ' already exists!');
    }

    $queries = array(
      "CREATE TABLE BS_item_code_setting_object_breakdowns (id BIGSERIAL, item_code_setting_object_id BIGINT NOT NULL, claim_certificate_id BIGINT NOT NULL, item_code_setting_id BIGINT NOT NULL, amount NUMERIC(18,5), created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
      "CREATE UNIQUE INDEX item_code_setting_object_breakdown_unique_idx ON BS_item_code_setting_object_breakdowns (item_code_setting_object_id, claim_certificate_id, item_code_setting_id);",
      "CREATE INDEX item_code_setting_object_breakdown_id_idx ON BS_item_code_setting_object_breakdowns (id, item_code_setting_object_id, claim_certificate_id, item_code_setting_id);",
      "CREATE INDEX item_code_setting_object_breakdown_fx_idx ON BS_item_code_setting_object_breakdowns (item_code_setting_object_id, claim_certificate_id, item_code_setting_id);",
      "ALTER TABLE BS_item_code_setting_object_breakdowns ADD CONSTRAINT BS_item_code_setting_object_breakdowns_updated_by_fk FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_item_code_setting_object_breakdowns ADD CONSTRAINT BS_item_code_setting_object_breakdowns_item_code_setting_id_fk FOREIGN KEY (item_code_setting_id) REFERENCES BS_item_code_settings(id) NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_item_code_setting_object_breakdowns ADD CONSTRAINT BS_item_code_setting_object_breakdowns_item_code_setting_object_id_fk FOREIGN KEY (item_code_setting_object_id) REFERENCES BS_item_code_setting_objects(id) NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_item_code_setting_object_breakdowns ADD CONSTRAINT BS_item_code_setting_object_breakdowns_created_by_fk FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
      "ALTER TABLE BS_item_code_setting_object_breakdowns ADD CONSTRAINT BS_item_code_setting_object_breakdowns_claim_certificate_id_fk FOREIGN KEY (claim_certificate_id) REFERENCES BS_claim_certificates(id) NOT DEFERRABLE INITIALLY IMMEDIATE;",
    );

    foreach ($queries as $query )
    {
        $stmt = $con->prepare($query);

        $stmt->execute();
    }

    return $this->logSection('2_5_0-7-create_item_code_setting_object_breakdowns_table', 'Successfully created ' . $tableName . ' table!');
  }
}
