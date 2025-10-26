<?php namespace PCK\ProjectReport;

use Carbon\Carbon;
use PCK\Base\Helpers;

class ProjectReportNotificationRepository
{
    public function getAllRecords($data=[])
    {
        $query1 = ProjectReportNotification::query();

        if (isset($data['projectId'])) {
            $query1->where('project_id', $data['projectId']);
        }
        if (isset($data['mappingId'])) {
            $query1->where('project_report_type_mapping_id', $data['mappingId']);
        }
        if (isset($data['notificationType'])) {
            $query1->where('notification_type', $data['notificationType']);
        } else {
            $query1->where('notification_type', ProjectReportNotification::TYPE_REMINDER);  // Default type
        }
        if (isset($data['isPublished'])) {
            $query1->where('is_published', (bool)$data['isPublished']);
        }

        return $query1->orderBy('id')->get();
    }

    public function getRecord($id)
    {
        return ProjectReportNotification::where('id', $id)->first();
    }

    public function getRecordsByMappingId($mappingId)
    {
        return ProjectReportNotification::where('project_report_type_mapping_id', $mappingId)->orderBy('id', 'desc')->get();
    }

    public function createRecord($projectId, $mappingId, $inputs)
    {
        $record = new ProjectReportNotification();
        $record->project_id = $projectId;
        $record->project_report_type_mapping_id = $mappingId;
        $this->populateRecord($record, $inputs);
        $record->save();

        if (isset($inputs['periodValue'])) {
            $this->syncPeriod($record->id, $inputs);
        }
        if (isset($inputs['subject']) || isset($inputs['body'])) {
            $this->saveContent($record->id, $inputs);
        }

        return $record->id;
    }

    public function updateRecord($recordId, $mappingId, $inputs)
    {
        $record = $this->getRecord($recordId);
        //$record->project_report_type_mapping_id = $mappingId;
        $this->populateRecord($record, $inputs);
        $record->save();

        if (isset($inputs['periodValue'])) {
            $this->syncPeriod($record->id, $inputs);
        }
        if (isset($inputs['subject']) || isset($inputs['body'])) {
            $this->saveContent($record->id, $inputs);
        }

        return true;
    }

    private function populateRecord($record, $inputs)
    {
        $record->category_column_id = $inputs['categoryColumn'];
        /*if (! empty($inputs['valueColumn'])) {
            $record->value_column_id = $inputs['valueColumn'];
        }*/
        if (isset($inputs['notificationType'])) {
            $record->notification_type = ! empty($inputs['notificationType']) ? $inputs['notificationType'] : ProjectReportNotification::TYPE_REMINDER;
        }
        if (isset($inputs['publish'])) {
            $record->is_published = (bool) $inputs['publish'];
        }
        if (isset($inputs['templateName'])) {
            $name = trim($inputs['templateName']);
            $record->template_name = ! empty($name) ? $name : trans('projectReportNotification.title');
        }
    }

    public function syncPeriod($recordId, $inputs)
    {
        // Convert periodValue string to an array
        $periodValues = is_array($inputs['periodValue']) ? $inputs['periodValue'] : explode(',', $inputs['periodValue']);;

        // Retrieve existing periods for the given record ID
        $existingPeriods = ProjectReportNotificationPeriod::where('project_report_notification_id', $recordId)->get();

        // Create an array to keep track of existing period combinations
        $existingPeriodsMap = [];
        foreach ($existingPeriods as $item) {
            $key = $item->period_value . ':' . $item->period_type;
            $existingPeriodsMap[$key] = $item->id;
        }

        // Retrieve the single period type from inputs
        $periodType = $inputs['periodType'];

        // Loop through the input period values
        foreach ($periodValues as $periodValue) {
            $key = $periodValue . ':' . $periodType;

            if (! isset($existingPeriodsMap[$key])) {
                // If the period does not exist, create a new one
                ProjectReportNotificationPeriod::create([
                    'project_report_notification_id' => $recordId,
                    'period_value' => $periodValue,
                    'period_type' => $periodType,
                ]);
            } else {
                // If the period exists, remove it from the existing periods map
                unset($existingPeriodsMap[$key]);
            }
        }

        // Delete obsolete periods
        ProjectReportNotificationPeriod::whereIn('id', $existingPeriodsMap)->delete();
    }

    public function saveContent($recordId, $inputs)
    {
        $record = ProjectReportNotificationContent::where('project_report_notification_id', $recordId)->first();
        if (! $record) {
            $record = new ProjectReportNotificationContent();
            $record->project_report_notification_id = $recordId;
        }
        if (isset($inputs['subject'])) {
            $subject = trim($inputs['subject']);
            $record->subject = ! empty($subject) ? $subject : trans('projectReportNotification.title');
        }
        if (isset($inputs['body'])) {
            $record->body = trim($inputs['body']);
        }
        $record->save();
    }

    public function publishRecord($recordId, $publish=true)
    {
        $record = $this->getRecord($recordId);
        $record->is_published = $publish;
        $record->save();

        return true;
    }

    public function deleteRecord($recordId)
    {
        $record = $this->getRecord($recordId);
        $record->delete();

        return true;
    }

    public function getContent($recordId)
    {
        return ProjectReportNotificationContent::where('project_report_notification_id', $recordId)->first();
    }

