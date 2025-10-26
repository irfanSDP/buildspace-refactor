<?php namespace ProjectReport;

use Carbon\Carbon;
use PCK\Base\Helpers;
use PCK\EmailSettings\EmailSetting;
use PCK\Projects\Project;
use PCK\ProjectReport\ProjectReportNotificationRepository;
use PCK\ProjectReport\ProjectReportTypeMappingRepository;

class ProjectReportNotificationController extends \BaseController
{
    private $projectReportNotificationRepository;
    private $projectReportTypeMappingRepository;

    public function __construct(ProjectReportNotificationRepository $projectReportNotificationRepository, ProjectReportTypeMappingRepository $projectReportTypeMappingRepository)
    {
        $this->projectReportNotificationRepository = $projectReportNotificationRepository;
        $this->projectReportTypeMappingRepository = $projectReportTypeMappingRepository;
    }

    private function getCategoryColumns($mappingId)
    {
        $categoryColumns = $this->projectReportNotificationRepository->getCategoryColumns($mappingId);
        if (count($categoryColumns) === 0) {
            \Flash::error(trans('projectReportNotification.errorNoDateColumns'));
            return false;
        }
        return $categoryColumns;
    }

    public function index(Project $project, $mappingId)
    {
        $this->getCategoryColumns($mappingId);

        $emailSetting = EmailSetting::first();
        if (! $emailSetting)
        {
            $emailSetting = EmailSetting::createDefault();
        }

        $previewContents = array(
            'footerLogo' => array(
                'src' => '',
                'alignment' => '',
            ),
        );

        if (strlen($emailSetting->footer_logo_image) > 0)
        {
            $previewContents['footerLogo']['src'] = asset(EmailSetting::LOGO_FILE_DIRECTORY.DIRECTORY_SEPARATOR.$emailSetting->footer_logo_image).'?v='.time();
            $previewContents['footerLogo']['alignment'] = EmailSetting::getCompanyLogoAlignmentValue($emailSetting->company_logo_alignment_identifier);
        }

        return \View::make('project_report.template.notification.index', [
            'project' => $project,
            'mappingId' => $mappingId,
            'mappingTitle' => $this->projectReportTypeMappingRepository->getTitle($mappingId),
            'previewContents' => $previewContents,
        ]);
    }

    public function getList(Project $project, $mappingId)
    {
        $records = $this->projectReportNotificationRepository->getAllRecords(array('projectId' => $project->id, 'mappingId' => $mappingId));

        $data = [];
        foreach ($records as $record)
        {
            $categoryColumn = $record->categoryColumn;

            $periods = $this->projectReportNotificationRepository->getPeriodList($record->id);
            $notifyDatesData = [];
            $notifyDates = [];

            $valueColumnContent = $this->projectReportNotificationRepository->getLatestReportDate($project, $mappingId, $categoryColumn->id);
            if (! empty($valueColumnContent)) {
                foreach ($periods['values'] as $howMany) {
                    $notifyDate = Helpers::getTimeBefore(Carbon::parse($valueColumnContent), $howMany, strtolower($periods['typeLabel']));
                    $notifyDates[] = $notifyDate->format('d-m-Y');
                    $notifyDatesData[] = [
                            'date' => $notifyDate->format('d-m-Y'),
                            'status' => $notifyDate->isToday() || $notifyDate->isFuture()
                        ];
                }

                $valueColumnContent = \DateTime::createFromFormat('Y-m-d', $valueColumnContent)->format('d-m-Y');
            }

            $isPublishedLabels = [];
            if ($record->is_published) {
                $isPublishedLabels[] = [
                    trans('general.yes'),
                    trans('general.active'),
                    trans('general.activated'),
                    //trans('general.published'),
                ];
            } else {
                $isPublishedLabels[] = [
                    trans('general.no'),
                    //trans('general.inactive'),
                    trans('general.deactivated'),
                    //trans('general.unpublished'),
                ];
            }

            $temp = [
                'id' => $record->id,
                'categoryColumn' => $this->projectReportNotificationRepository->getColumnTitleWithParents($categoryColumn),
                'valueColumnContent' => $valueColumnContent,
                'notificationType' => $record->notification_type,
                'isPublishedLabels' => $isPublishedLabels,
                'isPublished' => $record->is_published,
                'templateName' => $record->template_name,
            ];

            $temp['periods'] = $periods['label'];
            $temp['notifyDates'] = $notifyDates;
            $temp['notifyDatesData'] = $notifyDatesData;

            $temp['route:publish'] = route('projectReport.notification.publish', [$project->id, $mappingId, $record->id]);
            $temp['route:preview'] = route('projectReport.notification.preview', [$project->id, $mappingId, $record->id]);
            $temp['route:edit'] = route('projectReport.notification.edit', [$project->id, $mappingId, $record->id]);
            $temp['route:delete'] = route('projectReport.notification.delete', [$project->id, $mappingId, $record->id]);

            $data[] = $temp;
        }

        return \Response::json($data);
    }

