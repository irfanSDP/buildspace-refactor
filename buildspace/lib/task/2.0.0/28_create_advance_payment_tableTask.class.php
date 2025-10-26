<?php

class create_advance_payment_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-28-create_advance_payment_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-28-create_advance_payment_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-28-create_advance_payment_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(AdvancePaymentTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-28-create_advance_payment_table', 'Table '.AdvancePaymentTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".AdvancePaymentTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_claim_id BIGINT NOT NULL, priority BIGINT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX advance_payment_priority_unique_idx ON ".AdvancePaymentTable::getInstance()->getTableName()." (post_contract_claim_id, deleted_at);",
            "CREATE INDEX advance_payment_id_idx ON ".AdvancePaymentTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".AdvancePaymentTable::getInstance()->getTableName()." ADD CONSTRAINT bs_advance_payments_post_contract_claim_id FOREIGN KEY (post_contract_claim_id) REFERENCES BS_post_contract_claims(id) NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".AdvancePaymentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_advance_payments_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".AdvancePaymentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_advance_payments_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-28-create_advance_payment_table', 'Successfully created '.AdvancePaymentTable::getInstance()->getTableName().' table!');
    }
}