<?php

class create_prime_cost_rate_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '5-create_prime_cost_rate_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [5-create_prime_cost_rate_table|INFO] task does things.
Call it with:

  [php symfony 5-create_prime_cost_rate_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorBillItemPrimeCostRateTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('5-create_prime_cost_rate_table', 'Table '.EditorBillItemPrimeCostRateTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." (id BIGSERIAL, bill_item_info_id BIGINT NOT NULL, supply_rate NUMERIC(18,5) DEFAULT 0 NOT NULL, wastage_percentage NUMERIC(18,5) DEFAULT 0 NOT NULL, wastage_amount NUMERIC(18,5) DEFAULT 0 NOT NULL, labour_for_installation NUMERIC(18,5) DEFAULT 0 NOT NULL, other_cost NUMERIC(18,5) DEFAULT 0 NOT NULL, profit_percentage NUMERIC(18,5) DEFAULT 0 NOT NULL, profit_amount NUMERIC(18,5) DEFAULT 0 NOT NULL, total NUMERIC(18,5) DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX editor_bill_ipcr_unique_idx ON ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." (bill_item_info_id);",
            "CREATE INDEX editor_bill_ipcr_id_idx ON ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." (id, bill_item_info_id, total);",
            "ALTER TABLE ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_prime_cost_rates_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_prime_cost_rates_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_prime_cost_rates_bill_item_info_id FOREIGN KEY (bill_item_info_id) REFERENCES ".EditorBillItemInfoTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('5-create_prime_cost_rate_table', 'Successfully created '.EditorBillItemPrimeCostRateTable::getInstance()->getTableName().' table!');
    }
}