    public function getPartials(Project $project, $mappingId)
    {
        $html = '';
        $templateId = null;
        $request = \Request::instance();

        if ($request->has('recordId')) {
            if (is_numeric($request->input('recordId'))) {
                $templateId = $request->input('recordId');
            }
        }

        if (empty($templateId)) {
            $view = 'create';
            //$record = null;
        } else {
            $view = 'edit';
            //$record = $this->projectReportNotificationRepository->getRecord($templateId);
        }

        if ($request->has('categoryColumnId')) {
            if (is_numeric($request->input('categoryColumnId'))) {
                $value = $this->projectReportNotificationRepository->getLatestReportDate($project, $mappingId, $request->input('categoryColumnId'));

                //$html .= \View::make('project_report.template.notification.partials.'.$view.'.date',
                $html .= \View::make('project_report.template.notification.partials.date',
                    array(
                        'value' => ! empty($value) ? \DateTime::createFromFormat('Y-m-d', $value)->format('d-m-Y') : $value,
                        /*'selections' => array(
                            'value_columns' => $this->projectReportNotificationRepository->getDateSelections($mappingId, $request->input('categoryColumnId'))
                        ),
                        'record' => $record*/
                    )
                )->render();
            }
        }

        return \Response::json($html);
    }

    public function getPreview(Project $project, $mappingId, $templateId)
    {
        $data = ['subject' => '', 'body' => ''];

        $record = $this->projectReportNotificationRepository->getRecord($templateId);
        if (! $record) {
            return \Response::json($data);
        }

        $data['subject'] = $record->content->subject;
        $data['body'] = nl2br($record->content->body);

        return \Response::json($data);
    }

    public function create(Project $project, $mappingId)
    {
        $categoryColumns = $this->getCategoryColumns($mappingId);
        if (! $categoryColumns) {
            return \Redirect::back();
        }

        return \View::make('project_report.template.notification.create', [
            'project' => $project,
            'mappingId' => $mappingId,
            'mappingTitle' => $this->projectReportTypeMappingRepository->getTitle($mappingId),
            'categoryColumns' => $categoryColumns,
            'periodSelections' => $this->projectReportNotificationRepository->getPeriodSelections(),
        ]);
    }

    public function store(Project $project, $mappingId)
    {
        $inputs = \Input::all();
        $errors = null;
        $success = false;

        try {
            $this->projectReportNotificationRepository->createRecord($project->id, $mappingId, $inputs);
            $success = true;
        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success(trans('forms.saved'));
        } else {
            \Log::error($errors);
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('projectReport.notification.index', [$project->id, $mappingId]);
    }

    public function edit(Project $project, $mappingId, $recordId)
    {
        $record = $this->projectReportNotificationRepository->getRecord($recordId);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::back();
        }

        $categoryColumns = $this->getCategoryColumns($mappingId);
        if (! $categoryColumns) {
            return \Redirect::back();
        }

        $periods = $record->periods;
        if (count($periods) > 0) {
            $periodType = $periods->first()->period_type;
        } else {
            $periodType = null;
        }

        return \View::make('project_report.template.notification.edit', [
            'project' => $project,
            'mappingId' => $mappingId,
            'mappingTitle' => $this->projectReportTypeMappingRepository->getTitle($mappingId),
            'categoryColumns' => $categoryColumns,
            'record' => $record,
            'periodValues' => $periods->lists('period_value'),
            'periodType' => $periodType,
            'periodSelections' => $this->projectReportNotificationRepository->getPeriodSelections(),
        ]);
    }

    public function update(Project $project, $mappingId, $recordId)
    {
        $inputs = \Input::all();
        $errors = null;
        $success = false;

        try {
            $this->projectReportNotificationRepository->updateRecord($recordId, $mappingId, $inputs);
            $success = true;
        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success(trans('forms.saved'));
        } else {
            \Log::error($errors);
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('projectReport.notification.index', [$project->id, $mappingId]);
    }

    public function publish(Project $project, $mappingId, $recordId)
    {
        $errors = null;
        $success = false;

        try {
            $record = $this->projectReportNotificationRepository->getRecord($recordId);
            if ($record) {
                $record->is_published = ! $record->is_published;
                $record->save();
                $success = true;
            }
        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        return \Response::json([
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    public function destroy(Project $project, $mappingId, $recordId)
    {
        $errors = null;
        $success = false;

        try {
            $record = $this->projectReportNotificationRepository->getRecord($recordId);
            if ($record) {
                $record->delete();
                $success = true;
            }
        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        return \Response::json([
            'success' => $success,
            'errors' => $errors,
        ]);
    }
}