<?php namespace ProjectReport;

use Carbon\Carbon;
use PCK\Helpers\DBTransaction;
use PCK\Projects\Project;
use PCK\ProjectReport\ProjectReportRepository;
use PCK\ProjectReport\ProjectReportColumnRepository;
use PCK\ProjectReport\ProjectReportTypeMapping;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportColumn;
use PCK\ProjectReport\ProjectReportUserPermission;
use PCK\ObjectField\ObjectField;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\Verifier\Verifier;
use PCK\Notifications\EmailNotifier;
use PCK\ProjectReport\ActionLog;

class ProjectReportsController extends \BaseController
{
    private $repository;
    private $columnRepository;
    private $emailNotifier;

    public function __construct(ProjectReportRepository $repository, ProjectReportColumnRepository $columnRepository, EmailNotifier $emailNotifier)
    {
        $this->repository       = $repository;
        $this->columnRepository = $columnRepository;
        $this->emailNotifier    = $emailNotifier;
    }

    public function index(Project $project)
    {
        $permissionType = 'submit';

        $inputs = \Input::all();
        if (! empty($inputs['permission_type']))
        {
            $permissionType = $inputs['permission_type'];
        }

        return \View::make('project_report.index', [
            'project' => $project,
            'permissionType' => $permissionType,
        ]);
    }

    // List of project report types
    public function list(Project $project)
    {
        $permissionType = 'submit';

        $inputs = \Input::all();
        if (! empty($inputs['permission_type']))
        {
            $permissionType = $inputs['permission_type'];
        }

        switch ($permissionType)
        {
            case 'reminder':
                $data = $this->repository->getProjectReportTypesList($project, [
                    ProjectReportUserPermission::IDENTIFIER_EDIT_REMINDER,
                ]);
                break;

            default:
                $data = $this->repository->getProjectReportTypesList($project, [
                    ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT,
                    ProjectReportUserPermission::IDENTIFIER_VERIFY_REPORT
                ]);
        }

        return \Response::json($data);
    }

    // Project report form
    public function show(Project $project, $mappingId)
    {
        $user                    = \Confide::user();
        $mapping                 = ProjectReportTypeMapping::find($mappingId);

        $request = \Request::instance();

        if (! $request->has('prid')) {
            $latestProjectReport = ProjectReport::getLatestProjectReport($project, $mapping);

            $canCreateNewRevision    = (is_null($latestProjectReport) || ($latestProjectReport && $latestProjectReport->isCompleted())) && ProjectReportUserPermission::hasPermission($project, $user, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);
            $canEditReport           = $latestProjectReport && $latestProjectReport->isDraft() && ProjectReportUserPermission::hasPermission($project, $user, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);
        } else {
            $projectReportId = $request->input('prid');
            $latestProjectReport = ProjectReport::where('project_id', $project->id)
                ->where('project_report_type_mapping_id', $mapping->id)
                ->where('id', $projectReportId)
                ->first();

            $canCreateNewRevision = false;
            $canEditReport = false;
        }

        $verifiers               = ProjectReportUserPermission::getLisOfUsersByIdentifier($project, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_VERIFY_REPORT);
        $isCurrentVerifier       = $latestProjectReport ? Verifier::isCurrentVerifier($user, $latestProjectReport) : false;
        $assignedVerifierRecords = $latestProjectReport ? Verifier::getAssignedVerifierRecords($latestProjectReport, true) : [];

        $projectProgressOptions = $this->columnRepository->getProjectProgressSelections();

        return \View::make('project_report.show', [
            'project'                 => $project,
            'mapping'                 => $mapping,
            'projectReportType'       => $mapping->projectReportType,
            'latestProjectReport'     => $latestProjectReport,
            'saveColumnContentRoute'  => is_null($latestProjectReport) ? null : route('projectReport.columns.contents.save', [$project->id, $latestProjectReport->id]),
            'canCreateNewRevision'    => $canCreateNewRevision,
            'canEditReport'           => $canEditReport,
            'verifiers'               => $verifiers,
            'isCurrentVerifier'       => $isCurrentVerifier,
            'assignedVerifierRecords' => $assignedVerifierRecords,
            'projectProgressOptions'  => $projectProgressOptions
        ]);
    }

    // List of project reports
    public function showAll(Project $project, $mappingId)
    {
        $user                    = \Confide::user();
        $mapping                 = ProjectReportTypeMapping::find($mappingId);
        $latestProjectReport     = ProjectReport::getLatestProjectReport($project, $mapping);
        $canCreateNewRevision    = (is_null($latestProjectReport) || ($latestProjectReport && $latestProjectReport->isCompleted())) && ProjectReportUserPermission::hasPermission($project, $user, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);
        $canEditReport           = $latestProjectReport && $latestProjectReport->isDraft() && ProjectReportUserPermission::hasPermission($project, $user, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);

        return \View::make('project_report.show_all', [
            'project'               => $project,
            'mapping'               => $mapping,
            'templateId'            => $mapping->project_report_id,
            'canCreateNewRevision'  => $canCreateNewRevision,
            'canEditReport'         => $canEditReport,
        ]);
    }

    public function getColumnDefinitions(Project $project, $mappingId, $projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $columnDefinition = $this->columnRepository->getColumnDefinitions($projectReport);

        return \Response::json($columnDefinition);
    }

    public function getColumnContents(Project $project, $mappingId, $projectReportId, $projectType)
    {
        $mapping = ProjectReportTypeMapping::find($mappingId);

        if ($mapping->latest_rev) {
            $latestProjectReport = ProjectReport::getLatestProjectReport($project, $mapping);
            $projectReportIds = array($latestProjectReport->id);
        } else {
            $projectReports = $this->repository->getReportsByMapping($project, $mapping, $projectType);
            $projectReportIds = array_column($projectReports, 'id');
        }
        $columnContents = $this->repository->getColumnContentsByTemplate($mapping, $projectReportIds, $projectType, true);

        return \Response::json($columnContents);
    }

