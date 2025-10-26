<?php

class refactor_companies_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_1_0-2-refactor_companies_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_1_0-2-refactor_companies_table|INFO] task does things.
Call it with:

  [php symfony legacy-2_1_0-2-refactor_companies_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table and column existence, if exists then proceed with migration query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(CompanyTable::getInstance()->getTableName())."' AND column_name='company_business_type_id');");

        $stmt->execute();

        $isColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if (!$isColumnExists )
        {
            return $this->logSection('legacy-2_1_0-2-refactor_companies_table', 'Table '.CompanyTable::getInstance()->getTableName().' is already migrated!');
        }

        $queries = array(
            "DELETE FROM ".PurchaseOrderSupplierTable::getInstance()->getTableName()." WHERE company_id IN (SELECT id FROM ".CompanyTable::getInstance()->getTableName()." WHERE registration_no IS NULL OR registration_no='');",
            "DELETE FROM ".RFQSupplierTable::getInstance()->getTableName()." WHERE company_id IN (SELECT id FROM ".CompanyTable::getInstance()->getTableName()." WHERE registration_no IS NULL OR registration_no='');",
            "DELETE FROM ".PurchaseOrderSupplierTable::getInstance()->getTableName()." WHERE company_id IN (SELECT id FROM ".CompanyTable::getInstance()->getTableName()." WHERE reference_id IS NULL OR reference_id='');",
            "DELETE FROM ".RFQSupplierTable::getInstance()->getTableName()." WHERE company_id IN (SELECT id FROM ".CompanyTable::getInstance()->getTableName()." WHERE reference_id IS NULL OR reference_id='');",
            "DELETE FROM ".CompanyTable::getInstance()->getTableName()." WHERE reference_id IS NULL OR reference_id = '';",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." DROP COLUMN postcode",
            "UPDATE pg_attribute SET atttypmod = 60+4 WHERE attrelid = '".strtolower(CompanyTable::getInstance()->getTableName())."'::regclass AND attname = 'registration_no';",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN registration_no SET NOT NULL;",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN reference_id SET NOT NULL;",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN address SET NOT NULL;",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN region_id SET NOT NULL;",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN sub_region_id SET NOT NULL;",
            "UPDATE ".CompanyTable::getInstance()->getTableName()." SET phone_number = ' ' WHERE phone_number IS NULL",
            "UPDATE ".CompanyTable::getInstance()->getTableName()." SET fax_number = ' ' WHERE fax_number IS NULL",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN phone_number SET NOT NULL;",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." ALTER COLUMN fax_number SET NOT NULL;",
            "CREATE UNIQUE INDEX company_registration_no_unique_idx ON ".CompanyTable::getInstance()->getTableName()." (registration_no);",
            "ALTER TABLE ".CompanyTable::getInstance()->getTableName()." DROP COLUMN company_business_type_id"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('legacy-2_1_0-2-refactor_companies_table', 'Successfully refactored table '.CompanyTable::getInstance()->getTableName().'!');
    }
}