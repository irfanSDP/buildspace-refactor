<?php

class create_sub_package_works_tableTaskTask extends sfBaseTask
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
        $this->name                = '2_0_0-9-create_sub_package_works_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-9-create_sub_package_works_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-9-create_sub_package_works_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(SubPackageWorksTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-9-create_sub_package_works_table', 'Table '.SubPackageWorksTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".SubPackageWorksTable::getInstance()->getTableName()." (id BIGSERIAL, name TEXT NOT NULL, type BIGINT NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX sub_package_works_name_unique_idx ON ".SubPackageWorksTable::getInstance()->getTableName()." (name);",
            "CREATE INDEX sub_package_works_type_idx ON ".SubPackageWorksTable::getInstance()->getTableName()." (type);",
            "CREATE INDEX sub_package_works_id_idx ON ".SubPackageWorksTable::getInstance()->getTableName()." (id);",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-9-create_sub_package_works_table', 'Successfully created '.SubPackageWorksTable::getInstance()->getTableName().' table!');
    }
}
