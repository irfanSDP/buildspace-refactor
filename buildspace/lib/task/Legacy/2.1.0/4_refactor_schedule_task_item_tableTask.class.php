<?php

class refactor_schedule_task_item_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_1_0-4-refactor_schedule_task_item_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_1_0-4-refactor_schedule_task_item_table|INFO] task does things.
Call it with:

  [php symfony legacy-2_1_0-4-refactor_schedule_task_item_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ScheduleTaskItemTable::getInstance()->getTableName())."' and column_name ='lag');");

        $stmt->execute();

        $isColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if (!$isColumnExists )
        {
            return $this->logSection('legacy-2_1_0-4-refactor_schedule_task_item_table', 'ScheduleTaskItem table already refactored!');
        }

        $queries = array(
            "DROP TABLE IF EXISTS bs_schedule_task_item_notes CASCADE",
            "TRUNCATE ".ScheduleTaskItemTable::getInstance()->getTableName()." CASCADE",
            "ALTER TABLE ".ScheduleTaskItemTable::getInstance()->getTableName()." DROP COLUMN type, DROP COLUMN date_completed, DROP COLUMN completion_percentage, DROP COLUMN deleted_at, DROP COLUMN successor_id, DROP COLUMN lag",
            "ALTER TABLE ".ScheduleTaskItemTable::getInstance()->getTableName()." ADD COLUMN completed_date DATE, ADD COLUMN title VARCHAR(255) NOT NULL, ADD COLUMN code VARCHAR(100), ADD COLUMN status INT NOT NULL, ADD COLUMN progress NUMERIC(5,2) DEFAULT 0, ADD COLUMN duration INT NOT NULL, ADD COLUMN start_is_milestone BOOLEAN DEFAULT 'false', ADD COLUMN end_is_milestone BOOLEAN DEFAULT 'false', ADD COLUMN temp_deleted BOOLEAN DEFAULT 'false', ADD COLUMN depends TEXT",
            "CREATE INDEX pst_items_status_idx ON BS_project_schedule_task_items (status)",
            "ALTER TABLE ".GlobalCalendarTable::getInstance()->getTableName()." ADD COLUMN is_holiday BOOLEAN NOT NULL DEFAULT 'true'",
            "ALTER TABLE ".ProjectManagementCalendarTable::getInstance()->getTableName()." ADD COLUMN is_holiday BOOLEAN NOT NULL DEFAULT 'true'",
            "ALTER TABLE ".ProjectScheduleTable::getInstance()->getTableName()." ADD COLUMN zoom VARCHAR(1) DEFAULT 'm' NOT NULL"
        );

        try
        {
            $con->beginTransaction();

            foreach ($queries as $query )
            {
                $stmt = $con->prepare($query);

                $stmt->execute();
            }

            $con->commit();

            return $this->logSection('legacy-2_1_0-4-refactor_schedule_task_item_table', 'Successfully refactored ScheduleTaskItem table!');
        }
        catch(Exception $e)
        {
            $con->rollBack();

            return $this->logSection('legacy-2_1_0-4-refactor_schedule_task_item_table', $e);
        }

    }
}