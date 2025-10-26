<?php

class add_new_label_columns_claim_certificate_print_settings_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '3_6_1-2-add_new_label_columns_claim_certificate_print_settings_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
        The [3_6_1-2-add_new_label_columns_claim_certificate_print_settings_table|INFO] task does things.
        Call it with:

        [php symfony 3_6_1-2-add_new_label_columns_claim_certificate_print_settings_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = strtolower(ClaimCertificatePrintSettingTable::getInstance()->getTableName());

        $columns = [
            "contractor_submitted_date_label",
            "site_verified_date_label",
            "certificate_received_date_label",
        ];

        foreach($columns as $columnName)
        {
            $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
                    AND table_name = '".$tableName."' and column_name = '".$columnName."');");

            $stmt->execute();
    
            $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            if($columnExists)
            {
                $this->logSection('3_6_1-2-add_new_label_columns_claim_certificate_print_settings_table', 'Column ' . $columnName . ' already exists in ' . $tableName . ' table!');
            }
            else
            {
                $stmt = $con->prepare("ALTER TABLE ".$tableName." ADD COLUMN ".$columnName." VARCHAR(255)");
    
                $stmt->execute();
    
                $this->logSection('3_6_1-2-add_new_label_columns_claim_certificate_print_settings_table', 'Successfully added ' . $columnName . ' column in ' . $tableName . ' table!');
            }
        }
    }
}
