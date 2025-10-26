<?php

class add_type_column_variation_order_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-19-add_type_column_variation_order_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-19-add_type_column_variation_order_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-19-add_type_column_variation_order_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderTable::getInstance()->getTableName())."' and column_name = 'type');");

        $stmt->execute();

        $isColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isColumnExists )
        {
            return $this->logSection('2_0_0-19-add_type_column_variation_order_table', 'Column type already exists in '.VariationOrderTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("ALTER TABLE ".VariationOrderTable::getInstance()->getTableName()." ADD COLUMN type INT DEFAULT 1 NOT NULL");

        $stmt->execute();

        $stmt = $con->prepare("DROP INDEX variation_order_id_idx");

        $stmt->execute();

        $stmt = $con->prepare("CREATE INDEX variation_order_id_idx ON ".VariationOrderTable::getInstance()->getTableName()." (id, project_structure_id, type)");

        $stmt->execute();

        return $this->logSection('2_0_0-19-add_type_column_variation_order_table', 'Successfully added column type in '.VariationOrderTable::getInstance()->getTableName().' table!');
    }
}
