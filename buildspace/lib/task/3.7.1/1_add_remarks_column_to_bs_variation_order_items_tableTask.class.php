<?php

class add_remarks_column_to_bs_variation_order_items_tableTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '3_7_1-1_add_remarks_column_to_bs_variation_order_items_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_7_1-1_add_remarks_column_to_bs_variation_order_items_table|INFO] task does things.
Call it with:

  [php symfony 3_7_1-1_add_remarks_column_to_bs_variation_order_items_table|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $con = $databaseManager->getDatabase($options['connection'])->getConnection();

    $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderItemTable::getInstance()->getTableName())."' and column_name = 'remarks');");

    $stmt->execute();

    $remarksColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

    if ( $remarksColumnExists )
    {
        $this->logSection('3_7_1-1_add_remarks_column_to_bs_variation_order_items_table', 'Column remarks already exists in '.VariationOrderItemTable::getInstance()->getTableName().' table!');
    }
    else
    {
        $stmt = $con->prepare("ALTER TABLE ".VariationOrderItemTable::getInstance()->getTableName()." ADD COLUMN remarks TEXT DEFAULT NULL");

        $stmt->execute();

        $this->logSection('3_7_1-1_add_remarks_column_to_bs_variation_order_items_table', 'Successfully added remarks column in '.VariationOrderItemTable::getInstance()->getTableName().' table!');
    }
  }
}
