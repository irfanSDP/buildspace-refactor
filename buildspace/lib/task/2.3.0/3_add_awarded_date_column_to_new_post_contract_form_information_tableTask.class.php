<?php

class add_awarded_date_column_to_new_post_contract_form_information_tableTask extends sfBaseTask
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
        $this->name                = '2_3_0-3-add_awarded_date_column_to_new_post_contract_form_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_3_0-3-add_awarded_date_column_to_new_post_contract_form_information_table|INFO] task does things.
Call it with:

  [php symfony 2_3_0-3-add_awarded_date_column_to_new_post_contract_form_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."' and column_name = 'awarded_date');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            return $this->logSection('2_3_0-3-add_awarded_date_column_to_new_post_contract_form_information_table', 'Column awarded_date already exists in '.NewPostContractFormInformationTable::getInstance()->getTableName().' table!');
        }

        $queries = array(
            "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD COLUMN awarded_date TIMESTAMP;",
            "UPDATE ".NewPostContractFormInformationTable::getInstance()->getTableName()." SET awarded_date = created_at;",
            "ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ALTER COLUMN awarded_date SET NOT NULL;",
        );

        foreach($queries as $query)
        {
            $stmt = $con->prepare($query);
    
            $stmt->execute();
        }

        return $this->logSection('2_3_0-3-add_awarded_date_column_to_new_post_contract_form_information_table', 'Successfully added display_tax_column column in '.NewPostContractFormInformationTable::getInstance()->getTableName().' table!');
    }
}
