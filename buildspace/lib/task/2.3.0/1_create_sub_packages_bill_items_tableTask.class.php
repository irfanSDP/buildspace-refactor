<?php

class create_sub_packages_bill_items_tableTask extends sfBaseTask
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
        $this->name                = '2_3_0-1-create_sub_packages_bill_items_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_3_0-1-create_sub_packages_bill_items_table|INFO] task does things.
Call it with:

  [php symfony 2_3_0-1-create_sub_packages_bill_items_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(SubPackageBillItemTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_3_0-1-create_sub_packages_bill_items_table', 'Table '.SubPackageBillItemTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . SubPackageBillItemTable::getInstance()->getTableName() . " (id BIGSERIAL, sub_package_id BIGINT NOT NULL, bill_item_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX sub_package_bill_item_unique_idx ON " . SubPackageBillItemTable::getInstance()->getTableName() . " (sub_package_id, bill_item_id);",
            "CREATE INDEX sub_package_bill_item_id_idx ON " . SubPackageBillItemTable::getInstance()->getTableName() . " (id);",
            "CREATE INDEX sub_package_bill_item_fk_idx ON " . SubPackageBillItemTable::getInstance()->getTableName() . " (sub_package_id, bill_item_id);",
            "ALTER TABLE " . SubPackageBillItemTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_sub_packages_bill_items_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . SubPackageBillItemTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_sub_packages_bill_items_sub_package_id_BS_sub_packages_id FOREIGN KEY (sub_package_id) REFERENCES BS_sub_packages(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . SubPackageBillItemTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_sub_packages_bill_items_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . SubPackageBillItemTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_sub_packages_bill_items_bill_item_id_BS_bill_items_id FOREIGN KEY (bill_item_id) REFERENCES BS_bill_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_3_0-1-create_sub_packages_bill_items_table', 'Successfully created '.SubPackageBillItemTable::getInstance()->getTableName().' table!');
    }
}
