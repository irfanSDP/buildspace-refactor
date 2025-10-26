<?php

class add_timestamp_and_signable_columns_to_new_post_contract_form_information_tableTask extends sfBaseTask
{
    private $existingColumns = array();
    private $newColumns = array();
    private $queries = array();

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-25-add_timestamp_and_signable_columns_to_new_post_contract_form_information_tableTask';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-25-add_timestamp_and_signable_columns_to_new_post_contract_form_information_tableTask|INFO] task does things.
Call it with:

  [php symfony 2_0_0-25-add_timestamp_and_signable_columns_to_new_post_contract_form_information_tableTask|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->addTimestampColumnQueries($con);
        $this->addSignableColumnQueries($con);

        foreach($this->queries as $query)
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        if(count($this->existingColumns) > 0)
        {
            $this->logSection('2_0_0-25', 'Existing columns in ' . NewPostContractFormInformationTable::getInstance()->getTableName() . ' table: [' . implode(', ', $this->existingColumns) . ']');
        }

        if(count($this->newColumns) > 0)
        {
            $this->logSection('2_0_0-25', 'Added columns to ' . NewPostContractFormInformationTable::getInstance()->getTableName() . ' table: [' . implode(', ', $this->newColumns) . ']');
        }

        return $this->logSection('2_0_0-25-add_timestamp_and_signable_columns_to_new_post_contract_form_information_tableTask', 'Successfully added timestamp and signable columns to '.NewPostContractFormInformationTable::getInstance()->getTableName().' table!');
    }

    private function addTimestampColumnQueries($con)
    {
        $columnName = 'created_at';

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."' and column_name = '{$columnName}');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->existingColumns[] = $columnName;
        }
        else
        {
            $this->newColumns[] = $columnName;
            $this->queries[] = "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD COLUMN  {$columnName} TIMESTAMP";

            // Set value for existing records.
            $this->queries[] = "UPDATE ".NewPostContractFormInformationTable::getInstance()->getTableName()." SET {$columnName} = NOW()";
            $this->queries[] = "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ALTER COLUMN {$columnName} SET NOT NULL";
        }

        $columnName = 'updated_at';

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."' and column_name = '{$columnName}');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->existingColumns[] = $columnName;
        }
        else
        {
            $this->newColumns[] = $columnName;
            $this->queries[] = "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD COLUMN  {$columnName} TIMESTAMP";

            // Set value for existing records.
            $this->queries[] = "UPDATE ".NewPostContractFormInformationTable::getInstance()->getTableName()." SET {$columnName} = NOW()";
            $this->queries[] = "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ALTER COLUMN {$columnName} SET NOT NULL";
        }
    }

    private function addSignableColumnQueries($con)
    {
        $columnName = 'created_by';

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."' and column_name = '{$columnName}');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->existingColumns[] = $columnName;
        }
        else
        {
            $this->newColumns[] = $columnName;
            $this->queries[] = "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD COLUMN  {$columnName} BIGINT";
        }

        $columnName = 'updated_by';

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."' and column_name = '{$columnName}');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->existingColumns[] = $columnName;
        }
        else
        {
            $this->newColumns[] = $columnName;
            $this->queries[] = "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD COLUMN  {$columnName} BIGINT";
        }
    }

}
