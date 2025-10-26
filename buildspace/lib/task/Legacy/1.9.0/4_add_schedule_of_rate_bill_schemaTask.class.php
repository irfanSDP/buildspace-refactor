<?php

class add_schedule_of_rate_bill_schemaTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_9_0_4_add_schedule_of_rate_bill_schema';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_9_0_4_add_schedule_of_rate_bill_schema|INFO] task does things.
Call it with:

  [php symfony 1_9_0_4_add_schedule_of_rate_bill_schema|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".ScheduleOfRateBillTable::getInstance()->getTableName()."');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_9_0_4_add_schedule_of_rate_bill_schema', 'Table '.ScheduleOfRateBillTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            'CREATE TABLE '.ScheduleOfRateBillTable::getInstance()->getTableName().' (id BIGSERIAL, title VARCHAR(200) NOT NULL, description TEXT, project_structure_id BIGINT NOT NULL, unit_type INT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));',
            'CREATE INDEX schedule_of_rate_bill_id_idx ON '.ScheduleOfRateBillTable::getInstance()->getTableName().' (id, project_structure_id, deleted_at);',
            'CREATE INDEX schedule_of_rate_bill_fk_idx ON '.ScheduleOfRateBillTable::getInstance()->getTableName().' (project_structure_id);',

            'CREATE TABLE '.ScheduleOfRateBillElementTable::getInstance()->getTableName().' (id BIGSERIAL, description TEXT, note TEXT, project_structure_id BIGINT NOT NULL, tender_origin_id TEXT, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX schedule_of_rate_bill_elem_priority_unique_idx ON '.ScheduleOfRateBillElementTable::getInstance()->getTableName().' (priority, project_structure_id, deleted_at);',
            'CREATE INDEX schedule_of_rate_bill_elem_id_idx ON '.ScheduleOfRateBillElementTable::getInstance()->getTableName().' (id, project_structure_id);',

            'CREATE TABLE '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' (id BIGSERIAL, description TEXT, note TEXT, type INT NOT NULL, element_id BIGINT NOT NULL, uom_id BIGINT, estimation_rate NUMERIC(18,5) DEFAULT 0, contractor_rate NUMERIC(18,5) DEFAULT 0, difference NUMERIC(18,5) DEFAULT 0, bill_import_item_id BIGINT, tender_origin_id TEXT, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));',
            'CREATE INDEX schedule_of_rate_bill_items_type_idx ON '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' (type);',
            'CREATE INDEX schedule_of_rate_bill_items_id_idx ON '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' (id, root_id, lft, rgt);',
            'CREATE INDEX schedule_of_rate_bill_items_fk_idx ON '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' (element_id, root_id, uom_id, bill_import_item_id);',

            'CREATE TABLE '.TenderScheduleOfRateTable::getInstance()->getTableName().' (id BIGSERIAL, tender_company_id BIGINT NOT NULL, schedule_of_rate_bill_item_id BIGINT NOT NULL, estimation_rate NUMERIC(18,5) DEFAULT 0, contractor_rate NUMERIC(18,5) DEFAULT 0, difference NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX tender_schedule_of_rates_unique_idx ON '.TenderScheduleOfRateTable::getInstance()->getTableName().' (tender_company_id, schedule_of_rate_bill_item_id);',
            'CREATE INDEX tender_schedule_of_rates_id_idx ON '.TenderScheduleOfRateTable::getInstance()->getTableName().' (id);',
            'CREATE INDEX tender_schedule_of_rates_fk_idx ON '.TenderScheduleOfRateTable::getInstance()->getTableName().' (tender_company_id, schedule_of_rate_bill_item_id);',

            'CREATE TABLE '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' (id BIGSERIAL, tender_company_id BIGINT NOT NULL, schedule_of_rate_bill_item_id BIGINT NOT NULL, estimation_rate NUMERIC(18,5) DEFAULT 0, contractor_rate NUMERIC(18,5) DEFAULT 0, difference NUMERIC(18,5) DEFAULT 0, type VARCHAR(255) NOT NULL, changes_count BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX tender_schedule_of_rate_bill_item_rate_logs_unique_idx ON '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' (tender_company_id, schedule_of_rate_bill_item_id, changes_count);',
            'CREATE INDEX tender_schedule_of_rate_bill_item_rate_logs_id_idx ON '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' (id);',
            'CREATE INDEX tender_schedule_of_rate_bill_item_rate_logs_fk_idx ON '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' (tender_company_id, schedule_of_rate_bill_item_id);',

            'ALTER TABLE '.ScheduleOfRateBillTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bills_uom_type_id FOREIGN KEY (unit_type) REFERENCES '.UnitOfMeasurementTypeTable::getInstance()->getTableName().'(id) NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bills FOREIGN KEY (project_structure_id) REFERENCES '.ProjectStructureTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bills_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bills_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillElementTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_elements_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES '.ProjectStructureTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillElementTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_elements_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillElementTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_elements_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_items_element_id FOREIGN KEY (element_id) REFERENCES '.ScheduleOfRateBillElementTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_items_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_items_uom_id_unit_of_measurements_id FOREIGN KEY (uom_id) REFERENCES '.UnitOfMeasurementTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillItemTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_of_rate_bill_items_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',

            'ALTER TABLE '.TenderScheduleOfRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_tender_company_id FOREIGN KEY (tender_company_id) REFERENCES '.TenderCompanyTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_sor_bill_item_id FOREIGN KEY (schedule_of_rate_bill_item_id) REFERENCES '.ScheduleOfRateBillItemTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_log_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_log_tender_company_id FOREIGN KEY (tender_company_id) REFERENCES '.TenderCompanyTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_log_sor_bill_item_id FOREIGN KEY (schedule_of_rate_bill_item_id) REFERENCES '.ScheduleOfRateBillItemTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderScheduleOfRateBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_schedule_of_rate_log_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',

            "CREATE TABLE ".ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName()." (id BIGSERIAL, schedule_of_rate_bill_layout_setting_id BIGINT, head BIGINT, bold BOOLEAN DEFAULT 'false', underline BOOLEAN DEFAULT 'false', italic BOOLEAN DEFAULT 'false', created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            'CREATE INDEX sorb_layout_hset_id_idx ON '.ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName().' (id, schedule_of_rate_bill_layout_setting_id, deleted_at);',
            'CREATE INDEX sorb_layout_hset_fk_idx ON '.ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName().' (schedule_of_rate_bill_layout_setting_id);',

            "CREATE TABLE ".ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName()." (id BIGSERIAL, schedule_of_rate_bill_layout_setting_id BIGINT, to_collection VARCHAR(150) DEFAULT 'To Collection', currency VARCHAR(150) DEFAULT 'RM', collection_in_grid VARCHAR(150) DEFAULT 'Collection', element_header_bold BOOLEAN DEFAULT 'false', element_header_underline BOOLEAN DEFAULT 'false', element_header_italic BOOLEAN DEFAULT 'false', element_note_top_left_row1 VARCHAR(150), element_note_top_left_row2 VARCHAR(150), element_note_top_right_row1 VARCHAR(150), created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            'CREATE INDEX sorb_layout_phrase_id_idx ON '.ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName().' (id, schedule_of_rate_bill_layout_setting_id, deleted_at);',
            'CREATE INDEX sorb_layout_phrase_fk_idx ON '.ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName().' (schedule_of_rate_bill_layout_setting_id);',

            "CREATE TABLE ".ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName()." (id BIGSERIAL, project_structure_id BIGINT, font TEXT DEFAULT 'Arial', size BIGINT DEFAULT 11, comma_total BOOLEAN DEFAULT 'false', comma_rate BOOLEAN DEFAULT 'false', priceformat VARCHAR(255) DEFAULT 'normal', includeiandoforbillref BOOLEAN DEFAULT 'false', add_cont BOOLEAN DEFAULT 'true', contd VARCHAR(150) DEFAULT '(CONT''d)', print_element_grid BOOLEAN DEFAULT 'true', print_element_grid_once BOOLEAN DEFAULT 'false', page_no_prefix VARCHAR(150), align_element_to_left BOOLEAN DEFAULT 'false', created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            'CREATE INDEX sorb_layout_set_id_idx ON '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().' (id, project_structure_id, deleted_at);',
            'CREATE INDEX sorb_layout_set_fk_idx ON '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().' (project_structure_id);',

            'ALTER TABLE '.ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_lhs_updated_by_sf_guard_user FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_lhs_sorb_layout_setting_id FOREIGN KEY (schedule_of_rate_bill_layout_setting_id) REFERENCES '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_lhs_created_by_sf_guard_user FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_lps_updated_by_sf_guard_user FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_lps_sorb_layout_setting_id FOREIGN KEY (schedule_of_rate_bill_layout_setting_id) REFERENCES '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_lps_created_by_sf_guard_user FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_ls_updated_by_sf_guard_user FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_ls_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES '.ProjectStructureTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName().' ADD CONSTRAINT sorb_ls_created_by_sf_guard_user FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;'
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_9_0_4_add_schedule_of_rate_bill_schema', 'Successfully added table '.ScheduleOfRateBillTable::getInstance()->getTableName().'!');
    }
}