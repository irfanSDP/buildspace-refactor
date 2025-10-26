<?php

class add_project_revision_id_column_to_bs_bill_elements_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '3_7_4-2_add_project_revision_id_column_to_bs_bill_elements_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_7_4-2_add_project_revision_id_column_to_bs_bill_elements_table|INFO] task does things.
Call it with:

  [php symfony 3_7_4-2_add_project_revision_id_column_to_bs_bill_elements_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(BillElementTable::getInstance()->getTableName())."' and column_name = 'project_revision_id');");

    $stmt->execute();

    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $columnExists )
    {
        $this->logSection('3_7_4-2_add_project_revision_id_column_to_bs_bill_elements_table', 'Column project_revision_id already exists in '.BillElementTable::getInstance()->getTableName().' table!');
    }
    else
    {
        $stmt = $con->prepare("ALTER TABLE ".BillElementTable::getInstance()->getTableName()." ADD COLUMN project_revision_id TEXT DEFAULT NULL");

        $stmt->execute();

        $this->logSection('3_7_4-2_add_project_revision_id_column_to_bs_bill_elements_table', 'Successfully added project_revision_id column in '.BillElementTable::getInstance()->getTableName().' table!');
    }
  }
}
