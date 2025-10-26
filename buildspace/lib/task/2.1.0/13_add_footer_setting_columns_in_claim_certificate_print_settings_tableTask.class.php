<?php

class add_footer_setting_columns_in_claim_certificate_print_settings_tableTask extends sfBaseTask
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
        $this->name                = '2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_format');");

        $stmt->execute();

        $footerFormatColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerFormatColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_format already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_format BIGINT DEFAULT 1 NOT NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_format column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_bank_label');");

        $stmt->execute();

        $footerBankLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerBankLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_bank_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_bank_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_bank_label = 'Bank' WHERE footer_bank_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_bank_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_bank_signature_label');");

        $stmt->execute();

        $footerBankSignatureLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerBankSignatureLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_bank_signature_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_bank_signature_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_bank_signature_label = 'Prepared By' WHERE footer_bank_signature_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_bank_signature_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_cheque_number_label');");

        $stmt->execute();

        $footerChequeNumberLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerChequeNumberLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_cheque_number_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_cheque_number_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_cheque_number_label = 'Cheque No.' WHERE footer_cheque_number_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_cheque_number_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_cheque_number_signature_label');");

        $stmt->execute();

        $footerChequeNumberSignatureLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerChequeNumberSignatureLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_cheque_number_signature_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_cheque_number_signature_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_cheque_number_signature_label = 'Checked By' WHERE footer_cheque_number_signature_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_cheque_number_signature_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_cheque_date_label');");

        $stmt->execute();

        $footerChequeDateLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerChequeDateLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_cheque_date_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_cheque_date_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_cheque_date_label = 'Cheque Date' WHERE footer_cheque_date_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_cheque_date_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_cheque_date_signature_label');");

        $stmt->execute();

        $footerChequeDateSignatureLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerChequeDateSignatureLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_cheque_date_signature_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_cheque_date_signature_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_cheque_date_signature_label = 'Approved By' WHERE footer_cheque_date_signature_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_cheque_date_signature_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_cheque_amount_label');");

        $stmt->execute();

        $footerChequeAmountLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerChequeAmountLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_cheque_amount_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_cheque_amount_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_cheque_amount_label = 'Cheque Amount' WHERE footer_cheque_amount_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_cheque_amount_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'footer_cheque_amount_signature_label');");

        $stmt->execute();

        $footerChequeAmountSignatureLabelColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $footerChequeAmountSignatureLabelColumnExists )
        {
            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Column footer_cheque_amount_signature_label already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        else
        {
            $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN footer_cheque_amount_signature_label VARCHAR(50)");
    
            $stmt->execute();

            $stmt = $con->prepare("UPDATE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." SET footer_cheque_amount_signature_label = 'Received By' WHERE footer_cheque_amount_signature_label IS NULL");
    
            $stmt->execute();

            $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added footer_cheque_amount_signature_label column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }

        if($footerFormatColumnExists &&
        $footerBankLabelColumnExists && $footerBankSignatureLabelColumnExists &&
        $footerChequeNumberLabelColumnExists && $footerChequeNumberSignatureLabelColumnExists &&
        $footerChequeDateLabelColumnExists && $footerChequeDateSignatureLabelColumnExists &&
        $footerChequeAmountLabelColumnExists && $footerChequeAmountSignatureLabelColumnExists)
        {
            return $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'All footer settings columns in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' already exists!');
        }
        else
        {
            return $this->logSection('2_1_0-13-add_footer_setting_columns_in_claim_certificate_print_settings_table', 'Successfully added all footer settings columns in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
    }
}
