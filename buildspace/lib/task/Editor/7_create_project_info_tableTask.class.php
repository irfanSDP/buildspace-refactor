<?php

class create_project_information_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'editor';
        $this->name                = '7-create_project_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [7-create_project_information_table|INFO] task does things.
Call it with:

  [php symfony 7-create_project_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(EditorProjectInformationTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('7-create_project_information_table', 'Table '.EditorProjectInformationTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".EditorProjectInformationTable::getInstance()->getTableName()." (id BIGSERIAL, project_structure_id BIGINT NOT NULL, company_id BIGINT NOT NULL, printing_revision_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX editor_project_info_unique_idx ON ".EditorProjectInformationTable::getInstance()->getTableName()." (project_structure_id, company_id);",
            "CREATE INDEX editor_project_info_id_idx ON ".EditorProjectInformationTable::getInstance()->getTableName()." (id, project_structure_id, company_id);",
            "CREATE INDEX editor_project_info_fk_idx ON ".EditorProjectInformationTable::getInstance()->getTableName()." (project_structure_id, company_id, printing_revision_id);",
            "ALTER TABLE ".EditorProjectInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_project_info_print_revision_id FOREIGN KEY (printing_revision_id) REFERENCES ".ProjectRevisionTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorProjectInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_project_info_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorProjectInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_project_info_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorProjectInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_project_info_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".EditorProjectInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_editor_project_info_company_id FOREIGN KEY (company_id) REFERENCES ".CompanyTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('7-create_project_information_table', 'Successfully created '.EditorProjectInformationTable::getInstance()->getTableName().' table!');
    }
}
