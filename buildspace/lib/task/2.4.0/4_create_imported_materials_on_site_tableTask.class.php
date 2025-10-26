<?php

class create_imported_materials_on_site_tableTask extends sfBaseTask
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
        $this->name                = '2_4_0-4-create_imported_materials_on_site_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_4_0-4-create_imported_materials_on_site_table|INFO] task does things.
Call it with:

  [php symfony 2_4_0-4-create_imported_materials_on_site_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ImportedMaterialOnSiteTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_4_0-4-create_imported_materials_on_site_table', 'Table '.ImportedMaterialOnSiteTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " (id BIGSERIAL, revision_id BIGINT NOT NULL, project_structure_id BIGINT NOT NULL, tender_origin_id TEXT, description TEXT, sequence BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX imported_mos_priority_unique_idx ON " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " (sequence, project_structure_id);",
            "CREATE INDEX imported_mos_id_idx ON " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " (id, project_structure_id, revision_id);",
            "ALTER TABLE " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " ADD CONSTRAINT imported_mos_revision_id_fk FOREIGN KEY (revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " ADD CONSTRAINT imported_mos_project_structure_id_fk FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_4_0-4-create_imported_materials_on_site_table', 'Successfully created '.ImportedMaterialOnSiteTable::getInstance()->getTableName().' table!');
    }
}
