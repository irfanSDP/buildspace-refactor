<?php

class create_imported_variation_order_items_tableTask extends sfBaseTask
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
        $this->name                = '2_4_0-2-create_imported_variation_order_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_4_0-2-create_imported_variation_order_items_table|INFO] task does things.
Call it with:

  [php symfony 2_4_0-2-create_imported_variation_order_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ImportedVariationOrderItemTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_4_0-2-create_imported_variation_order_items_table', 'Table '.ImportedVariationOrderItemTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " .ImportedVariationOrderItemTable::getInstance()->getTableName(). " (id BIGSERIAL, imported_variation_order_id BIGINT NOT NULL, tender_origin_id TEXT, description TEXT, priority BIGINT DEFAULT 0 NOT NULL, type INT NOT NULL, total_amount NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));",
            "CREATE INDEX imported_vo_items_id_idx ON " .ImportedVariationOrderItemTable::getInstance()->getTableName(). " (id, root_id, lft, rgt);",
            "CREATE INDEX imported_vo_items_fk_idx ON " .ImportedVariationOrderItemTable::getInstance()->getTableName(). " (imported_variation_order_id, root_id);",
            "ALTER TABLE " .ImportedVariationOrderItemTable::getInstance()->getTableName(). " ADD CONSTRAINT BS_imported_vo_items_imported_vo_id_fk FOREIGN KEY (imported_variation_order_id) REFERENCES BS_imported_variation_orders(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_4_0-2-create_imported_variation_order_items_table', 'Successfully created '.ImportedVariationOrderItemTable::getInstance()->getTableName().' table!');
    }
}
