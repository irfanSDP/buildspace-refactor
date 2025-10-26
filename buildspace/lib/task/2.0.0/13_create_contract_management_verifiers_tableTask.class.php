<?php

class create_contract_management_verifiers_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-13-create_contract_management_verifiers_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-13-create_contract_management_verifiers_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-13-create_contract_management_verifiers_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ContractManagementVerifierTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-13-create_contract_management_verifiers_table', 'Table '.ContractManagementVerifierTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".ContractManagementVerifierTable::getInstance()->getTableName()." (id BIGSERIAL, project_structure_id BIGINT NOT NULL, module_identifier BIGINT NOT NULL, user_id BIGINT NOT NULL, sequence_number BIGINT NOT NULL, approved BOOLEAN, verified_at TIMESTAMP, start_at TIMESTAMP, days_to_verify BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX contract_management_verifiers_user_id_unique_idx ON ".ContractManagementVerifierTable::getInstance()->getTableName()." (project_structure_id, module_identifier, user_id);",
            "CREATE UNIQUE INDEX contract_management_verifiers_sequence_number_unique_idx ON ".ContractManagementVerifierTable::getInstance()->getTableName()." (project_structure_id, module_identifier, sequence_number);",
            "CREATE INDEX contract_management_verifiers_project_structure_id_idx ON ".ContractManagementVerifierTable::getInstance()->getTableName()." (project_structure_id);",
            "CREATE INDEX contract_management_verifiers_id_idx ON ".ContractManagementVerifierTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".ContractManagementVerifierTable::getInstance()->getTableName()." ADD CONSTRAINT BS_contract_management_verifiers_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ContractManagementVerifierTable::getInstance()->getTableName()." ADD CONSTRAINT BS_contract_management_verifiers_user_id_BS_sf_guard_user_id FOREIGN KEY (user_id) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-13-create_contract_management_verifiers_table', 'Successfully created '.ContractManagementVerifierTable::getInstance()->getTableName().' table!');
    }
}
