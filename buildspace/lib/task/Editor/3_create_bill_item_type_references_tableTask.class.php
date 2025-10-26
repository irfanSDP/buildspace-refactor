<?php

class create_bill_item_type_references_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '3-create_bill_item_type_references_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3-create_bill_item_type_references_table|INFO] task does things.
Call it with:

  [php symfony 3-create_bill_item_type_references_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorBillItemTypeReferenceTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('3-create_bill_item_type_references_table', 'Table '.EditorBillItemTypeReferenceTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." (id BIGSERIAL, bill_item_info_id BIGINT NOT NULL, bill_column_setting_id BIGINT NOT NULL, quantity_per_unit NUMERIC(18,5) DEFAULT 0, total_quantity NUMERIC(18,5) DEFAULT 0, grand_total NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX editor_bill_item_type_references_unique_idx ON ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." (bill_item_info_id, bill_column_setting_id);",
            "CREATE INDEX editor_bill_item_type_references_id_idx ON ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX editor_bill_item_type_references_fk_idx ON ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." (bill_item_info_id, bill_column_setting_id);",
            "ALTER TABLE ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_type_references_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_type_references_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_type_references_item_info FOREIGN KEY (bill_item_info_id) REFERENCES ".EditorBillItemInfoTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemTypeReferenceTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_type_references_setting_id FOREIGN KEY (bill_column_setting_id) REFERENCES ".BillColumnSettingTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('3-create_bill_item_type_references_table', 'Successfully created '.EditorBillItemTypeReferenceTable::getInstance()->getTableName().' table!');
    }
}
