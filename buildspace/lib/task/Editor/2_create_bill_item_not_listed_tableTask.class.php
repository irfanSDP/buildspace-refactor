<?php

class create_bill_item_not_listed_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '2-create_bill_item_not_listed_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2-create_bill_item_not_listed_table|INFO] task does things.
Call it with:

  [php symfony 2-create_bill_item_not_listed_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorBillItemNotListedTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2-create_bill_item_not_listed_table', 'Table '.EditorBillItemNotListedTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorBillItemNotListedTable::getInstance()->getTableName()." (id BIGSERIAL, company_id BIGINT NOT NULL, bill_item_id BIGINT NOT NULL, description TEXT, uom_id BIGINT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX editor_bill_item_not_listed_unique_idx ON ".EditorBillItemNotListedTable::getInstance()->getTableName()." (company_id, bill_item_id);",
            "CREATE INDEX editor_bill_item_not_listed_idx ON ".EditorBillItemNotListedTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX editor_bill_item_not_listed_fk_idx ON ".EditorBillItemNotListedTable::getInstance()->getTableName()." (company_id, bill_item_id);",
            "ALTER TABLE ".EditorBillItemNotListedTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_not_listed_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemNotListedTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_not_listed_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemNotListedTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_not_listed_company_id FOREIGN KEY (company_id) REFERENCES ".CompanyTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemNotListedTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_not_listed_bill_item_id FOREIGN KEY (bill_item_id) REFERENCES ".BillItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemNotListedTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_not_listed_uom_id FOREIGN KEY (uom_id) REFERENCES ".UnitOfMeasurementTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2-create_bill_item_not_listed_table', 'Successfully created '.EditorBillItemNotListedTable::getInstance()->getTableName().' table!');
    }
}
