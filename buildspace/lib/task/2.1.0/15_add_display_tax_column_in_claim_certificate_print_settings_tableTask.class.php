<?php

class add_display_tax_column_in_claim_certificate_print_settings_tableTask extends sfBaseTask
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
        $this->name                = '2_1_0-15-add_display_tax_column_in_claim_certificate_print_settings_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_1_0-15-add_display_tax_column_in_claim_certificate_print_settings_table|INFO] task does things.
Call it with:

  [php symfony 2_1_0-15-add_display_tax_column_in_claim_certificate_print_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName())."' and column_name = 'display_tax_column');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            return $this->logSection('2_1_0-15-add_display_tax_column_in_claim_certificate_print_settings_table', 'Column display_tax_column already exists in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
        }
        
        $stmt = $con->prepare("ALTER TABLE ".ClaimCertificatePrintSettingTable::getInstance()->getTableName()." ADD COLUMN display_tax_column BOOLEAN DEFAULT 'true' NOT NULL");
    
        $stmt->execute();

        return $this->logSection('2_1_0-15-add_display_tax_column_in_claim_certificate_print_settings_table', 'Successfully added display_tax_column column in '.ClaimCertificatePrintSettingTable::getInstance()->getTableName().' table!');
    }
}
