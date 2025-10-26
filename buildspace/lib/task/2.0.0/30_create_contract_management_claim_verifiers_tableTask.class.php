<?php

class create_contract_management_claim_verifiers_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-30-create_contract_management_claim_verifiers_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-30-create_contract_management_claim_verifiers_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-30-create_contract_management_claim_verifiers_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ContractManagementClaimVerifierTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-30-create_contract_management_claim_verifiers_table', 'Table '.ContractManagementClaimVerifierTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . ContractManagementClaimVerifierTable::getInstance()->getTableName() . " (id BIGSERIAL, project_structure_id BIGINT NOT NULL, module_identifier BIGINT NOT NULL, object_id BIGINT NOT NULL, user_id BIGINT NOT NULL, sequence_number BIGINT NOT NULL, approved BOOLEAN, verified_at TIMESTAMP, start_at TIMESTAMP, days_to_verify BIGINT, remarks TEXT, substitute_id BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX contract_management_claim_verifiers_idx ON " . ContractManagementClaimVerifierTable::getInstance()->getTableName() . " (project_structure_id, module_identifier, object_id);",
            "CREATE INDEX contract_management_claim_verifiers_id_idx ON " . ContractManagementClaimVerifierTable::getInstance()->getTableName() . " (id);",
            "ALTER TABLE " . ContractManagementClaimVerifierTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_contract_management_claim_verifiers_user_id_fk FOREIGN KEY (user_id) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . ContractManagementClaimVerifierTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_contract_management_claim_verifiers_project_structure_id_fk FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-30-create_contract_management_claim_verifiers_table', 'Successfully created '.ContractManagementClaimVerifierTable::getInstance()->getTableName().' table!');
    }
}
