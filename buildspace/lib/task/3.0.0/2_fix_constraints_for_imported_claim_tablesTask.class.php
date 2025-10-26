<?php

class fix_constraints_for_imported_claim_tablesTask extends sfBaseTask
{
    protected $con;

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '3_0_0-2-fix_constraints_for_imported_claim_tables';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3_0_0-2-fix_constraints_for_imported_claim_tables|INFO] task does things.
Call it with:

  [php symfony 3_0_0-2-fix_constraints_for_imported_claim_tables|INFO]
EOF;
    }

    protected function constraintExists($tablename, $constraint)
    {
        $stmt = $this->con->prepare("
            SELECT
                tc.table_schema, 
                tc.constraint_name, 
                tc.table_name, 
                kcu.column_name, 
                ccu.table_schema AS foreign_table_schema,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name 
            FROM 
                information_schema.table_constraints AS tc 
                JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                    ON ccu.constraint_name = tc.constraint_name
                    AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
            AND tc.table_name ilike '" .$tablename. "'
            AND tc.constraint_name ilike '" . $constraint . "';");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $this->con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $statements = array();

        if ( $this->constraintExists(ImportedVariationOrderTable::getInstance()->getTableName(), 'bs_imported_variation_orders') )
        {
            $statements[] = "ALTER TABLE " .ImportedVariationOrderTable::getInstance()->getTableName(). " DROP CONSTRAINT bs_imported_variation_orders;";
            $statements[] = "ALTER TABLE " .ImportedVariationOrderTable::getInstance()->getTableName(). " ADD CONSTRAINT bs_imported_vo_revision_id_fk  FOREIGN KEY (revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;";
        }

        $statements[] = "ALTER TABLE " .ImportedVariationOrderClaimItemTable::getInstance()->getTableName(). " DROP CONSTRAINT imported_vo_claim_items_imported_vo_item_id_fk;";
        $statements[] = "ALTER TABLE " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " ADD CONSTRAINT imported_vo_claim_items_imported_vo_item_id_fk FOREIGN KEY (imported_variation_order_item_id) REFERENCES BS_imported_variation_order_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;";

        if ( $this->constraintExists(ClaimImportLogTable::getInstance()->getTableName(), 'bs_claim_import_logs_revision_id_bs_project_revisions_id') )
        {
            $statements[] = "ALTER TABLE " . ClaimImportLogTable::getInstance()->getTableName() . " DROP CONSTRAINT bs_claim_import_logs_revision_id_bs_project_revisions_id;";
            $statements[] = "ALTER TABLE " . ClaimImportLogTable::getInstance()->getTableName() . " ADD CONSTRAINT bs_claim_import_logs_revision_id_fk FOREIGN KEY (revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;";
        }

        foreach($statements as $statement)
        {
            $stmt = $this->con->prepare($statement);

            $stmt->execute();
        }

        return $this->logSection('3_0_0-2-fix_constraints_for_imported_claim_tables', 'Updated constraints for imported claim tables.');
    }
}
