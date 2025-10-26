<?php

class create_bill_items_formulated_columns_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '4-create_bill_items_formulated_columns_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [4-create_bill_items_formulated_columns_table|INFO] task does things.
Call it with:

  [php symfony 4-create_bill_items_formulated_columns_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorBillItemFormulatedColumnTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('4-create_bill_items_formulated_columns_table', 'Table '.EditorBillItemFormulatedColumnTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()." (id BIGSERIAL, linked BOOLEAN DEFAULT 'false', relation_id BIGINT NOT NULL, column_name VARCHAR(50) NOT NULL, value TEXT, final_value NUMERIC(18,5), created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE TABLE ".EditorBillItemEdgeTable::getInstance()->getTableName()." (id BIGSERIAL, node_from BIGINT NOT NULL, node_to BIGINT NOT NULL, column_name VARCHAR(50) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX editor_bill_ifc_rel_idx ON ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()." (relation_id);",
            "CREATE INDEX editor_bill_ifc_id_idx ON ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()." (id, relation_id, value, final_value, column_name, deleted_at);",
            "CREATE INDEX editor_bill_item_edge_id_idx ON ".EditorBillItemEdgeTable::getInstance()->getTableName()." (id, node_from, node_to, column_name, deleted_at);",
            "CREATE INDEX editor_bill_item_edge_fk_idx ON ".EditorBillItemEdgeTable::getInstance()->getTableName()." (node_from, node_to);",
            "ALTER TABLE ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_formulated_columns_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_formulated_columns_relation_id FOREIGN KEY (relation_id) REFERENCES ".EditorBillItemInfoTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_formulated_columns_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemEdgeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_edges_node_to FOREIGN KEY (node_to) REFERENCES ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemEdgeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_edges_node_from FOREIGN KEY (node_from) REFERENCES ".EditorBillItemFormulatedColumnTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemEdgeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_edges_updated_by FOREIGN KEY (updated_by) REFERENCES  ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorBillItemEdgeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_bill_item_edges_created_by FOREIGN KEY (created_by) REFERENCES  ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('4-create_bill_items_formulated_columns_table', 'Successfully created '.EditorBillItemFormulatedColumnTable::getInstance()->getTableName().' table!');
    }
}
