<?php

class add_tender_alternative_tableTask extends sfBaseTask
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
        $this->name                = '3_4_0-4-add_table_alternative_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3_4_0-4-add_tender_alternative_table|INFO] task does things.
Call it with:

  [php symfony 3_4_0-4-add_tender_alternative_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(TenderAlternativeTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( !$isTableExists )
        {
            $queries = [
                "CREATE TABLE ".TenderAlternativeTable::getInstance()->getTableName()." (id BIGSERIAL, title TEXT NOT NULL, description TEXT, project_structure_id BIGINT NOT NULL, tender_origin_id TEXT, project_revision_id BIGINT, deleted_at_project_revision_id BIGINT, project_revision_deleted_at TIMESTAMP, is_awarded BOOLEAN DEFAULT 'false' NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id))",
                "CREATE INDEX tender_alternative_id_idx ON ".TenderAlternativeTable::getInstance()->getTableName()." (id, project_structure_id, project_revision_deleted_at);",
                "CREATE INDEX tender_alternative_fk_idx ON ".TenderAlternativeTable::getInstance()->getTableName()." (project_structure_id, project_revision_id, deleted_at_project_revision_id);",
                "ALTER TABLE ".TenderAlternativeTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_project_revision_id FOREIGN KEY (project_revision_id) REFERENCES ".ProjectRevisionTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE ".TenderAlternativeTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE ".TenderAlternativeTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE ".TenderAlternativeTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
            ];
    
            foreach($queries as $query)
            {
                $stmt = $con->prepare($query);
                $stmt->execute();
            }
            
            $this->logSection('3_4_0-4-add_tender_alternative_table', 'Successfully added '.TenderAlternativeTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $this->logSection('3_4_0-4-add_tender_alternative_table', 'Table '.TenderAlternativeTable::getInstance()->getTableName().' already exists!');
        }
        
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(TenderAlternativeBillTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('3_4_0-4-add_tender_alternative_table', 'Table '.TenderAlternativeBillTable::getInstance()->getTableName().' already exists!');
        }

        $queries = [
            "CREATE TABLE ".TenderAlternativeBillTable::getInstance()->getTableName()." (tender_alternative_id BIGINT, project_structure_id BIGINT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(tender_alternative_id, project_structure_id));",
            "CREATE UNIQUE INDEX tender_alternatives_bills_unique_idx ON ".TenderAlternativeBillTable::getInstance()->getTableName()." (tender_alternative_id, project_structure_id);",
            "CREATE INDEX tender_alternatives_bills_id_idx ON ".TenderAlternativeBillTable::getInstance()->getTableName()." (tender_alternative_id, project_structure_id);",
            "ALTER TABLE ".TenderAlternativeBillTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_tender_alternative_id FOREIGN KEY (tender_alternative_id) REFERENCES ".TenderAlternativeTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".TenderAlternativeBillTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_bills_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".TenderAlternativeBillTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_bills_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".TenderAlternativeBillTable::getInstance()->getTableName()." ADD CONSTRAINT tender_alternatives_bills_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        ];
        
        foreach($queries as $query)
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('3_4_0-4-add_tender_alternative_table', 'Successfully added '.TenderAlternativeBillTable::getInstance()->getTableName().' table!');
    }
}
