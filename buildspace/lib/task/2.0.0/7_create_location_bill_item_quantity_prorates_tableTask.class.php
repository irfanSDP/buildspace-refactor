<?php

class create_location_bill_item_quantity_prorates_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-7-create_location_bill_item_quantity_prorates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-7-create_location_bill_item_quantity_prorates_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-7-create_location_bill_item_quantity_prorates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(LocationBillItemQuantityProrateTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-7-create_location_bill_item_quantity_prorates_table', 'Table '.LocationBillItemQuantityProrateTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." (id BIGSERIAL, location_assignment_id BIGINT NOT NULL, bill_column_setting_id BIGINT NOT NULL, unit BIGINT NOT NULL, percentage NUMERIC(18,5) DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX location_bill_item_quantity_prorates_unique_idx ON ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." (location_assignment_id, bill_column_setting_id, unit);",
            "CREATE INDEX location_bill_item_quantity_prorates_id_idx ON ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." (id, location_assignment_id, bill_column_setting_id, unit);",
            "ALTER TABLE ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_bill_item_quantity_prorates_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_bill_item_quantity_prorates_location_assignment_id FOREIGN KEY (location_assignment_id) REFERENCES ".LocationAssignmentTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_bill_item_quantity_prorates_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_bill_item_quantity_prorates_bill_column_setting_id FOREIGN KEY (bill_column_setting_id) REFERENCES ".BillColumnSettingTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-7-create_location_bill_item_quantity_prorates_table', 'Successfully created '.LocationBillItemQuantityProrateTable::getInstance()->getTableName().' table!');
    }
}