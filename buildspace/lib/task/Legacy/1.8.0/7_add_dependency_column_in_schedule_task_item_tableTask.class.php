<?php

class add_dependency_columns_in_schedule_task_item_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_8_0_7_add_dependency_column_in_schedule_tak_item_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_8_0_7_add_dependency_column_in_schedule_tak_item_table|INFO] task does things.
Call it with:

  [php symfony 1_8_0_7_add_dependency_column_in_schedule_tak_item_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".ScheduleTaskItemTable::getInstance()->getTableName()."' and column_name ='successor_id');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_8_0_7_add_dependency_column_in_schedule_tak_item_table', 'Column successor_id already exists in ScheduleTaskItem table!');
        }

        $queries = array(
            "ALTER TABLE ".ScheduleTaskItemTable::getInstance()->getTableName()." ADD COLUMN successor_id BIGINT",
            "ALTER TABLE ".ScheduleTaskItemTable::getInstance()->getTableName()." ADD CONSTRAINT schedule_task_item_successor_fk FOREIGN KEY (successor_id)
            REFERENCES ".ScheduleTaskItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_8_0_7_add_dependency_column_in_schedule_tak_item_table', 'Successfully added column successor_id in ScheduleTaskItem table!');
    }
}