<?php

class create_claim_certificate_print_settings_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_1_0-11-create_claim_certificate_print_settings_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-11-create_claim_certificate_print_settings_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-11-create_claim_certificate_print_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_1_0-11-create_claim_certificate_print_settings_table', "Table ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." already exists!");
        }

        $queries = array(
            "CREATE TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." (id BIGSERIAL, post_contract_id BIGINT NOT NULL, certificate_title VARCHAR(255), certificate_print_format smallint NOT NULL, section_a_label VARCHAR(10), section_b_label VARCHAR(10), section_c_label VARCHAR(10), section_d_label VARCHAR(10), section_misc_label VARCHAR(255), section_others_label VARCHAR(255), section_payment_on_behalf_label VARCHAR(255), tax_label VARCHAR(255), tax_invoice_by_sub_contractor_label VARCHAR(255), include_advance_payment BOOLEAN DEFAULT 'true' NOT NULL, include_deposit BOOLEAN DEFAULT 'true' NOT NULL, include_material_on_site BOOLEAN DEFAULT 'true' NOT NULL, include_ksk BOOLEAN DEFAULT 'true' NOT NULL, include_work_on_behalf_mc BOOLEAN DEFAULT 'true' NOT NULL, include_work_on_behalf BOOLEAN DEFAULT 'true' NOT NULL, include_purchase_on_behalf BOOLEAN DEFAULT 'true' NOT NULL, include_penalty BOOLEAN DEFAULT 'true' NOT NULL, include_utility BOOLEAN DEFAULT 'true' NOT NULL, include_permit BOOLEAN DEFAULT 'true' NOT NULL, include_debit_credit_note BOOLEAN DEFAULT 'true' NOT NULL, debit_credit_note_with_breakdown BOOLEAN DEFAULT 'false' NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX claim_certificate_print_settings_unique_idx ON ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." (post_contract_id);",
            "CREATE INDEX claim_certificate_print_settings_id_idx ON ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX claim_certificate_print_settings_foreign_keys_idx ON ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." (post_contract_id);",
            "ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_print_settings_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_print_settings_post_contract_id_BS_post_contracts_id FOREIGN KEY (post_contract_id) REFERENCES ".PostContractTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD CONSTRAINT BS_claim_certificate_print_settings_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);
            $stmt->execute();
        }

        return $this->logSection('2_1_0-11-create_claim_certificate_print_settings_table', "Successfully created ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." table!");
    }
}
