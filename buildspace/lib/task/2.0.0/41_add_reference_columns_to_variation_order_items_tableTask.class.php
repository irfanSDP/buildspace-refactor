<?php

class add_reference_columns_to_variation_order_items_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-41-add_reference_columns_to_variation_order_items_tableTask';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-41-add_reference_columns_to_variation_order_items_tableTask|INFO] task does things.
Call it with:

  [php symfony 2_0_0-41-add_reference_columns_to_variation_order_items_tableTask|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $queries = array();

        // Column reference_quantity.
        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderItemTable::getInstance()->getTableName())."' and column_name ='reference_quantity');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( ! $columnExists )
        {
            $queries[] = "ALTER TABLE ".VariationOrderItemTable::getInstance()->getTableName()." ADD COLUMN reference_quantity NUMERIC(18,2) DEFAULT 0";
        }

        // Column reference_rate.
        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderItemTable::getInstance()->getTableName())."' and column_name ='reference_rate');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( ! $columnExists )
        {
            $queries[] = "ALTER TABLE ".VariationOrderItemTable::getInstance()->getTableName()." ADD COLUMN reference_rate NUMERIC(18,5) DEFAULT 0";
        }

        // Column reference_amount.
        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderItemTable::getInstance()->getTableName())."' and column_name ='reference_amount');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( ! $columnExists )
        {
            $queries[] = "ALTER TABLE ".VariationOrderItemTable::getInstance()->getTableName()." ADD COLUMN reference_amount NUMERIC(18,5) DEFAULT 0";
        }

        foreach($queries as $query)
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        $queryCount = count($queries);

        return $this->logSection('2_0_0-41-add_reference_columns_to_variation_order_items_tableTask', 'Successfully added reference columns in '.VariationOrderItemTable::getInstance()->getTableName()." table! (Added {$queryCount}/3 columns)");
    }
}
