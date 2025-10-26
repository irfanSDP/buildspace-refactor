<?php

class add_lag_columns_in_schedule_task_item_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_8_0_8_add_lag_column_in_schedule_task_item_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_8_0_8_add_lag_column_in_schedule_task_item_table|INFO] task does things.
Call it with:

  [php symfony 1_8_0_8_add_lag_column_in_schedule_task_item_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".ScheduleTaskItemTable::getInstance()->getTableName()."' and column_name ='lag');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_8_0_8_add_lag_column_in_schedule_task_item_table', 'Column lag already exists in ScheduleTaskItem table!');
        }
        
        $stmt = $con->prepare("ALTER TABLE ".ScheduleTaskItemTable::getInstance()->getTableName()." ADD COLUMN lag BIGINT DEFAULT 0 NOT NULL");

        $stmt->execute();

        return $this->logSection('1_8_0_8_add_lag_column_in_schedule_task_item_table', 'Successfully added column lag in ScheduleTaskItem table!');
    }
}