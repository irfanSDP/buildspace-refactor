<?php

class add_from_rfv_column_in_variation_order_items_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-48-add_from_rfv_column_in_variation_order_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-48-add_from_rfv_column_in_variation_order_items_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-48-add_from_rfv_column_in_variation_order_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = VariationOrderItemTable::getInstance()->getTableName();

        $queries = [];

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."' and column_name ='is_from_rfv');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->logSection('2_0_0-48-add_from_rfv_column_in_variation_order_items_table', "Column is_from_rfv already exists in {$tableName} table!");
        }
        else
        {
            $queries = [
                "ALTER TABLE {$tableName} ADD COLUMN is_from_rfv BOOLEAN default false;",
            ];

        }

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-48-add_from_rfv_column_in_variation_order_items_table', "Successfully added is_from_rfv columns in {$tableName} table!");
    }
}
