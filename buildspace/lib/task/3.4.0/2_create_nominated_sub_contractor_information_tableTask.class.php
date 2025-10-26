<?php

class create_nominated_sub_contractor_information_tableTask extends sfBaseTask
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
        $this->name                = '3_4_0-2-create_nominated_sub_contractor_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3_4_0-2-create_nominated_sub_contractor_information_table|INFO] task does things.
Call it with:

  [php symfony 3_4_0-2-create_nominated_sub_contractor_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = NominatedSubContractorInformationTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('3_4_0-2-create_nominated_sub_contractor_information_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, project_structure_id BIGINT NOT NULL, sub_project_id BIGINT NOT NULL, profit_and_attendance_percentage NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX nominated_sub_contractor_info_unique_idx ON {$tableName} (project_structure_id, sub_project_id);",
            "CREATE INDEX nominated_sub_contractor_info_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT nominated_sub_contractor_information_project_structure_id_fk FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT nominated_sub_contractor_information_updated_by_fk FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT nominated_sub_contractor_information_sub_project_id_fk FOREIGN KEY (sub_project_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT nominated_sub_contractor_information_created_by_fk FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('3_4_0-2-create_nominated_sub_contractor_information_table', "Successfully created {$tableName} table!");
    }
}
