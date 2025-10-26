<?php

class add_max_retention_sum_columns_to_new_post_contract_form_information_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-44-add_max_retention_sum_columns_to_new_post_contract_form_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-44-add_max_retention_sum_columns_to_new_post_contract_form_information_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-44-add_max_retention_sum_columns_to_new_post_contract_form_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = NewPostContractFormInformationTable::getInstance()->getTableName();

        $queries = [];

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."' and column_name ='has_max_retention_sum');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->logSection('2_0_0-44-add_max_retention_sum_columns_to_new_post_contract_form_information_table', "Column has_max_retention_sum already exists in {$tableName} table!");
        }
        else
        {
            $queries[] = "ALTER TABLE {$tableName} ADD COLUMN has_max_retention_sum BOOLEAN DEFAULT 'false' NOT NULL;";
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."' and column_name ='max_retention_sum');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->logSection('2_0_0-44-add_max_retention_sum_columns_to_new_post_contract_form_information_table', "Column max_retention_sum already exists in {$tableName} table!");
        }
        else
        {
            $queries[] = "ALTER TABLE {$tableName} ADD COLUMN max_retention_sum NUMERIC(5,2) DEFAULT 0;";
        }

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-44-add_max_retention_sum_columns_to_new_post_contract_form_information_table', "Successfully added max_retention_sum columns in {$tableName} table!");
    }
}
