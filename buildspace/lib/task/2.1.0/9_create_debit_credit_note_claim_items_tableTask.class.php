<?php

class create_debit_credit_note_claim_items_tableTask extends sfBaseTask
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
        // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_1_0-9-create_debit_credit_note_claim_items_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-9-create_debit_credit_note_claim_items_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-9-create_debit_credit_note_claim_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // add your code here
        // add your code here
        $tableName = DebitCreditNoteClaimItemTable::getInstance()->getTableName();

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-9-create_debit_credit_note_claim_items_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, debit_credit_note_claim_id BIGINT NOT NULL, account_code_id BIGINT, invoice_number TEXT, invoice_date DATE, due_date DATE, uom_id BIGINT, quantity NUMERIC(18,5) DEFAULT 0, rate NUMERIC(18,5) DEFAULT 0, remarks TEXT, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE INDEX debit_credit_note_claim_item_id_idx ON {$tableName} (id, debit_credit_note_claim_id, account_code_id);",
            "CREATE INDEX debit_credit_note_claim_item_fk_idx ON {$tableName} (debit_credit_note_claim_id, account_code_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_debit_credit_note_claim_debit_credit_note_claim_id FOREIGN KEY (debit_credit_note_claim_id) REFERENCES BS_debit_credit_note_claims(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_debit_credit_note_claim_account_code_id FOREIGN KEY (account_code_id) REFERENCES BS_account_codes(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_debit_credit_note_claim_items_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT BS_debit_credit_note_claim_items_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('2_1_0-9-create_debit_credit_note_claim_items_table', "Successfully created {$tableName} table!");
    }
}