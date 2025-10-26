<?php

class create_publish_to_post_contract_options_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-12-create_publish_to_post_contract_options_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-12-create_publish_to_post_contract_options_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-12-create_publish_to_post_contract_options_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(PublishToPostContractOptionTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-12-create_publish_to_post_contract_options_table', 'Table '.PublishToPostContractOptionTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".PublishToPostContractOptionTable::getInstance()->getTableName()." (id BIGSERIAL, project_structure_id BIGINT NOT NULL, with_not_listed_item BOOLEAN NOT NULL, rate_type BIGINT NOT NULL, assign_users_manually BOOLEAN DEFAULT 'false' NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX publish_to_post_contract_options_project_structure_id_unique_idx ON ".PublishToPostContractOptionTable::getInstance()->getTableName()." (project_structure_id);",
            "CREATE INDEX publish_to_post_contract_options_id_idx ON ".PublishToPostContractOptionTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".PublishToPostContractOptionTable::getInstance()->getTableName()." ADD CONSTRAINT BS_publish_to_post_contract_options_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-12-create_publish_to_post_contract_options_table', 'Successfully created '.PublishToPostContractOptionTable::getInstance()->getTableName().' table!');
    }
}
