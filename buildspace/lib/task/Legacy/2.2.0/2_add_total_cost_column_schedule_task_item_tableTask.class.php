<?php

class add_total_cost_column_schedule_task_item_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_2_0-2-add_total_cost_column_schedule_task_item_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_2_0-2-add_total_cost_column_schedule_task_item_table|INFO] task does things.
Call it with:

  [php symfony legacy-2_2_0-2-add_total_cost_column_schedule_task_item_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(ScheduleTaskItemTable::getInstance()->getTableName())."' and column_name ='total_cost');");

        $stmt->execute();

        $isColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ($isColumnExists )
        {
            return $this->logSection('legacy-2_2_0-2-add_total_cost_column_schedule_task_item_table', 'total_cost column already exists in ScheduleTaskItem table!');
        }

        $queries = array(
            "ALTER TABLE ".ScheduleTaskItemTable::getInstance()->getTableName()." ADD COLUMN total_cost NUMERIC(18,5) DEFAULT 0",
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

            return $this->logSection('legacy-2_2_0-2-add_total_cost_column_schedule_task_item_table', 'Successfully added total_cost column in ScheduleTaskItem table!');
        }
        catch(Exception $e)
        {
            $con->rollBack();

            return $this->logSection('legacy-2_2_0-2-add_total_cost_column_schedule_task_item_table', $e);
        }

    }
}