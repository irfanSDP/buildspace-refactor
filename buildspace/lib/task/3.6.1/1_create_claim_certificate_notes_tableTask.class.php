<?php

class create_claim_certificate_notes_tableTask extends sfBaseTask
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
        $this->name                = '3_6_1-1-create_claim_certificate_notes_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3_6_1-1-create_claim_certificate_notes_table|INFO] task does things.
Call it with:

  [php symfony 3_6_1-1-create_claim_certificate_notes_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ClaimCertificateNoteTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( !$isTableExists )
        {
            $queries = [
                "CREATE TABLE ".ClaimCertificateNoteTable::getInstance()->getTableName()." (id BIGSERIAL, claim_certificate_id BIGINT NOT NULL, note text, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
                "CREATE UNIQUE INDEX claim_certificate_notes_unique_idx ON ".ClaimCertificateNoteTable::getInstance()->getTableName()." (claim_certificate_id);",
                "CREATE INDEX claim_certificate_notes_id_idx ON ".ClaimCertificateNoteTable::getInstance()->getTableName()." (id, created_by);",
                "CREATE INDEX claim_certificate_notes_fk_idx ON ".ClaimCertificateNoteTable::getInstance()->getTableName()." (claim_certificate_id);",
                "ALTER TABLE ".ClaimCertificateNoteTable::getInstance()->getTableName()." ADD CONSTRAINT claim_certificate_notes_claim_cert_id FOREIGN KEY (claim_certificate_id) REFERENCES ".ClaimCertificateTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE ".ClaimCertificateNoteTable::getInstance()->getTableName()." ADD CONSTRAINT claim_certificate_notes_updated_by FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
                "ALTER TABLE ".ClaimCertificateNoteTable::getInstance()->getTableName()." ADD CONSTRAINT claim_certificate_notes_created_by FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;"
            ];
    
            foreach($queries as $query)
            {
                $stmt = $con->prepare($query);
                $stmt->execute();
            }
            
           return $this->logSection('3_6_1-1-create_claim_certificate_notes_table', 'Successfully added '.ClaimCertificateNoteTable::getInstance()->getTableName().' table!');
        }
        else
        {
           return $this->logSection('3_6_1-1-create_claim_certificate_notes_table', 'Table '.ClaimCertificateNoteTable::getInstance()->getTableName().' already exists!');
        }
    }
}
