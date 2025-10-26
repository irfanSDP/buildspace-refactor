<?php

class add_variation_order_cost_column_to_cost_data_items_tableTask extends sfBaseTask
{
    protected $con;

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'costdata';
        $this->name                = '25-add_variation_order_cost_column_to_cost_data_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [25-add_variation_order_cost_column_to_cost_data_items_table|INFO] task does things.
Call it with:

  [php symfony 25-add_variation_order_cost_column_to_cost_data_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = CostDataItemTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."' and column_name = 'variation_order_cost');");

        $stmt->execute();

        $isColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isColumnExists )
        {
            return $this->logSection('25-add_variation_order_cost_column_to_cost_data_items_table', "Column variation_order_cost already exists in {$tableName} table!");
        }

        $queries = array(
            "ALTER TABLE {$tableName} ADD COLUMN variation_order_cost NUMERIC(18,5) DEFAULT 0 NOT NULL",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('25-add_variation_order_cost_column_to_cost_data_items_table', "Successfully added column variation_order_cost in {$tableName} table!");
    }
}
