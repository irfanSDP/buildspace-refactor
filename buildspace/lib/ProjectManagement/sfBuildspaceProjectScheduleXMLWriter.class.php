<?php

class sfBuildspaceProjectScheduleXMLWriter extends sfBuildspaceXMLGenerator
{
    const TAG_PROJECT_SCHEDULE            = "PROJECT_SCHEDULE";
    const TAG_PROJECT_SCHEDULE_TASK_ITEMS = "TAG_PROJECT_SCHEDULE_TASK_ITEMS";
    const TAG_TASK_ITEM                   = "TAG_TASK_ITEM";

    const FILE_EXT                        = "epm";

    protected $projectSchedule;
    protected $pdo;

    function __construct($filename = null, ProjectSchedule $projectSchedule, $deleteFile = false)
    {
        $savePath = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

        $this->projectSchedule = $projectSchedule;
        $this->pdo	= ProjectScheduleTable::getInstance()->getConnection()->getDbh();


        parent::__construct( $filename, $savePath, "xml", $deleteFile );

    }

    public function process( $write = true )
    {
        parent::create( self::TAG_PROJECT_SCHEDULE, array(
            'exportType'        => ExportedFile::EXPORT_TYPE_PROJECT_SCHEDULE,
            'title'             => $this->projectSchedule->title,
            'description'       => $this->projectSchedule->description,
            'type'              => $this->projectSchedule->type,
            'exclude_saturdays' => $this->projectSchedule->exclude_saturdays,
            'exclude_sundays'   => $this->projectSchedule->exclude_sundays,
            'timezone'          => $this->projectSchedule->timezone,
            'start_date'        => $this->projectSchedule->start_date,
            'zoom'              => $this->projectSchedule->zoom
        ));

        $taskItemsTag = parent::createTag( self::TAG_PROJECT_SCHEDULE_TASK_ITEMS );

        foreach($this->projectSchedule->getTaskItems()->toArray() as $taskItem)
        {
            parent::addChildTag($taskItemsTag, self::TAG_TASK_ITEM, $taskItem );

        }

        if($write)
            parent::write();
    }

}