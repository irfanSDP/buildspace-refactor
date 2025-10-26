<?php

class add_tender_origin_id_column_to_variation_order_items_tableTask extends sfBaseTask
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
        $this->name                = '2_5_0-3-add_tender_origin_id_column_to_variation_order_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_5_0-3-add_tender_origin_id_column_to_variation_order_items_table|INFO] task does things.
Call it with:

  [php symfony 2_5_0-3-add_tender_origin_id_column_to_variation_order_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderItemTable::getInstance()->getTableName())."' and column_name ='tender_origin_id');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_5_0-3-add_tender_origin_id_column_to_variation_order_items_table', 'Column tender_origin_id already exists in '.VariationOrderItemTable::getInstance()->getTableName().' table!');
        }

        $queries = array(
            "ALTER TABLE ".VariationOrderItemTable::getInstance()->getTableName()." ADD COLUMN tender_origin_id TEXT",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_5_0-3-add_tender_origin_id_column_to_variation_order_items_table', 'Successfully added column tender_origin_id in '.VariationOrderItemTable::getInstance()->getTableName().' table!');
    }
}
