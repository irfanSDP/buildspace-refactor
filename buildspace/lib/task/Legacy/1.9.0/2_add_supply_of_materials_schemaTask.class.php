<?php

class add_supply_of_materials_schemaTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_9_0_2_add_supply_of_materials_schema';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_9_0_2_add_supply_of_materials_schema|INFO] task does things.
Call it with:

  [php symfony 1_9_0_2_add_supply_of_materials_schema|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".SupplyOfMaterialTable::getInstance()->getTableName()."');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_9_0_2_add_supply_of_materials_schema', 'Table '.SupplyOfMaterialTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            'CREATE TABLE '.SupplyOfMaterialTable::getInstance()->getTableName().' (id BIGSERIAL, title VARCHAR(200) NOT NULL, description TEXT, project_structure_id BIGINT NOT NULL, unit_type INT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));',
            'CREATE INDEX supply_of_material_id_idx ON '.SupplyOfMaterialTable::getInstance()->getTableName().' (id, project_structure_id, deleted_at);',
            'CREATE INDEX supply_of_material_fk_idx ON '.SupplyOfMaterialTable::getInstance()->getTableName().' (project_structure_id);',
            'CREATE TABLE '.SupplyOfMaterialElementTable::getInstance()->getTableName().' (id BIGSERIAL, description TEXT, note TEXT, project_structure_id BIGINT NOT NULL, tender_origin_id TEXT, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX supply_of_material_elem_priority_unique_idx ON '.SupplyOfMaterialElementTable::getInstance()->getTableName().' (priority, project_structure_id, deleted_at);',
            'CREATE INDEX supply_of_material_elem_id_idx ON '.SupplyOfMaterialElementTable::getInstance()->getTableName().' (id, project_structure_id);',
            'CREATE TABLE '.SupplyOfMaterialItemTable::getInstance()->getTableName().' (id BIGSERIAL, description TEXT, note TEXT, type INT NOT NULL, element_id BIGINT NOT NULL, uom_id BIGINT, supply_rate NUMERIC(18,5) DEFAULT 0, contractor_supply_rate NUMERIC(18,5) DEFAULT 0, estimated_qty NUMERIC(18,5) DEFAULT 0, percentage_of_wastage NUMERIC(18,5) DEFAULT 0, difference NUMERIC(18,5) DEFAULT 0, amount NUMERIC(18,5) DEFAULT 0, bill_import_item_id BIGINT, tender_origin_id TEXT, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));',
            'CREATE INDEX supply_of_material_items_type_idx ON '.SupplyOfMaterialItemTable::getInstance()->getTableName().' (type);',
            'CREATE INDEX supply_of_material_items_id_idx ON '.SupplyOfMaterialItemTable::getInstance()->getTableName().' (id, root_id, lft, rgt);',
            'CREATE INDEX supply_of_material_items_fk_idx ON '.SupplyOfMaterialItemTable::getInstance()->getTableName().' (element_id, root_id, uom_id, bill_import_item_id);',
            'CREATE TABLE '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' (id BIGSERIAL, tender_company_id BIGINT NOT NULL, supply_of_material_item_id BIGINT NOT NULL, supply_rate NUMERIC(18,5) DEFAULT 0, contractor_supply_rate NUMERIC(18,5) DEFAULT 0, estimated_qty NUMERIC(18,5) DEFAULT 0, percentage_of_wastage NUMERIC(18,5) DEFAULT 0, difference NUMERIC(18,5) DEFAULT 0, amount NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX tender_supply_of_material_rates_unique_idx ON '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' (tender_company_id, supply_of_material_item_id);',
            'CREATE INDEX tender_supply_of_material_rates_id_idx ON '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' (id);',
            'CREATE INDEX tender_supply_of_material_rates_fk_idx ON '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' (tender_company_id, supply_of_material_item_id);',
            'CREATE TABLE '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' (id BIGSERIAL, tender_company_id BIGINT NOT NULL, supply_of_material_item_id BIGINT NOT NULL, supply_rate NUMERIC(18,5) DEFAULT 0, contractor_supply_rate NUMERIC(18,5) DEFAULT 0, estimated_qty NUMERIC(18,5) DEFAULT 0, percentage_of_wastage NUMERIC(18,5) DEFAULT 0, difference NUMERIC(18,5) DEFAULT 0, amount NUMERIC(18,5) DEFAULT 0, type VARCHAR(255) NOT NULL, changes_count BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX tender_supply_of_material_item_rate_logs_unique_idx ON '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' (tender_company_id, supply_of_material_item_id, changes_count);',
            'CREATE INDEX tender_supply_of_material_item_rate_logs_id_idx ON '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' (id);',
            'CREATE INDEX tender_supply_of_material_item_rate_logs_fk_idx ON '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' (tender_company_id, supply_of_material_item_id);',
            'ALTER TABLE '.SupplyOfMaterialTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_materials_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES '.ProjectStructureTable::getInstance()->getTableName().' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_materials_unit_type_units_of_measurement_type_id FOREIGN KEY (unit_type) REFERENCES '.UnitOfMeasurementTypeTable::getInstance()->getTableName().'(id) NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_materials_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_materials_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialElementTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_elem_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES '.ProjectStructureTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialElementTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_elem_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialElementTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_elem_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialItemTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_item_elem_id FOREIGN KEY (element_id) REFERENCES '.SupplyOfMaterialElementTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialItemTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_items_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialItemTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_items_uom_id_unit_of_measurements_id FOREIGN KEY (uom_id) REFERENCES '.UnitOfMeasurementTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.SupplyOfMaterialItemTable::getInstance()->getTableName().' ADD CONSTRAINT supply_of_material_items_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_tender_company_id FOREIGN KEY (tender_company_id) REFERENCES '.TenderCompanyTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_som_item_id FOREIGN KEY (supply_of_material_item_id) REFERENCES '.SupplyOfMaterialItemTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialRateTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_log_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_log_tender_company_id FOREIGN KEY (tender_company_id) REFERENCES '.TenderCompanyTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_log_som_item_id FOREIGN KEY (supply_of_material_item_id) REFERENCES '.SupplyOfMaterialItemTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderSupplyOfMaterialItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_supply_of_material_rate_log_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;'
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_9_0_2_add_supply_of_materials_schema', 'Successfully added table '.SupplyOfMaterialTable::getInstance()->getTableName().'!');
    }
}