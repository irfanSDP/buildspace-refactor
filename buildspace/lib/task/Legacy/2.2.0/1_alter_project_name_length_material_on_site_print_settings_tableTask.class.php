<?php

class alter_project_name_length_material_on_site_print_settings_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_2_0-1-alter_project_name_length_material_on_site_print_settings_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_2_0-1-alter_project_name_length_material_on_site_print_settings_table|INFO] task does things.
Call it with:

  [php symfony legacy-2_2_0-1-alter_project_name_length_material_on_site_print_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("ALTER TABLE ".MaterialOnSitePrintSettingTable::getInstance()->getTableName()." ALTER COLUMN project_name TYPE TEXT");

        $stmt->execute();

        return $this->logSection('legacy-2_2_0-1-alter_project_name_length_material_on_site_print_settings_table', 'Successfully altered table '.MaterialOnSitePrintSettingTable::getInstance()->getTableName().'!');
    }
}