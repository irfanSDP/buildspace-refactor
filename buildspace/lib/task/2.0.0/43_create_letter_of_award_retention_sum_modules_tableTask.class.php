<?php

class create_letter_of_award_retention_sum_modules_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-43-create_letter_of_award_retention_sum_modules_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-43-create_letter_of_award_retention_sum_modules_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-43-create_letter_of_award_retention_sum_modules_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = LetterOfAwardRetentionSumModulesTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-43-create_letter_of_award_retention_sum_modules_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, new_post_contract_form_information_id BIGINT NOT NULL, type BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX la_retention_sum_modules_unique_idx ON {$tableName} (new_post_contract_form_information_id, type);",
            "CREATE INDEX la_retention_sum_modules_id_idx ON {$tableName} (id);",
            "CREATE INDEX la_retention_sum_modules_la_id_idx ON {$tableName} (new_post_contract_form_information_id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT la_retention_sum_modules_la_id FOREIGN KEY (new_post_contract_form_information_id) REFERENCES BS_new_post_contract_form_information(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-43-create_letter_of_award_retention_sum_modules_table', "Successfully created {$tableName} table!");
    }
}
