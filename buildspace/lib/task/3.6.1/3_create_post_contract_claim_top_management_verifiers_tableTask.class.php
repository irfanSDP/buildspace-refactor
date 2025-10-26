<?php

class create_post_contract_claim_top_management_verifiersTask extends sfBaseTask
{
    protected function configure()
    {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
    ));

    $this->namespace        = 'buildspace';
    $this->name             = '3_6_1-3-create_post_contract_claim_top_management_verifiers_table';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [3_6_1-3-create_post_contract_claim_top_management_verifiers_table|INFO] task does things.
    Call it with:

    [php symfony 3_6_1-3-create_post_contract_claim_top_management_verifiers_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '".strtolower(PostContractClaimTopManagementVerifierTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if( ! $isTableExists )
        {
            $queries = [
                "CREATE TABLE " . PostContractClaimTopManagementVerifierTable::getInstance()->getTableName() . " (id BIGSERIAL, object_id BIGINT NOT NULL, object_type VARCHAR(255) NOT NULL, sequence BIGINT NOT NULL, user_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
                "CREATE UNIQUE INDEX post_contract_claim_top_management_verifiers_unique_idx ON " . PostContractClaimTopManagementVerifierTable::getInstance()->getTableName() . " (object_id, object_type, user_id);",
                "CREATE INDEX post_contract_claim_top_management_verifiers_id_idx ON " . PostContractClaimTopManagementVerifierTable::getInstance()->getTableName() . " (user_id);",
                "ALTER TABLE " . PostContractClaimTopManagementVerifierTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_post_contract_claim_top_management_verifiers_user_id FOREIGN KEY (user_id) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE " . PostContractClaimTopManagementVerifierTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_post_contract_claim_top_management_verifiers_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE " . PostContractClaimTopManagementVerifierTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_post_contract_claim_top_management_verifiers_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            ];

            foreach($queries as $query)
            {
                $stmt = $con->prepare($query);
                $stmt->execute();
            }
            
           return $this->logSection('3_6_1-3-create_post_contract_claim_top_management_verifiers_table', 'Successfully added '.PostContractClaimTopManagementVerifierTable::getInstance()->getTableName().' table!');
        }
        else
        {
           return $this->logSection('3_6_1-3-create_post_contract_claim_top_management_verifiers_table', 'Table '.PostContractClaimTopManagementVerifierTable::getInstance()->getTableName().' already exists!');
        }
    }
}
