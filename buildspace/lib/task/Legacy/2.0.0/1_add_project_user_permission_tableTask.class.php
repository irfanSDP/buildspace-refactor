<?php

class add_project_user_permission_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '2_0_0_1_add_project_user_permission_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0_1_add_project_user_permission_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0_1_add_project_user_permission_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(ProjectUserPermissionTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0_1_add_project_user_permission_table', 'Table '.ProjectUserPermissionTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".ProjectUserPermissionTable::getInstance()->getTableName()." (id BIGSERIAL, project_structure_id BIGINT NOT NULL, user_id BIGINT NOT NULL, project_status BIGINT NOT NULL, is_admin BOOLEAN DEFAULT 'false' NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX project_user_perm_unique_idx ON ".ProjectUserPermissionTable::getInstance()->getTableName()." (project_structure_id, user_id, project_status);",
            "CREATE INDEX project_user_perm_fk_idx ON ".ProjectUserPermissionTable::getInstance()->getTableName()." (project_structure_id, user_id);",
            "ALTER TABLE ".ProjectUserPermissionTable::getInstance()->getTableName()." ADD CONSTRAINT project_user_permissions_user_project_structure FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ProjectUserPermissionTable::getInstance()->getTableName()." ADD CONSTRAINT project_user_permissions_user_sf_guard_user FOREIGN KEY (user_id) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ProjectUserPermissionTable::getInstance()->getTableName()." ADD CONSTRAINT project_user_permissions_updated_by_sf_guard_user FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".ProjectUserPermissionTable::getInstance()->getTableName()." ADD CONSTRAINT project_user_permissions_created_by_sf_guard_user FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0_1_add_project_user_permission_table', 'Successfully added table '.ProjectUserPermissionTable::getInstance()->getTableName().'!');
    }
}