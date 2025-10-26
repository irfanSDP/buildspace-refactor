<?php

class sfBuildspaceProjectScheduleXMLReader extends sfXMLReaderParser
{
    private $pdo;
    private $conn;
    private $userId;
    private $project;
    private $subPackage;
    private $useTargetProjectStartDate;
    private $excludeSaturdays;
    private $excludeSundays;

    function __construct($userId, ProjectStructure $project, SubPackage $subPackage = null, $filename, $excludeSaturdays=true, $excludeSundays=true, $useTargetProjectStartDate = false, $uploadPath = null, $deleteFile = false, Doctrine_Connection $conn = null)
    {
        $this->project = $project;

        $this->conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $this->pdo = $conn->getDbh();

        $this->userId = $userId;

        $this->subPackage = $subPackage;

        $this->useTargetProjectStartDate = $useTargetProjectStartDate;

        $this->excludeSaturdays = $excludeSaturdays;

        $this->excludeSundays = $excludeSundays;

        parent::__construct($filename, $uploadPath, "xml", $deleteFile);
    }

    public function process()
    {
        $fileParsed = $this->getBySingleTag(sfBuildspaceProjectScheduleXMLWriter::TAG_PROJECT_SCHEDULE);

        if( $fileParsed )
        {
            $projectScheduleInfo = $fileParsed["@attributes"];

            if($projectScheduleId = $this->processProjectSchedule($projectScheduleInfo))
            {
                $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($projectScheduleId);

                $this->processTaskItems($fileParsed[sfBuildspaceProjectScheduleXMLWriter::TAG_PROJECT_SCHEDULE_TASK_ITEMS], $projectSchedule, $projectScheduleInfo);
            }

        }
    }

