<?php

class update_timezone_valuesTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_3_0-1-update_timezone_values';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_3_0-1-update_timezone_values|INFO] task does things.
Call it with:

  [php symfony legacy-2_3_0-1-update_timezone_values|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("UPDATE ".myCompanyProfileTable::getInstance()->getTableName()." SET timezone = 'Asia/Kuala_Lumpur' ");

        $stmt->execute();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("UPDATE ".ProjectScheduleTable::getInstance()->getTableName()." SET timezone = 'Asia/Kuala_Lumpur' ");

        $stmt->execute();

        return $this->logSection('legacy-2_3_0-1-update_timezone_values', 'Successfully updated timezone values!');
    }
}