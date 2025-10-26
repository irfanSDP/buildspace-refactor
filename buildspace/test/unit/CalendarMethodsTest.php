<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';

$configuration = ProjectConfiguration::getApplicationConfiguration( 'backend', 'test', true);

$dbm = new sfDatabaseManager($configuration);
$con = Doctrine_Manager::getInstance()->getConnection('main_conn');

try
{
    $con->beginTransaction();

    $region = DoctrineQuery::create()->select('r.id')
        ->from('Regions r')
        ->where('LOWER(r.country) = ?', 'malaysia')
        ->limit(1)
        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
        ->fetchOne();

    $subRegion = DoctrineQuery::create()
        ->from('Subregions sr')
        ->where('LOWER(sr.name) = ?', 'selangor')
        ->andWhere('sr.region_id = ?', $region['id'])
        ->limit(1)
        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
        ->fetchOne();

    $globalEvent1 = new GlobalCalendar();

    $globalEvent1->description = "Christmas";
    $globalEvent1->region_id = $region['id'];
    $globalEvent1->event_type = GlobalCalendar::EVENT_TYPE_PUBLIC;
    $globalEvent1->start_date = '2015-12-25';
    $globalEvent1->end_date = '2015-12-26';
    $globalEvent1->save($con);

    $globalEvent2 = new GlobalCalendar();

    $globalEvent2->description = "Sultans' Selangor Birthday";
    $globalEvent2->region_id = $region['id'];
    $globalEvent2->subregion_id = $subRegion['id'];
    $globalEvent2->event_type = GlobalCalendar::EVENT_TYPE_PUBLIC;
    $globalEvent2->start_date = '2015-12-11';
    $globalEvent2->end_date = '2015-12-11';
    $globalEvent2->save($con);

    $globalCalendarEvents = DoctrineQuery::create()->select('e.start_date, e.end_date')
        ->from('GlobalCalendar e')
        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
        ->execute();

    $project = createProject($region['id'], $subRegion['id'], $con);

    $projectSchedule = new ProjectSchedule();
    $projectSchedule->title = "PROJECT SCHEDULE A";
    $projectSchedule->type = ProjectSchedule::TYPE_MAIN_PROJECT;
    $projectSchedule->start_date = "2015-01-01";
    $projectSchedule->timezone = $subRegion['timezone'];
    $projectSchedule->project_structure_id = $project->id;
    $projectSchedule->save($con);

    $nonWorkingDay1 = new ProjectManagementCalendar();
    $nonWorkingDay1->project_structure_id = $project->id;
    $nonWorkingDay1->description = "Non Working Day 1";
    $nonWorkingDay1->event_type = GlobalCalendar::EVENT_TYPE_PUBLIC;
    $nonWorkingDay1->start_date = "2015-02-12";
    $nonWorkingDay1->end_date = "2015-02-14";
    $nonWorkingDay1->save($con);

    $nonWorkingDays = $projectSchedule->getNonWorkingDays();

    $t = new lime_test(18);

    $t->comment('Testing Utilities::isNonWorkingDays');
    $t->is(Utilities::isNonWorkingDay('2015-12-25', $nonWorkingDays), true, "Utilities::isNonWorkingDays detects 2015-12-25 as non working day");
    $t->is(Utilities::isNonWorkingDay('2015-12-14', $nonWorkingDays), false, "Utilities::isNonWorkingDays detects 2015-12-14 as working day");
    $t->is(Utilities::isNonWorkingDay('2015-02-12', $nonWorkingDays), true, "Utilities::isNonWorkingDays detects 2015-02-12 as non working day");
    $t->is(Utilities::isNonWorkingDay('2015-02-13', $nonWorkingDays), true, "Utilities::isNonWorkingDays detects 2015-02-13 (date in range) as non working day");

    $workingDay1 = new ScheduleCalendarEventWorkingDay();
    $workingDay1->project_schedule_id = $projectSchedule->id;
    $workingDay1->event_date = $nonWorkingDay1->start_date;
    $workingDay1->save($con);

    $t->comment('Changing non working day to working day');

    $nonWorkingDays = $projectSchedule->getNonWorkingDays();

    $t->is(Utilities::isNonWorkingDay($nonWorkingDay1->start_date, $nonWorkingDays), false, "Utilities::isNonWorkingDays detects 2015-02-12 as working day");

    $workingDay1->event_date = '2015-02-13';
    $workingDay1->save($con);

    $nonWorkingDays = $projectSchedule->getNonWorkingDays();

    $t->is(Utilities::isNonWorkingDay('2015-02-13', $nonWorkingDays), false, "Utilities::isNonWorkingDays detects 2015-02-13 (date in range) as working day");

    $t->comment('Validate weekends (Sat & Sun)');

    $t->is($projectSchedule->exclude_saturdays, true, "projectSchedule set exclude_saturdays to true");
    $t->is($projectSchedule->exclude_sundays, true, "projectSchedule set exclude_sundays to true");

    $t->is(Utilities::isNonWorkingDay('2015-01-24', $nonWorkingDays, $projectSchedule->exclude_saturdays, $projectSchedule->exclude_sundays), true, "Utilities::isNonWorkingDays detects 2015-01-24 (Sat) as non working day");
    $t->is(Utilities::isNonWorkingDay('2015-01-25', $nonWorkingDays, $projectSchedule->exclude_saturdays, $projectSchedule->exclude_sundays), true, "Utilities::isNonWorkingDays detects 2015-01-25 (Sun) as non working day");

    $projectSchedule->exclude_saturdays = false;
    $projectSchedule->save($con);

    $t->is($projectSchedule->exclude_saturdays, false, "projectSchedule set exclude_saturdays to false");
    $t->is(Utilities::isNonWorkingDay('2015-01-24', $nonWorkingDays, $projectSchedule->exclude_saturdays, $projectSchedule->exclude_sundays), false, "Utilities::isNonWorkingDays detects 2015-01-24 (Sat) as working day");

    $projectSchedule->exclude_sundays = false;
    $projectSchedule->save($con);

    $t->is($projectSchedule->exclude_sundays, false, "projectSchedule set exclude_sundays to false");
    $t->is(Utilities::isNonWorkingDay('2015-01-25', $nonWorkingDays, $projectSchedule->exclude_saturdays, $projectSchedule->exclude_sundays), false, "Utilities::isNonWorkingDays detects 2015-01-25 (Sun) as working day");


    $taskItemA = new ScheduleTaskItem();
    $taskItemA->description = "Task A";
    $taskItemA->type = ScheduleTaskItem::TYPE_ITEM;
    $taskItemA->project_schedule_id = $projectSchedule->id;
    $taskItemA->start_date = "2015-12-23";
    $taskItemA->end_date = "2015-12-25";

    $taskItemA->save($con);

    $t->comment('Testing computeStartDate(), computeEndDate(), computeEndDateByDuration(), distanceInWorkingDaysToDate()');

    $t->is($taskItemA->start_date, "2015-12-23", "Task item start date should be 2015-12-24");
    $t->is($taskItemA->end_date, "2015-12-26", "Task item end date should be 2015-12-26");

    $t->comment('Set task item start date to non working date 2015-12-25');

    $taskItemA->start_date = "2015-12-25";
    $taskItemA->save($con);

    $t->is($taskItemA->start_date, "2015-12-26", "Task item start date should be 2015-12-26");
    $t->is($taskItemA->end_date, "2015-12-28", "Task item start date should be 2015-12-28");

    //$con->commit();
    $con->rollback();
}
catch(Exception $e)
{
    $con->rollback();
    throw $e;
}

function createProject($regionId, $subRegionId, Doctrine_Connection $con)
{
    DoctrineQuery::create()
        ->update('ProjectStructure')
        ->set('priority', 'priority + 1')
        ->where('priority >= ?', 0)
        ->andWhere('id = root_id')
        ->execute();

    $project = new ProjectStructure();

    $project->type     = ProjectStructure::TYPE_ROOT; //to factor
    $project->title    = "Test Project ABC";
    $project->priority = 0;
    $project->save($con);

    ProjectStructureTable::getInstance()->getTree()->createRoot($project);

    $projectMainInfo = new ProjectMainInformation();

    $projectMainInfo->title = $project->title;
    $projectMainInfo->project_structure_id = $project->id;
    $projectMainInfo->region_id = $regionId;
    $projectMainInfo->subregion_id = $subRegionId;
    $projectMainInfo->site_address = "ADDRESS 123";
    $projectMainInfo->status = ProjectMainInformation::STATUS_PRETENDER;
    $projectMainInfo->save($con);

    return $project;
}