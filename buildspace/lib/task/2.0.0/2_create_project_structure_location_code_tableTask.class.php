<?php

class create_project_structure_location_code_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-2-create_project_structure_location_code_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-2-create_project_structure_location_code_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-2-create_project_structure_location_code_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ProjectStructureLocationCodeTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-2-create_project_structure_location_code_table', 'Table '.ProjectStructureLocationCodeTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".ProjectStructureLocationCodeTable::getInstance()->getTableName()." (id BIGSERIAL, description TEXT, project_structure_id BIGINT NOT NULL, priority BIGINT DEFAULT 0 NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, root_id BIGINT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id));",
            "CREATE INDEX project_structure_location_codes_id_idx ON ".ProjectStructureLocationCodeTable::getInstance()->getTableName()." (id, root_id, lft, rgt);",
            "CREATE INDEX project_structure_location_codes_fk_idx ON ".ProjectStructureLocationCodeTable::getInstance()->getTableName()." (root_id, project_structure_id);",
            "ALTER TABLE ".ProjectStructureLocationCodeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_project_structure_location_codes_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ProjectStructureLocationCodeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_project_structure_location_codes_project_structure_id_BS_project_structure FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ProjectStructureLocationCodeTable::getInstance()->getTableName()." ADD CONSTRAINT BS_project_structure_location_codes_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-2-create_project_structure_location_code_table', 'Successfully created '.ProjectStructureLocationCodeTable::getInstance()->getTableName().' table!');
    }
}