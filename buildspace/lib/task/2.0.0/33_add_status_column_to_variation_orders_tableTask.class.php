<?php

class add_status_column_to_variation_orders_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-33-add_status_column_to_variation_orders_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-33-add_status_column_to_variation_orders_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-33-add_status_column_to_variation_orders_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderTable::getInstance()->getTableName())."' and column_name ='status');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            return $this->logSection('2_0_0-33-add_status_column_to_variation_orders_table', 'Column status already exists in '.VariationOrderTable::getInstance()->getTableName().' table!');
        }

        $queries = array(
            "ALTER TABLE ".VariationOrderTable::getInstance()->getTableName()." ADD COLUMN status BIGINT",
            "UPDATE ".VariationOrderTable::getInstance()->getTableName()." SET status = " . PostContractClaim::STATUS_APPROVED . " WHERE is_approved = TRUE",
            "UPDATE ".VariationOrderTable::getInstance()->getTableName()." SET status = " . PostContractClaim::STATUS_PREPARING . " WHERE is_approved = FALSE",
            "ALTER TABLE ".VariationOrderTable::getInstance()->getTableName()." ALTER COLUMN status SET NOT NULL",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-33-add_status_column_to_variation_orders_table', 'Successfully added column status in '.VariationOrderTable::getInstance()->getTableName().' table!');
    }
}
