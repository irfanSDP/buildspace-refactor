<?php

class add_tables_for_sp_material_on_siteTask extends sfBaseTask {

	protected function configure()
	{
		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
			// add your own options here
		));

		$this->namespace           = '';
		$this->name                = '1_8_0_3_add_tables_for_sp_material_on_siteTask';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_8_0_3_add_tables_for_sp_material_on_siteTask|INFO] task does things.
Call it with:

  [php symfony 1_8_0_3_add_tables_for_sp_material_on_siteTask|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$con             = $databaseManager->getDatabase($options['connection'])->getConnection();

		// check for table existence, if not then proceed with insertion query
		$stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
		AND table_name = 'bs_sp_material_on_sites');");

		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ( $result['exists'] )
		{
			return $this->logSection('1_8_0_3_add_tables_for_sp_material_on_siteTask', 'Table for Sub Package Material on Site Module has been added before!');
		}

		$queries = $this->explodeQueries();

		foreach ( $queries as $query )
		{
			$stmt = $con->prepare(trim($query));

			$stmt->execute();
		}

		return $this->logSection('1_8_0_3_add_tables_for_sp_material_on_siteTask', 'Successfully added table for Sub Package Material on Site Module!');
	}

	private function explodeQueries()
	{
		$queries = "CREATE TABLE BS_sp_material_on_sites (id BIGSERIAL, sub_package_id BIGINT, description VARCHAR(100), status SMALLINT DEFAULT 1, reduction_percentage NUMERIC(18,5) DEFAULT 0, total NUMERIC(18,5) DEFAULT 0, total_after_reduction NUMERIC(18,5) DEFAULT 0, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));

		CREATE TABLE BS_sp_material_on_site_items (id BIGSERIAL, sp_material_on_site_id BIGINT NOT NULL, import_resource_item_id BIGINT, description TEXT, type INT NOT NULL, uom_id BIGINT, delivered_qty NUMERIC(18,5) DEFAULT 0, used_qty NUMERIC(18,5) DEFAULT 0, balance_qty NUMERIC(18,5) DEFAULT 0, rate NUMERIC(18,5) DEFAULT 0, amount NUMERIC(18,5) DEFAULT 0, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));

		CREATE INDEX sp_material_on_site_idx ON BS_sp_material_on_sites (sub_package_id, deleted_at);
		CREATE INDEX sp_mos_items_type_idx ON BS_sp_material_on_site_items (type);
		CREATE INDEX sp_mos_items_id_idx ON BS_sp_material_on_site_items (id, root_id, lft, rgt);
		CREATE INDEX sp_mos_items_fk_idx ON BS_sp_material_on_site_items (sp_material_on_site_id, root_id, uom_id);

		ALTER TABLE BS_sp_material_on_sites ADD CONSTRAINT BS_sp_material_on_sites_sub_package_id_BS_sub_packages_id FOREIGN KEY (sub_package_id) REFERENCES BS_sub_packages(id) NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_sites ADD CONSTRAINT BS_sp_material_on_sites_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_sites ADD CONSTRAINT BS_sp_material_on_sites_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_site_items ADD CONSTRAINT sp_mosi_mos FOREIGN KEY (sp_material_on_site_id) REFERENCES BS_sp_material_on_sites(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_site_items ADD CONSTRAINT BS_sp_material_on_site_items_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_site_items ADD CONSTRAINT BS_sp_material_on_site_items_uom_id_BS_unit_of_measurements_id FOREIGN KEY (uom_id) REFERENCES BS_unit_of_measurements(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_site_items ADD CONSTRAINT BS_sp_material_on_site_items_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;";

		return array_filter(explode(';', $queries));
	}

}