    public function getColumnTitleWithParents($column)
    {
        $label = '';
        if (!is_null($column->parent_id))
        {
            $parentColumns = $column->parentsList();
            if (count($parentColumns) > 0)
            {
                $label .= implode(' > ', array_map(function($parentColumn) {
                    return $parentColumn->getColumnTitle();
                }, $parentColumns));
            }
        }
        if (!empty($label)) {
            $label .= ' > ';
        }
        $label .= $column->getColumnTitle();
        return $label;
    }

    public function getCategoryColumns($mappingId)
    {
        $selections = array();
        $mapping = ProjectReportTypeMapping::find($mappingId);
        if (! $mapping) {
            return $selections;
        }
        $template = $mapping->projectReportTemplate;
        if (! $template) {
            return $selections;
        }

        $categoryColumns = $template->columns()->where('type', ProjectReportColumn::COLUMN_DATE)->get();

        if (! empty($categoryColumns)) {
            foreach ($categoryColumns as $categoryColumn) {
                $selections[$categoryColumn->id] = $this->getColumnTitleWithParents($categoryColumn);
            }
        }
        return $selections;
    }

    public function getLatestReportDate($project, $mappingId, $categoryColumnId)
    {
        $mapping = ProjectReportTypeMapping::find($mappingId);
        if (! $mapping) {
            return null;
        }
        $latestReport = ProjectReport::latestApprovedProjectReport($project, $mapping);
        if (! $latestReport) {
            return null;
        }

        $categoryColumn = ProjectReportColumn::find($categoryColumnId);
        if (! $categoryColumn) {
            return null;
        }
        $record = ProjectReportColumn::where('reference_id', $categoryColumn->reference_id)
            ->where('project_report_id', $latestReport->id)
            ->whereNotNull('content')
            ->first();

        return $record ? $record->content : null;
    }

    public function getDateSelections($mappingId, $categoryColumnId)
    {
        $categoryColumn = ProjectReportColumn::find($categoryColumnId);
        if (! $categoryColumn) {
            return array();
        }

        $records = ProjectReportColumn::where('reference_id', $categoryColumn->reference_id)
            ->withProjectReportQuery(['mapping_id' => $mappingId])
            ->selectRaw('DISTINCT TO_CHAR(TO_DATE(content, \'YYYY-MM-DD\'), \'YYYY-MM-DD\') AS day, id')
            ->whereNotNull('content')
            ->orderBy('day', 'asc')
            ->get();

        if (count($records) === 0) {
            return array();
        }

        // Use lists method to create an associative array
        $list = $records->lists('day', 'id');
        /*foreach ($list as $id => $day) {
            $list[$id] = \DateTime::createFromFormat('Y-m-d', $day)->format('d-m-Y');
        }*/
        return $list;
    }

    public function getPeriodSelections()
    {
        return [
            ProjectReportNotificationPeriod::PERIOD_DAYS => trans('projectReportNotification.periodDays'),
            ProjectReportNotificationPeriod::PERIOD_WEEKS => trans('projectReportNotification.periodWeeks'),
            ProjectReportNotificationPeriod::PERIOD_MONTHS => trans('projectReportNotification.periodMonths'),
            ProjectReportNotificationPeriod::PERIOD_YEARS => trans('projectReportNotification.periodYears'),
        ];
    }

    public function getPeriodList($templateId)
    {
        $records = ProjectReportNotificationPeriod::where('project_report_notification_id', $templateId)->get();
        $periods = ['values' => [], 'type' => null, 'label' => null];

        if (count($records) === 0) {
            return $periods;
        }
        $periodValues = $records->lists('period_value');
        rsort($periodValues);
        $periods['values'] = $periodValues;

        $periodType = $records->first()->period_type;
        $periods['type'] = $periodType;
        $periods['typeLabel'] = ProjectReportNotificationPeriod::getTypeLabel($periodType);

        $periods['label'] = implode(', ', $periods['values']) . ' ' . $periods['typeLabel'];

        return $periods;
    }

    public function checkNotifyDates($record)
    {
        $project = $record->project;
        if (! $project) {
            return false;
        }
        $mapping = $record->projectReportTypeMapping;
        if (! $mapping) {
            return false;
        }
        $categoryColumn = $record->categoryColumn;
        if (! $categoryColumn) {
            return false;
        }

        $notify = false;

        $valueColumnContent = $this->getLatestReportDate($project, $mapping->id, $categoryColumn->id);
        if (! empty($valueColumnContent)) {
            $periods = $this->getPeriodList($record->id);

            foreach ($periods['values'] as $howMany) {
                $notifyDate = Helpers::getTimeBefore(Carbon::parse($valueColumnContent), $howMany, strtolower($periods['typeLabel']));

                if ($notifyDate->isToday()) {
                    $notify = true;
                    break;
                }
            }
        }

        return $notify;
    }

    public function getNotificationRecipientLogs()
    {
        return ProjectReportNotificationRecipient::withNotificationQuery()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function notificationRecipientLog($recordId, $userId)
    {
        $recipientRecord = new ProjectReportNotificationRecipient();
        $recipientRecord->project_report_notification_id = $recordId;
        $recipientRecord->user_id = $userId;
        $recipientRecord->save();
    }
}