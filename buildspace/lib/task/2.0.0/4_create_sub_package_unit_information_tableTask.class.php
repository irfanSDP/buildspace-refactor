<?php

class create_sub_package_unit_information_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-4-create_sub_package_unit_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-4-create_sub_package_unit_information_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-4-create_sub_package_unit_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(SubPackageUnitInformationTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-4-create_sub_package_unit_information_table', 'Table '.SubPackageUnitInformationTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".SubPackageUnitInformationTable::getInstance()->getTableName()." (id BIGSERIAL, bill_column_setting_id BIGINT NOT NULL, counter BIGINT NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX sub_package_unit_information_unique_idx ON ".SubPackageUnitInformationTable::getInstance()->getTableName()." (bill_column_setting_id, counter);",
            "CREATE INDEX sub_package_unit_information_id_idx ON ".SubPackageUnitInformationTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX sub_package_unit_information_fk_idx ON ".SubPackageUnitInformationTable::getInstance()->getTableName()." (bill_column_setting_id);",
            "ALTER TABLE ".SubPackageUnitInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_sub_package_unit_information_bill_column_setting_id FOREIGN KEY (bill_column_setting_id) REFERENCES ".BillColumnSettingTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-4-create_sub_package_unit_information_table', 'Successfully created '.SubPackageUnitInformationTable::getInstance()->getTableName().' table!');
    }
}
