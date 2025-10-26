<?php

class create_attachments_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-34-create_attachments_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-34-create_attachments_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-34-create_attachments_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(AttachmentsTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-34-create_attachments_table', 'Table '.AttachmentsTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".AttachmentsTable::getInstance()->getTableName()." (id BIGSERIAL, object_id BIGINT NOT NULL, object_class TEXT NOT NULL, filepath TEXT NOT NULL, filename TEXT NOT NULL, extension TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE INDEX attachments_non_unique_idx ON ".AttachmentsTable::getInstance()->getTableName()." (object_class);",
            "CREATE INDEX attachments_id_idx ON ".AttachmentsTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".AttachmentsTable::getInstance()->getTableName()." ADD CONSTRAINT BS_attachments_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".AttachmentsTable::getInstance()->getTableName()." ADD CONSTRAINT BS_attachments_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-34-create_attachments_table', 'Successfully created '.AttachmentsTable::getInstance()->getTableName().' table!');
    }
}