<?php

class create_new_post_contract_form_information_sub_package_work_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-11-create_new_post_contract_form_information_sub_package_work_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-11-create_new_post_contract_form_information_sub_package_work_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-11-create_new_post_contract_form_information_sub_package_work_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('2_0_0-11-create_new_post_contract_form_information_sub_package_work_table', 'Table '.NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName()." (id BIGSERIAL, new_post_contract_form_information_id BIGINT NOT NULL, sub_package_work_id BIGINT NOT NULL, sub_package_work_type BIGINT NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX sub_package_unit_information_sub_package_work_unique_idx ON ".NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName()." (new_post_contract_form_information_id, sub_package_work_type);",
            "CREATE INDEX sub_package_unit_information_sub_package_work_new_post_contract_information_id_idx ON ".NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName()." (new_post_contract_form_information_id);",
            "CREATE INDEX sub_package_unit_information_sub_package_work_id_idx ON ".NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName()." (id);",
            "ALTER TABLE ".NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName()." ADD CONSTRAINT BS_new_post_contract_form_information_work_id FOREIGN KEY (sub_package_work_id) REFERENCES ".SubPackageWorksTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName()." ADD CONSTRAINT BS_new_post_contract_form_information_form_info_id FOREIGN KEY (new_post_contract_form_information_id) REFERENCES ".NewPostContractFormInformationTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-11-create_new_post_contract_form_information_sub_package_work_table', 'Successfully created '.NewPostContractFormInformationSubPackageWorkTable::getInstance()->getTableName().' table!');
    }
}
