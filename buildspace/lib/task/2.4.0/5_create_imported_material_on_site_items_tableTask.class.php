<?php

class create_imported_material_on_site_items_tableTask extends sfBaseTask
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
        $this->name                = '2_4_0-5-create_imported_material_on_site_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_4_0-5-create_imported_material_on_site_items_table|INFO] task does things.
Call it with:

  [php symfony 2_4_0-5-create_imported_material_on_site_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ImportedMaterialOnSiteItemTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_4_0-5-create_imported_material_on_site_items_table', 'Table '.ImportedMaterialOnSiteItemTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " (id BIGSERIAL, imported_material_on_site_id BIGINT NOT NULL, tender_origin_id TEXT, description TEXT, sequence BIGINT DEFAULT 0 NOT NULL, type INT NOT NULL, uom_symbol VARCHAR(10), quantity NUMERIC(18,2) DEFAULT 0, rate NUMERIC(18,5) DEFAULT 0, final_amount NUMERIC(18,5) DEFAULT 0, reduction_percentage NUMERIC(18,5) DEFAULT 0, reduction_amount NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));",
            "CREATE INDEX imported_mos_items_id_idx ON " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " (id, root_id, lft, rgt);",
            "CREATE INDEX imported_mos_items_fk_idx ON " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " (imported_material_on_site_id, root_id);",
            "ALTER TABLE " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " ADD CONSTRAINT imported_mos_items_imported_mos_id_fk FOREIGN KEY (imported_material_on_site_id) REFERENCES BS_imported_materials_on_site(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_4_0-5-create_imported_material_on_site_items_table', 'Successfully created '.ImportedMaterialOnSiteItemTable::getInstance()->getTableName().' table!');
    }
}
