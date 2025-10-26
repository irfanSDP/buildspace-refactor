<?php

class create_location_assignment_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-3-create_location_assignment_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-3-create_location_assignment_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-3-create_location_assignment_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(LocationAssignmentTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-3-create_location_assignment_table', 'Table '.LocationAssignmentTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".LocationAssignmentTable::getInstance()->getTableName()." (id BIGSERIAL, pre_defined_location_code_id BIGINT NOT NULL, project_structure_location_code_id BIGINT NOT NULL, bill_item_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX location_assignments_unique_idx ON ".LocationAssignmentTable::getInstance()->getTableName()." (pre_defined_location_code_id, project_structure_location_code_id, bill_item_id);",
            "CREATE INDEX location_assignments_id_idx ON ".LocationAssignmentTable::getInstance()->getTableName()." (id, pre_defined_location_code_id, project_structure_location_code_id, bill_item_id);",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_assignments_project_structure_location_code_id FOREIGN KEY (project_structure_location_code_id) REFERENCES ".ProjectStructureLocationCodeTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_assignments_pre_defined_location_code_id FOREIGN KEY (pre_defined_location_code_id) REFERENCES ".PreDefinedLocationCodeTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_assignments_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_assignments_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".LocationAssignmentTable::getInstance()->getTableName()." ADD CONSTRAINT BS_location_assignments_bill_item_id_BS_bill_items_id FOREIGN KEY (bill_item_id) REFERENCES ".BillItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-3-create_location_assignment_table', 'Successfully created '.LocationAssignmentTable::getInstance()->getTableName().' table!');
    }
}