    public function listPreviousRevisions(Project $project, $mappingId)
    {
        $mapping = ProjectReportTypeMapping::find($mappingId);
        $data    = $this->repository->listPreviousRevisions($project, $mapping);

        return \Response::json($data);
    }

    public function previousRevisionShow(Project $project, $projectReportId)
    {
        $projectReport           = ProjectReport::find($projectReportId);
        $assignedVerifierRecords = Verifier::getAssignedVerifierRecords($projectReport, true);

        return \View::make('project_report.previous_revision_show', [
            'project'                 => $project,
            'projectReport'           => $projectReport,
            'projectReportType'       => $projectReport->projectReportTypeMapping->projectReportType,
            'assignedVerifierRecords' => $assignedVerifierRecords,
        ]);
    }

    public function createNewRevision(Project $project, $mappingId)
    {
        $user                    = \Confide::user();
        $mapping                 = ProjectReportTypeMapping::find($mappingId);
        $latestProjectReport     = ProjectReport::getLatestProjectReport($project, $mapping);

        $canCreateNewRevision = (is_null($latestProjectReport) || ($latestProjectReport && $latestProjectReport->isCompleted())) && ProjectReportUserPermission::hasPermission($project, $user, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);

        if ($canCreateNewRevision) {
            $success = false;

            try {
                $transaction = new DBTransaction();
                $transaction->begin();

                $this->repository->cloneReportFromTemplateMapping($project, $mapping);

                $mapping->projectReportType->lock();

                $mapping->lock();   // Lock mapping template

                $transaction->commit();

                $projectReport = ProjectReport::getLatestProjectReport($project, $mapping);

                $this->emailNotifier->sendProjectReportCreatedNotifications($projectReport);

                ActionLog::logAction($projectReport, ActionLog::CREATED_NEW_REVISION);

                $success = true;
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                $transaction->rollback();
            } finally {
                if ($success) {
                    if ($mapping->latest_rev) {
                        \Flash::success(trans('projectReport.newRevisionCreated'));
                    } else {
                        \Flash::success(trans('projectReport.newRecordCreated'));
                    }
                } else {
                    \Flash::error(trans('general.anErrorHasOccured'));
                }
            }
        } else {
            $canEditReport = $latestProjectReport && $latestProjectReport->isDraft() && ProjectReportUserPermission::hasPermission($project, $user, $mapping->projectReportType->id, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);
            if (! $canEditReport) {
                \Flash::error(trans('projectReport.operationNotAllowed'));
                return \Redirect::back();
            }
        }

        return \Redirect::route('projectReport.show', [$project->id, $mappingId]);
    }

    public function getProjectProjectReportColumns(Project $project, $projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $columns       = $this->columnRepository->getColumns($projectReport);

        return \Response::json($columns);
    }

    public function saveColumnContents(Project $project, $projectReportId)
    {
        $inputs              = \Input::all();
        $inputs['verifiers'] = array_key_exists('verifiers', $inputs) ? $inputs['verifiers'] : [];

        if(array_key_exists(ProjectReportColumn::COLUMN_NAME_PREFIX, $inputs))
        {
            $trimmedContents = array_map(function($content) {
                return (trim($content) == '') ? null : trim($content);
            }, $inputs[ProjectReportColumn::COLUMN_NAME_PREFIX]);

            $this->columnRepository->saveColumnContents($trimmedContents);
        }

        $projectReport = ProjectReport::find($projectReportId);

        if(array_key_exists('send_to_verify', $inputs))
        {
            $this->repository->submitForApproval($projectReport, $inputs);

            ActionLog::logAction($projectReport, ActionLog::SUBMITTED_FOR_APPROVAL);
        }
        else
        {
            $this->emailNotifier->sendProjectReportSavedNotifications($projectReport);

            ActionLog::logAction($projectReport, ActionLog::REPORT_SAVED);
        }

        \Flash::success(trans('forms.saved'));

        return \Redirect::back();
    }

    public function delete(Project $project, $projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);

        if($projectReport)
        {
            $projectReport->delete();
        }

        \Flash::success(trans('forms.deleted'));

        return \Redirect::route('projectReport.showAll', [$project->id, $projectReport->project_report_type_mapping_id]);
    }

    public function attachmentsUpdate(Project $project, $projectReportId, $field)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $inputs        = \Input::all();
        $object        = ObjectField::findOrCreateNew($projectReport, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

		return array(
			'success' => true,
		);
    }

    public function getAttachmentCount(Project $project, $projectReportId, $field)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $object        = ObjectField::findOrCreateNew($projectReport, $field);

        return \Response::json([
            'field'           => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function getAttachmentsList(Project $project, $projectReportId, $field)
	{
        $projectReport = ProjectReport::find($projectReportId);
        $object        = ObjectField::findOrCreateNew($projectReport, $field);
		$uploadedFiles = $this->getAttachmentDetails($object);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
	}

    public function getActionLogs(Project $project, $projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $actionLogs    = $projectReport->actionLogs()->orderBy('id', 'DESC')->get();

        $data = [];

        foreach($actionLogs as $log)
        {
            $data[] = [
                'user'     => $log->createdBy->name,
                'dateTime' => Carbon::parse($log->created_at)->format(\Config::get('dates.full_format')),
                'action'   => $log->getActionDescription(),
            ];
        }

        return \Response::json($data);
    }
}