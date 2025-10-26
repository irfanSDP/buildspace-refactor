<?php

class create_tender_company_attachments_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-18-create_tender_company_attachments_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-18-create_tender_company_attachments_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-18-create_tender_company_attachments_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(TenderCompanyAttachmentsTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-18-create_tender_company_attachments_table', 'Table '.TenderCompanyAttachmentsTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . TenderCompanyAttachmentsTable::getInstance()->getTableName() . " (id BIGSERIAL, project_structure_id BIGINT NOT NULL, company_id BIGINT NOT NULL, filepath TEXT NOT NULL, filename TEXT NOT NULL, extension TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE INDEX tender_company_attachments_id_idx ON " . TenderCompanyAttachmentsTable::getInstance()->getTableName() . " (project_structure_id);",
            "ALTER TABLE " . TenderCompanyAttachmentsTable::getInstance()->getTableName() . " ADD CONSTRAINT BpBi_56 FOREIGN KEY (project_structure_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . TenderCompanyAttachmentsTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_tender_company_attachments_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . TenderCompanyAttachmentsTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_tender_company_attachments_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . TenderCompanyAttachmentsTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_tender_company_attachments_company_id_BS_companies_id FOREIGN KEY (company_id) REFERENCES BS_companies(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-18-create_tender_company_attachments_table', 'Successfully created '.TenderCompanyAttachmentsTable::getInstance()->getTableName().' table!');
    }
}
