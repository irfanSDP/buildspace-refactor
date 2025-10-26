<?php

class add_schedule_calendar_event_working_day_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_8_0_4_schedule_calendar_event_working_day_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_8_0_4_schedule_calendar_event_working_day_table|INFO] task does things.
Call it with:

  [php symfony 1_8_0_4_schedule_calendar_event_working_day_table|INFO]
EOF;
    }

    private static $queries = array(
        "CREATE TABLE BS_project_schedule_calendar_event_working_days (id BIGSERIAL, event_date DATE NOT NULL, project_schedule_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id))",
        "CREATE UNIQUE INDEX pscewd_unique_idx ON BS_project_schedule_calendar_event_working_days (project_schedule_id, event_date)",
        "CREATE INDEX pscewd_id_idx ON BS_project_schedule_calendar_event_working_days (id, project_schedule_id)",
        "CREATE INDEX pscewd_fk_idx ON BS_project_schedule_calendar_event_working_days (project_schedule_id)",
        "ALTER TABLE BS_project_schedule_calendar_event_working_days ADD CONSTRAINT pscewd_fk_csd_idx FOREIGN KEY (project_schedule_id) REFERENCES BS_project_schedules(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
    );

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".ScheduleCalendarEventWorkingDayTable::getInstance()->getTableName()."');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_8_0_4_schedule_calendar_event_working_day_table', 'Table for ScheduleCalendarEventWorkingDay has been added before!');
        }

        foreach ( self::$queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_8_0_4_schedule_calendar_event_working_day_table', 'Successfully created ScheduleCalendarEventWorkingDay table!');
    }
}