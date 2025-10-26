<?php

class create_new_post_contract_form_information_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-10-create_new_post_contract_form_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-10-create_new_post_contract_form_information_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-10-create_new_post_contract_form_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-10-create_new_post_contract_form_information_table', 'Table '.NewPostContractFormInformationTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." (id BIGSERIAL, type BIGINT NOT NULL, form_number BIGINT NOT NULL, project_structure_id BIGINT NOT NULL, contract_period_from TIMESTAMP NOT NULL, contract_period_to TIMESTAMP NOT NULL, pre_defined_location_code_id BIGINT NOT NULL, creditor_code TEXT, remarks TEXT, retention NUMERIC(5,2) DEFAULT 0, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX new_post_contract_form_information_project_structure_id_unique_idx ON ".NewPostContractFormInformationTable::getInstance()->getTableName()." (project_structure_id);",
            "CREATE INDEX new_post_contract_form_information_project_structure_id_idx ON ".NewPostContractFormInformationTable::getInstance()->getTableName()." (project_structure_id);",
            "CREATE INDEX new_post_contract_form_information_id_idx ON ".NewPostContractFormInformationTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_new_post_contract_form_info_project_structure_id FOREIGN KEY (project_structure_id) REFERENCES ".ProjectStructureTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD CONSTRAINT BS_new_post_contract_form_info_pre_defined_location_code_id FOREIGN KEY (pre_defined_location_code_id) REFERENCES ".PreDefinedLocationCodeTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-10-create_new_post_contract_form_information_table', 'Successfully created '.NewPostContractFormInformationTable::getInstance()->getTableName().' table!');
    }
}
