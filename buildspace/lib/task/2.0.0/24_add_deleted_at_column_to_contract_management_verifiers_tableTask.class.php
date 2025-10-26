<?php

class add_deleted_at_column_to_contract_management_verifiers_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-24-add_deleted_at_column_to_contract_management_verifiers_tableTask';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-24-add_deleted_at_column_to_contract_management_verifiers_tableTask|INFO] task does things.
Call it with:

  [php symfony 2_0_0-24-add_deleted_at_column_to_contract_management_verifiers_tableTask|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ContractManagementVerifierTable::getInstance()->getTableName())."' and column_name ='deleted_at');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            return $this->logSection('2_0_0-24-add_deleted_at_column_to_contract_management_verifiers_tableTask', 'Column deleted_at already exists in '.ContractManagementVerifierTable::getInstance()->getTableName().' table!');
        }

        $queries = array(
            "ALTER TABLE ".ContractManagementVerifierTable::getInstance()->getTableName()." ADD COLUMN  deleted_at TIMESTAMP",
            "DROP INDEX contract_management_verifiers_user_id_unique_idx;",
            "DROP INDEX contract_management_verifiers_sequence_number_unique_idx;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-24-add_deleted_at_column_to_contract_management_verifiers_tableTask', 'Successfully added column deleted_at in '.ContractManagementVerifierTable::getInstance()->getTableName().' table!');
    }
}
