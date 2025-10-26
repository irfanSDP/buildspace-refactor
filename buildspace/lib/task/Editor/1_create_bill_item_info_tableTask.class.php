<?php

class create_bill_item_information_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '1-create_bill_item_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1-create_bill_item_information_table|INFO] task does things.
Call it with:

  [php symfony 1-create_bill_item_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorBillItemInfoTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('1-create_bill_item_information_table', 'Table '.EditorBillItemInfoTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorBillItemInfoTable::getInstance()->getTableName()." (id BIGSERIAL, bill_item_id BIGINT NOT NULL, company_id BIGINT NOT NULL, grand_total_quantity NUMERIC(18,5) DEFAULT 0, grand_total NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX editor_bill_item_info_unique_idx ON ".EditorBillItemInfoTable::getInstance()->getTableName()." (bill_item_id, company_id);",
            "CREATE INDEX editor_bill_item_info_id_idx ON ".EditorBillItemInfoTable::getInstance()->getTableName()." (id, bill_item_id, company_id);",
            "CREATE INDEX editor_bill_item_info_fk_idx ON ".EditorBillItemInfoTable::getInstance()->getTableName()." (bill_item_id, company_id);",
            "ALTER TABLE ".EditorBillItemInfoTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_information_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemInfoTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_information_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemInfoTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_information_company_id FOREIGN KEY (company_id) REFERENCES ".CompanyTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemInfoTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_information_bill_item_id FOREIGN KEY (bill_item_id) REFERENCES ".BillItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1-create_bill_item_information_table', 'Successfully created '.EditorBillItemInfoTable::getInstance()->getTableName().' table!');
    }
}
