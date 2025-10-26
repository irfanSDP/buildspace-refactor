<?php

class drop_has_max_retention_sum_column_from_new_post_contract_form_information_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-46-drop_has_max_retention_sum_column_from_new_post_contract_form_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-46-drop_has_max_retention_sum_column_from_new_post_contract_form_information_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-46-drop_has_max_retention_sum_column_from_new_post_contract_form_information_table|INFO]
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
            $queries[] = "ALTER TABLE {$tableName} DROP COLUMN IF EXISTS has_max_retention_sum;";
        }
        else
        {
            $this->logSection('2_0_0-46-drop_has_max_retention_sum_column_from_new_post_contract_form_information_table', "Column has_max_retention_sum does not exist in {$tableName} table!");
        }

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-46-drop_has_max_retention_sum_column_from_new_post_contract_form_information_table', "Successfully removed has_max_retention_sum column from {$tableName} table!");
    }
}
