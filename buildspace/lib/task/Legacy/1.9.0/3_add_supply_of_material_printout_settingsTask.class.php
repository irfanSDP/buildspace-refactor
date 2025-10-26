<?php

class add_supply_of_material_printout_settingsTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name',
                'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_9_0_3_add_supply_of_material_printout_setting_schema';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_9_0_3_add_supply_of_material_printout_setting_schema|INFO] task does things.
Call it with:

  [php symfony 1_9_0_3_add_supply_of_material_printout_setting_schema|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con             = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
		AND table_name = 'bs_som_layout_head_settings');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['exists'])
        {
            return $this->logSection('1_9_0_3_add_supply_of_material_printout_setting_schema',
                'Table ' . SupplyOfMaterialLayoutSettingTable::getInstance()->getTableName() . ' already exists!');
        }

        $queries = $this->explodeQueries();

        foreach ($queries as $query)
        {
            $stmt = $con->prepare(trim($query));

            $stmt->execute();
        }

        // after execute all the schema queries, data load default settings
        Doctrine::loadData(array( sfConfig::get('sf_data_dir') . '/fixtures/supply_of_material_printing_settings.yml' ));

        return $this->logSection('1_9_0_3_add_supply_of_material_printout_setting_schema',
            'Successfully added table ' . SupplyOfMaterialLayoutSettingTable::getInstance()->getTableName() . '!');
    }

    private function explodeQueries()
    {
        $queries = "
        CREATE TABLE bs_som_layout_head_settings (id BIGSERIAL, som_layout_setting_id BIGINT, head BIGINT, bold BOOLEAN DEFAULT 'false', underline BOOLEAN DEFAULT 'false', italic BOOLEAN DEFAULT 'false', created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));

        CREATE TABLE bs_som_layout_phrase_settings (id BIGSERIAL, som_layout_setting_id BIGINT, to_collection VARCHAR(150) DEFAULT 'To Collection', currency VARCHAR(150) DEFAULT 'RM', collection_in_grid VARCHAR(150) DEFAULT 'Collection', element_header_bold BOOLEAN DEFAULT 'false', element_header_underline BOOLEAN DEFAULT 'false', element_header_italic BOOLEAN DEFAULT 'false', element_note_top_left_row1 VARCHAR(150), element_note_top_left_row2 VARCHAR(150), element_note_top_right_row1 VARCHAR(150), created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));

        CREATE TABLE bs_som_layout_settings (id BIGSERIAL, project_structure_id BIGINT, font TEXT DEFAULT 'Arial', size BIGINT DEFAULT 11, comma_total BOOLEAN DEFAULT 'false', comma_rate BOOLEAN DEFAULT 'false', priceformat VARCHAR(255) DEFAULT 'normal', includeiandoforbillref BOOLEAN DEFAULT 'false', add_cont BOOLEAN DEFAULT 'true', contd VARCHAR(150) DEFAULT '(CONT''d)', print_element_grid BOOLEAN DEFAULT 'true', print_element_grid_once BOOLEAN DEFAULT 'false', page_no_prefix VARCHAR(150), align_element_to_left BOOLEAN DEFAULT 'false', created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));

        CREATE INDEX som_layout_hset_id_idx ON bs_som_layout_head_settings (id, som_layout_setting_id, deleted_at);
        CREATE INDEX som_layout_hset_fk_idx ON bs_som_layout_head_settings (som_layout_setting_id);
        CREATE INDEX som_layout_phrase_id_idx ON bs_som_layout_phrase_settings (id, som_layout_setting_id, deleted_at);
        CREATE INDEX som_layout_phrase_fk_idx ON bs_som_layout_phrase_settings (som_layout_setting_id);
        CREATE INDEX som_layout_set_id_idx ON bs_som_layout_settings (id, project_structure_id, deleted_at);
        CREATE INDEX som_layout_set_fk_idx ON bs_som_layout_settings (project_structure_id);

        ALTER TABLE bs_som_layout_head_settings ADD CONSTRAINT bs_som_layout_head_settings_bs_som_layout_settings_id FOREIGN KEY (som_layout_setting_id) REFERENCES bs_som_layout_settings(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_head_settings ADD CONSTRAINT bs_som_layout_head_settings_updated_by_bs_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES bs_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_head_settings ADD CONSTRAINT bs_som_layout_head_settings_created_by_bs_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES bs_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_phrase_settings ADD CONSTRAINT bs_som_layout_phrase_settings_bs_som_layout_settings_id FOREIGN KEY (som_layout_setting_id) REFERENCES bs_som_layout_settings(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_phrase_settings ADD CONSTRAINT bs_som_layout_phrase_settings_updated_by_bs_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES bs_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_phrase_settings ADD CONSTRAINT bs_som_layout_phrase_settings_created_by_bs_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES bs_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_settings ADD CONSTRAINT BpBi_40 FOREIGN KEY (project_structure_id) REFERENCES bs_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_settings ADD CONSTRAINT bs_som_layout_settings_updated_by_bs_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES bs_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ALTER TABLE bs_som_layout_settings ADD CONSTRAINT bs_som_layout_settings_created_by_bs_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES bs_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;";

        return array_filter(explode(';', $queries));
    }

}