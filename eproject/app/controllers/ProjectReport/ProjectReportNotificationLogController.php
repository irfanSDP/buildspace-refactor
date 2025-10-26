<?php namespace ProjectReport;

use Carbon\Carbon;
use PCK\Base\Helpers;
use PCK\ProjectReport\ProjectReportNotificationRepository;

class ProjectReportNotificationLogController extends \BaseController
{
    private $projectReportNotificationRepository;

    public function __construct(ProjectReportNotificationRepository $projectReportNotificationRepository)
    {
        $this->projectReportNotificationRepository = $projectReportNotificationRepository;
    }

    public function index()
    {
        return \View::make('project_report.log.notification.index', [
            //...
        ]);
    }

    public function getList()
    {
        $records = $this->projectReportNotificationRepository->getNotificationRecipientLogs();

        $data = [];
        foreach ($records as $record)
        {
            $notification = $record->notification;
            $user = $record->user;

            $temp = [
                'id' => $record->id,
                'templateId' => $notification->id,
                'templateName' => $notification->template_name,
                'recipientUsername' => $user->username,
                'recipientName' => $user->name,
                'sentDateTime' => Carbon::parse($record->created_at)->format('d-m-Y H:i:s'),
            ];

            $data[] = $temp;
        }

        return \Response::json($data);
    }
}