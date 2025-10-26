<?php

class add_tables_for_sp_material_on_site_print_settingsTask extends sfBaseTask {

	protected function configure()
	{
		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
			// add your own options here
		));

		$this->namespace           = '';
		$this->name                = '1_8_0_6_add_tables_for_sp_material_on_site_print_settingsTask';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_8_0_6_add_tables_for_sp_material_on_site_print_settingsTask|INFO] task does things.
Call it with:

  [php symfony 1_8_0_6_add_tables_for_sp_material_on_site_print_settingsTask|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$con             = $databaseManager->getDatabase($options['connection'])->getConnection();

		// check for table existence, if not then proceed with insertion query
		$stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
		AND table_name = 'bs_sp_material_on_site_print_settings');");

		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ( $result['exists'] )
		{
			return $this->logSection('1_8_0_6_add_tables_for_sp_material_on_site_print_settingsTask', 'Table for Material on Site Print Setting has been added before!');
		}

		$queries = $this->explodeQueries();

		foreach ( $queries as $query )
		{
			$stmt = $con->prepare($query);

			$stmt->execute();
		}

		return $this->logSection('1_8_0_6_add_tables_for_sp_material_on_site_print_settingsTask', 'Successfully added table for Material on Site Print Setting!');
	}

	private function explodeQueries()
	{
		$queries = "CREATE TABLE BS_sp_material_on_site_print_settings (id BIGSERIAL, sub_package_id BIGINT, project_name VARCHAR(285) NOT NULL, site_belonging_address VARCHAR(100) DEFAULT 'Tarikh Milik Tapak:', original_finished_date VARCHAR(100) DEFAULT 'Tarikh Siap Asal:', contract_duration VARCHAR(100) DEFAULT 'Jangkamasa Kontrak:', contract_original_amount VARCHAR(100) DEFAULT 'Harga Kontrak Asal:', payment_revision_no VARCHAR(100) DEFAULT 'BAYARAN KEMAJUAN NO:', evaluation_date VARCHAR(100) DEFAULT 'TARIKH PENILAIAN:', total_text VARCHAR(100) DEFAULT 'TOTAL' NOT NULL, percentage_of_material_on_site_text VARCHAR(100) DEFAULT '% OF MATERIAL ON SITE' NOT NULL, carried_to_final_summary_text VARCHAR(100) DEFAULT 'CARRIED TO FINAL SUMMARY' NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));

		CREATE INDEX sp_mos_print_setting_idx ON BS_sp_material_on_site_print_settings (sub_package_id, deleted_at);

		ALTER TABLE BS_sp_material_on_site_print_settings ADD CONSTRAINT BuBi_48 FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_site_print_settings ADD CONSTRAINT BsBi_30 FOREIGN KEY (sub_package_id) REFERENCES BS_sub_packages(id) NOT DEFERRABLE INITIALLY IMMEDIATE;
		ALTER TABLE BS_sp_material_on_site_print_settings ADD CONSTRAINT BcBi_53 FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;";

		return array_filter(explode(';', trim($queries)));
	}

}