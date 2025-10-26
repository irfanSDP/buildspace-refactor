<?php

class create_lump_sum_percentage_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '6-create_lump_sum_percentage_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [6-create_lump_sum_percentage_table|INFO] task does things.
Call it with:

  [php symfony 6-create_lump_sum_percentage_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorBillItemLumpSumPercentageTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('6-create_lump_sum_percentage_table', 'Table '.EditorBillItemLumpSumPercentageTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." (id BIGSERIAL, bill_item_info_id BIGINT NOT NULL, rate NUMERIC(18,5) DEFAULT 0 NOT NULL, percentage NUMERIC(18,2) DEFAULT 0 NOT NULL, amount NUMERIC(18,5) DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX editor_bill_ilsp_unique_idx ON ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." (bill_item_info_id);",
            "CREATE INDEX editor_bill_ilsp_id_idx ON ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." (id, bill_item_info_id, amount);",
            "ALTER TABLE ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_lump_sum_percentages_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_lump_sum_percentages_created_by_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_lump_sum_percentages_bill_item_info_id FOREIGN KEY (bill_item_info_id) REFERENCES ".EditorBillItemInfoTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('6-create_lump_sum_percentage_table', 'Successfully created '.EditorBillItemLumpSumPercentageTable::getInstance()->getTableName().' table!');
    }
}