    private function processProjectSchedule($info)
    {
        $stmt = $this->pdo->prepare("INSERT INTO ".ProjectScheduleTable::getInstance()->getTableName()."
        (title, description, type, exclude_saturdays, exclude_sundays, start_date, timezone, project_structure_id, sub_package_id, created_at, updated_at, created_by, zoom)
        VALUES
        (:title, :description, :type, :exclude_saturdays, :exclude_sundays, :start_date, :timezone, :project_structure_id, :sub_package_id, NOW(), NOW(), :created_by, :zoom)
        RETURNING id");

        $stmt->execute(array(
            'title'                => $info['title'],
            'description'          => $info['description'],
            'type'                 => !empty($this->subPackage) ? ProjectSchedule::TYPE_SUB_PACKAGE : ProjectSchedule::TYPE_MAIN_PROJECT,
            'exclude_saturdays'    => ($this->excludeSaturdays) ? 'TRUE' : 'FALSE',
            'exclude_sundays'      => ($this->excludeSundays) ? 'TRUE' : 'FALSE',
            'start_date'           => $this->useTargetProjectStartDate ? $this->project->getMainInformation()->start_date : $info['start_date'],
            'timezone'             => $info['timezone'],
            'project_structure_id' => $this->project->id,
            'sub_package_id'       => !empty($this->subPackage) ? $this->subPackage->id : null,
            'created_by'           => $this->userId,
            'zoom'                 => $info['zoom']
        ));

        return $result = $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    private function processTaskItems(Array $taskItems, ProjectSchedule $projectSchedule, $projectScheduleInfoXml)
    {
        if(!array_key_exists(sfBuildspaceProjectScheduleXMLWriter::TAG_TASK_ITEM, $taskItems))
            return;

        $rowsSQL = array();
        $toBind = array();

        foreach ($taskItems[sfBuildspaceProjectScheduleXMLWriter::TAG_TASK_ITEM] as $idx => $taskItem)
        {
            $params = array();

            $params[] = ":title".$idx;
            $toBind[":title".$idx] = $taskItem['title'];

            $params[] = ":description".$idx;
            $toBind[":description".$idx] = !empty($taskItem['description']) ? $taskItem['description'] : "";

            $params[] = ":code".$idx;
            $toBind[":code".$idx] = !empty($taskItem['code']) ? $taskItem['code'] : NULL;

            $params[] = ":status".$idx;
            $toBind[":status".$idx] = $taskItem['status'];

            if($this->useTargetProjectStartDate)
            {
                $duration  = Utilities::distanceInWorkingDays($projectScheduleInfoXml['start_date'], $taskItem['start_date'], $projectSchedule->getNonWorkingDays(), $projectSchedule->exclude_saturdays, $projectSchedule->exclude_sundays);

                $startDate = $this->computeEndDateByStartDateAndDuration($projectSchedule, $this->project->MainInformation->start_date, $duration );
                $endDate   = $this->computeEndDateByStartDateAndDuration($projectSchedule, $startDate, $taskItem['duration'] );
            }
            else
            {
                $startDate = $taskItem['start_date'];
                $endDate   = $taskItem['end_date'];
            }

            $params[] = ":start_date".$idx;
            $toBind[":start_date".$idx] = $startDate;

            $params[] = ":end_date".$idx;
            $toBind[":end_date".$idx] = $endDate;

            $params[] = ":completed_date".$idx;
            $toBind[":completed_date".$idx] = (!$this->useTargetProjectStartDate && !empty($taskItem['completed_date'])) ? $taskItem['completed_date'] : NULL;

            $params[] = ":progress".$idx;
            $toBind[":progress".$idx] = $taskItem['progress'];

            $params[] = ":duration".$idx;
            $toBind[":duration".$idx] = $taskItem['duration'];

            $params[] = ":total_cost".$idx;
            $toBind[":total_cost".$idx] = $taskItem['total_cost'];

            $params[] = ":start_is_milestone".$idx;
            $toBind[":start_is_milestone".$idx] = !empty($taskItem['start_is_milestone']) ? 'TRUE' : 'FALSE';

            $params[] = ":end_is_milestone".$idx;
            $toBind[":end_is_milestone".$idx] = !empty($taskItem['end_is_milestone']) ? 'TRUE' : 'FALSE';

            $params[] = ":depends".$idx;
            $toBind[":depends".$idx] = !empty($taskItem['depends']) ? $taskItem['depends'] : NULL;

            $params[] = ":hours_per_day".$idx;
            $toBind[":hours_per_day".$idx] = $taskItem['hours_per_day'];

            $params[] = ":priority".$idx;
            $toBind[":priority".$idx] = $taskItem['priority'];

            $params[] = ":project_schedule_id".$idx;
            $toBind[":project_schedule_id".$idx] = $projectSchedule->id;

            $params[] = ":created_at".$idx;
            $toBind[":created_at".$idx] = 'NOW()';

            $params[] = ":updated_at".$idx;
            $toBind[":updated_at".$idx] = 'NOW()';

            $params[] = ":created_by".$idx;
            $toBind[":created_by".$idx] = $this->userId;

            $params[] = ":updated_by".$idx;
            $toBind[":updated_by".$idx] = $this->userId;

            $params[] = ":lft".$idx;
            $toBind[":lft".$idx] = $taskItem['lft'];

            $params[] = ":rgt".$idx;
            $toBind[":rgt".$idx] = $taskItem['rgt'];

            $params[] = ":level".$idx;
            $toBind[":level".$idx] = $taskItem['level'];

            $rowsSQL[] = "(" . implode(", ", $params) . ")";

            $rootIdToUpdate[$taskItem['id']] = $taskItem['root_id'];

        }

        if(!empty($rowsSQL))
        {
            $stmt = $this->pdo->prepare('INSERT INTO ' . ScheduleTaskItemTable::getInstance()->getTableName() . '
            (title, description, code, status, start_date, end_date, completed_date, progress, duration, total_cost, start_is_milestone, end_is_milestone,
            depends, hours_per_day, priority, project_schedule_id, created_at, updated_at, created_by, updated_by, lft, rgt, level)
            VALUES ' . implode(', ', $rowsSQL).' RETURNING id');

            foreach($toBind as $param => $val)
            {
                $stmt->bindValue($param, $val);
            }

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $taskItemIds = array();
            foreach($taskItems[sfBuildspaceProjectScheduleXMLWriter::TAG_TASK_ITEM] as $idx => $taskItem)
            {
                $taskItemIds[$taskItem['id']] = $result[$idx];
            }

            $rootIds = array();
            foreach($rootIdToUpdate as $originalId => $rootId)
            {
                $rootIds[] = "({$taskItemIds[$originalId]}, {$taskItemIds[$rootId]})";

            }

            if(!empty($rootIds))
            {
                $stmt = $this->pdo->prepare("UPDATE " . ScheduleTaskItemTable::getInstance()->getTableName() . " SET
                root_id = newValues.saved_root_id, updated_at = NOW() FROM (VALUES " . implode(', ', $rootIds) . ") AS newValues (saved_id, saved_root_id)
                WHERE id = newValues.saved_id AND project_schedule_id = ".$projectSchedule->id);

                $stmt->execute();
            }

        }
    }

    private function computeEndDateByStartDateAndDuration(ProjectSchedule $projectSchedule, $startDate, $duration)
    {
        $endDate = $startDate;
        $q       = $duration - 1;

        $nonWorkingDays   = $projectSchedule->getNonWorkingDays();
        $excludeSaturdays = $projectSchedule->exclude_saturdays;
        $excludeSundays   = $projectSchedule->exclude_sundays;

        while($q > 0)
        {
            $endDate = date('Y-m-d',strtotime("+1 day", strtotime($endDate)));

            if(!Utilities::isNonWorkingDay($endDate, $nonWorkingDays, $excludeSaturdays, $excludeSundays))
                $q--;
        }

        return $endDate;
    }
}
