<?php

class add_disable_column_in_account_groups_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
        ));

    $this->namespace        = 'buildspace';
    $this->name             = '3_7_3-1_add_disable_column_in_account_groups_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_7_3-1_add_disable_column_in_account_groups_table|INFO] task does things.
Call it with:

  [php symfony 3_7_3-1_add_disable_column_in_account_groups_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    // check for table existence, if not then proceed with insertion query
    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
    AND table_name = '".strtolower(AccountGroupTable::getInstance()->getTableName())."' and column_name = 'disable');");

    $stmt->execute();

    $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $columnExists )
    {
        return $this->logSection('3_7_3-1_add_disable_column_in_account_groups_table', 'Column disable already exists in '.AccountGroupTable::getInstance()->getTableName().' table!');
    }

    $stmt = $con->prepare("ALTER TABLE ".AccountGroupTable::getInstance()->getTableName()." ADD COLUMN disable BOOLEAN DEFAULT 'false' NOT NULL");
    
    $stmt->execute();

    return $this->logSection('3_7_3-1_add_disable_column_in_account_groups_table', 'Successfully added disable column in '.AccountGroupTable::getInstance()->getTableName().' table!');
  }
}
