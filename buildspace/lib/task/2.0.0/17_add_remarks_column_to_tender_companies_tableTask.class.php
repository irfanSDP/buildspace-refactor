<?php

class add_remarks_column_to_tender_companies_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-17-add_remarks_column_to_tender_companies_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-17-add_remarks_column_to_tender_companies_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-17-add_remarks_column_to_tender_companies_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(TenderCompanyTable::getInstance()->getTableName())."' and column_name ='remarks');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            return $this->logSection('2_0_0-17-add_remarks_column_to_tender_companies_table', 'Column remarks already exists in '.TenderCompanyTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("ALTER TABLE ".TenderCompanyTable::getInstance()->getTableName()." ADD COLUMN remarks TEXT");

        $stmt->execute();

        return $this->logSection('2_0_0-17-add_remarks_column_to_tender_companies_table', 'Successfully added column remarks in '.TenderCompanyTable::getInstance()->getTableName().' table!');
    }
}
