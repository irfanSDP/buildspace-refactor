<?php

class add_tenderer_rate_log_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_9_0_1_add_tenderer_rate_log_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_9_0_1_add_tenderer_rate_log_table|INFO] task does things.
Call it with:

  [php symfony 1_9_0_1_add_tenderer_rate_log_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".TenderBillItemRateLogTable::getInstance()->getTableName()."');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_9_0_1_add_tenderer_rate_log_table', 'Table '.TenderBillItemRateLogTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            'CREATE TABLE '.TenderBillItemRateLogTable::getInstance()->getTableName().' (id BIGSERIAL, tender_company_id BIGINT NOT NULL, bill_item_id BIGINT NOT NULL, rate NUMERIC(18,5) DEFAULT 0, grand_total NUMERIC(18,5) DEFAULT 0, type VARCHAR(255) NOT NULL, changes_count BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
            'CREATE UNIQUE INDEX tender_bill_item_rate_logs_unique_idx ON '.TenderBillItemRateLogTable::getInstance()->getTableName().' (tender_company_id, bill_item_id, changes_count);',
            'CREATE INDEX tender_bill_item_rate_logs_id_idx ON '.TenderBillItemRateLogTable::getInstance()->getTableName().' (id);',
            'CREATE INDEX tender_bill_item_rate_logs_fk_idx ON '.TenderBillItemRateLogTable::getInstance()->getTableName().' (tender_company_id, bill_item_id);',
            'ALTER TABLE '.TenderBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_bill_item_rate_logs_tender_company_id FOREIGN KEY (tender_company_id) REFERENCES '.TenderCompanyTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_bill_item_rate_logs_updated_by_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_bill_item_rate_logs_created_by_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
            'ALTER TABLE '.TenderBillItemRateLogTable::getInstance()->getTableName().' ADD CONSTRAINT tender_bill_item_rate_logs_bill_item_id FOREIGN KEY (bill_item_id) REFERENCES '.BillItemTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;'
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_9_0_1_add_tenderer_rate_log_table', 'Successfully added table '.TenderBillItemRateLogTable::getInstance()->getTableName().'!');
    }
}