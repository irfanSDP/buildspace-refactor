<?php

class drop_master_cost_data_work_category_item_settings_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'costdata';
        $this->name                = '18-drop_master_cost_data_work_category_item_settings_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [18-drop_master_cost_data_work_category_item_settings_table|INFO] task does things.
Call it with:

  [php symfony 18-drop_master_cost_data_work_category_item_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = 'bs_master_cost_data_work_category_item_settings';

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( !$isTableExists )
        {
            return $this->logSection('18-drop_master_cost_data_work_category_item_settings_table', "Table {$tableName} does not exist!");
        }

        $queries = array(
            "DROP TABLE {$tableName};",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('18-drop_master_cost_data_work_category_item_settings_table', "Successfully dropped {$tableName} table!");
    }
}
