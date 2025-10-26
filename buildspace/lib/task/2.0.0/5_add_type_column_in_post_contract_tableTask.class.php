<?php

class add_published_type_column_in_post_contract_tableTaskTask extends sfBaseTask
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
        $this->name                = '2_0_0-5-add_published_type_column_in_post_contract_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-5-add_published_type_column_in_post_contract_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-5-add_published_type_column_in_post_contract_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(PostContractTable::getInstance()->getTableName())."' and column_name ='published_type');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-5-add_published_type_column_in_post_contract_table', 'Column published_type already exists in '.PostContractTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("ALTER TABLE ".ProjectMainInformationTable::getInstance()->getTableName()." DROP COLUMN IF EXISTS post_contract_type");

        $stmt->execute();


        $stmt = $con->prepare("ALTER TABLE ".PostContractTable::getInstance()->getTableName()." ADD COLUMN published_type BIGINT DEFAULT 1 NOT NULL");

        $stmt->execute();

        $stmt = $con->prepare("DROP INDEX post_contracts_id_idx");

        $stmt->execute();

        $stmt = $con->prepare("CREATE INDEX post_contracts_id_idx ON ".PostContractTable::getInstance()->getTableName()." (id, project_structure_id, published_type)");

        $stmt->execute();

        return $this->logSection('2_0_0-5-add_published_type_column_in_post_contract_table', 'Successfully added column published_type in '.PostContractTable::getInstance()->getTableName().' table!');
    }
}
