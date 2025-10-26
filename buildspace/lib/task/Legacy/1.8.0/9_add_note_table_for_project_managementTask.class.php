<?php

class add_note_table_for_project_managementTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_8_0_9_add_note_table_for_project_management';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_8_0_9_add_note_table_for_project_management|INFO] task does things.
Call it with:

  [php symfony 1_8_0_9_add_note_table_for_project_management|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".ScheduleTaskItemNoteTable::getInstance()->getTableName()."');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_8_0_9_add_note_table_for_project_management', 'Table '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' already exists!');
        }
        
        $queries = array(
        'CREATE TABLE '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' (id BIGSERIAL, schedule_task_item_id BIGINT NOT NULL, description text NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
        'CREATE UNIQUE INDEX schedule_task_item_note_fk_idx ON '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' (schedule_task_item_id);',
        'CREATE INDEX schedule_task_item_note_idx ON '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' (id, schedule_task_item_id);',
        'ALTER TABLE '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' ADD CONSTRAINT schedule_task_item_note_fk_cascade FOREIGN KEY (schedule_task_item_id) REFERENCES '.ScheduleTaskItemTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
        'ALTER TABLE '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' ADD CONSTRAINT BS_schedule_task_item_notes_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;',
        'ALTER TABLE '.ScheduleTaskItemNoteTable::getInstance()->getTableName().' ADD CONSTRAINT BS_schedule_task_item_notes_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES '.sfGuardUserTable::getInstance()->getTableName().'(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;'
        );
        
        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_8_0_9_add_note_table_for_project_management', 'Successfully added table '.ScheduleTaskItemTable::getInstance()->getTableName().'!');
    }
}