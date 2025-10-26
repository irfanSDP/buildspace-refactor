<?php

class add_tax_option_in_project_summary_general_settings_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_2_0-1-add_tax_option_in_project_summary_general_settings_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
        The [2_2_0-1-add_tax_option_in_project_summary_general_settings_table|INFO] task does things.
        Call it with:

        [php symfony 2_2_0-1-add_tax_option_in_project_summary_general_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = strtolower(ProjectSummaryGeneralSettingTable::getInstance()->getTableName());

        $columns = [
            "include_tax"    => "ALTER TABLE " . $tableName . " ADD COLUMN include_tax BOOLEAN DEFAULT 'false'",
            "tax_name"       => "ALTER TABLE " . $tableName . " ADD COLUMN tax_name VARCHAR(100)",
            "tax_percentage" => "ALTER TABLE " . $tableName . " ADD COLUMN tax_percentage NUMERIC(18,5)",
        ];

        foreach($columns as $columnName => $query)
        {
            $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
                    AND table_name = '".$tableName."' and column_name = '".$columnName."');");

            $stmt->execute();
    
            $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            if($columnExists)
            {
                $this->logSection('2_2_0-1-add_tax_option_in_project_summary_general_settings_table', 'Column ' . $columnName . ' already exists in ' . $tableName . ' table!');
            }
            else
            {
                $stmt = $con->prepare($query);
    
                $stmt->execute();
    
                $this->logSection('2_2_0-1-add_tax_option_in_project_summary_general_settings_table', 'Successfully added ' . $columnName . ' column in ' . $tableName . ' table!');
            }
        }
    }
}
