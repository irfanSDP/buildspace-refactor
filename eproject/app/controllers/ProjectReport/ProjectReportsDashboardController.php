<?php namespace ProjectReport;

use PCK\Projects\Project;
use PCK\ProjectReport\ProjectReportDashboardRepository;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportTypeMapping;
use PCK\ProjectReport\ProjectReportColumnRepository;
use PCK\Reports\ProjectReportDashboardExcelGenerator;
use PCK\ObjectField\ObjectField;
use PCK\Base\Helpers;

class ProjectReportsDashboardController extends \BaseController
{
    private $repository;
    private $columnRepository;

    public function __construct(ProjectReportDashboardRepository $repository, ProjectReportColumnRepository $columnRepository)
    {
        $this->repository       = $repository;
        $this->columnRepository = $columnRepository;
    }

    public function index()
    {
        return \View::make('project_report.dashboard.index');
    }

    public function listProjectReportTypes()
    {
        $reportTypes = $this->repository->getListOfReportTypes();

        $data = array();

        foreach($reportTypes as $record)
        {
            $mapping = ProjectReportTypeMapping::find($record['mapping_id']);
            if (! $mapping) continue;
            if (count($this->repository->getLatestReportsByMapping($mapping, Project::TYPE_MAIN_PROJECT)) === 0) continue;

            $data[] = array(
                'id'         => $record['id'],
                'title'      => $record['title'],
                'route:show' => route('projectReport.dashboard.projectReport.show', $record['mapping_id']),
            );
        }

        return \Response::json($data);
    }

    public function show($mappingId)
    {
        $mapping              = ProjectReportTypeMapping::find($mappingId);
        $latestProjectReports = $this->repository->getLatestReportsByMapping($mapping, Project::TYPE_MAIN_PROJECT);

        if (empty($latestProjectReports)) {
            \Flash::error(trans('errors.noRecordsFound'));
            return \Redirect::back();
        }

        $templates            = $this->repository->getProjectReportsGroupedByTemplate(array_column($latestProjectReports, 'id'));
        $firstTemplateRecord  = array_slice($templates, 0, 1, false);
        $firstTemplateId      = $firstTemplateRecord[0]['template_id'];

        return \View::make('project_report.dashboard.show', [
            'mapping'         => $mapping,
            'templates'       => $templates,
            'firstTemplateId' => $firstTemplateId,
        ]);
    }

    public function subPackageShow($projectReportId)
    {
        $projectReport        = ProjectReport::find($projectReportId);
        $subPackageMapping    = $projectReport->projectReportTypeMapping->projectReportType->mappings()->where('project_type', Project::TYPE_SUB_PACKAGE)->first();
        $subPackages          = Project::where('parent_project_id', $projectReport->project->id)->get();
        $latestProjectReports = $this->repository->getLatestSubpackageReportsByMapping($subPackageMapping, Project::TYPE_SUB_PACKAGE, $subPackages->lists('id'));
        $templates            = $this->repository->getProjectReportsGroupedByTemplate(array_column($latestProjectReports, 'id'));
        $firstTemplateRecord  = array_slice($templates, 0, 1, false);
        $firstTemplateId      = $firstTemplateRecord[0]['template_id'];

        return \View::make('project_report.dashboard.subpackage_show', [
            'projectReport'     => $projectReport,
            'mapping'           => $projectReport->projectReportTypeMapping,
            'templates'         => $templates,
            'subPackageMapping' => $subPackageMapping,
            'firstTemplateId'   => $firstTemplateId,
        ]);
    }

    public function getColumnDefinitions($mappingId, $templateId)
    {
        $template         = ProjectReport::find($templateId);
        $columnDefinition = $this->columnRepository->getColumnDefinitions($template);

        return \Response::json($columnDefinition);
    }

    public function getColumnContents($mappingId, $templateId, $projectType)
    {
        $mapping = ProjectReportTypeMapping::find($mappingId);

        if ($mapping->latest_rev) {
            $latestProjectReports = $this->repository->getLatestReportsByMapping($mapping, $projectType);
            $projectReportsByTemplate = $this->repository->getProjectReportsGroupedByTemplate(array_column($latestProjectReports, 'id'));
            $projectReportIds = $projectReportsByTemplate[$templateId]['project_report_ids'];
        } else {
            $projectReports = $this->repository->getReportsByMapping($mapping, $projectType);
            $projectReportIds = array_column($projectReports, 'id');
        }
        $columnContents = $this->repository->getColumnContentsByTemplate($mapping, $projectReportIds, $projectType, true);

        return \Response::json($columnContents);
    }

    public function exportExcel($mappingId, $templateId, $projectType)
    {
        $template                 = ProjectReport::find($templateId);
        $mapping                  = ProjectReportTypeMapping::find($mappingId);
        $latestProjectReports     = $this->repository->getLatestReportsByMapping($mapping, $projectType);
        $projectReportsByTemplate = $this->repository->getProjectReportsGroupedByTemplate(array_column($latestProjectReports, 'id'));
        $projectReportIds         = $projectReportsByTemplate[$template->id]['project_report_ids'];
        $columnContents           = $this->repository->getColumnContentsByTemplate($mapping, $projectReportIds, $projectType, false);

        $title = ($projectType == Project::TYPE_SUB_PACKAGE) ? $template->title . ' (' . trans('projects.subPackage') . ')' : $template->title;

        $reportGenerator = new ProjectReportDashboardExcelGenerator();
        $reportGenerator->setSpreadsheetTitle($title);
        $reportGenerator->setTemplate($template);
        $reportGenerator->setRowRecords($columnContents);

        return $reportGenerator->generate();
    }

    public function updateRemarks($projectReportId)
    {
        $success = false;
        $errors  = null;
        $inputs  = \Input::all();

        try
        {
            $projectReport = ProjectReport::find($projectReportId);
            $projectReport->remarks = trim($inputs['remarks']);
            $projectReport->save();

            $success = true;
        }
        catch(\Exception $e)
        {
            $error = $e->getMessage();

            \Log::error("Unable to update remarks [Project Report ID:{$projectReport->id}] -> {$error}");
            \Log::error($e->getTraceAsString());
        }

        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function listAllReportsInLine($projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $reportsInLine = $projectReport->getAllReportsInLine('DESC');

        $data = [];

        foreach($reportsInLine as $report)
        {
            $objectField = ObjectField::findRecord($report, ObjectField::PROJECT_REPORT);

            $data[] = [
                'id'                             => $report->id,
                'title'                          => $report->title,
                'revision'                       => $report->isOriginalRevision() ? trans('projectReport.original') : $report->revision,
                'attachmentCount'                => is_null($objectField) ? 0 : $objectField->attachments->count(),
                'route:attachments'              => route('projectReport.dashboard.attachments.get', [$report->id, ObjectField::PROJECT_REPORT]),
                'route:downloadAttachmentsAsZip' => route('projectReport.dashboard.attachments.downloadAsZip', [$report->id, ObjectField::PROJECT_REPORT]),
            ];
        }

        return \Response::json($data);
    }

    public function getAttachmentsList($projectReportId, $field)
	{
        $projectReport = ProjectReport::find($projectReportId);
        $object        = ObjectField::findOrCreateNew($projectReport, $field);
		$uploadedFiles = $this->getAttachmentDetails($object);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']   = $file->generateThumbnailURL();
			$file['size']	  = Helpers::formatBytes($file->size);
            $file['uploader'] = $file->createdBy->name;

			$data[] = $file;
		}

		return $data;
	}

    public function downloadAttachmentsAsZip($projectReportId, $field)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $object        = ObjectField::findOrCreateNew($projectReport, $field);
		$uploadedFiles = $this->getAttachmentDetails($object);

        $filesToZip = [];

        foreach($uploadedFiles as $uploadedFile)
        {
            \PCK\Helpers\Files::copy($uploadedFile->getFullFilePath(), $downloadPath = \PCK\Helpers\Files::getTmpFileUri());

            $filesToZip[$uploadedFile->filename] = $downloadPath;
        }

        if( empty($filesToZip) )
        {
            \Flash::error(trans('files.noFilesToDownload'));

            return \Redirect::back();
        }

        $pathToZipFile = \PCK\Helpers\Zip::zip($filesToZip);

        foreach($filesToZip as $filepath)
        {
            \PCK\Helpers\Files::deleteFile($filepath);
        }

        $revisionText = $projectReport->isOriginalRevision() ? trans('projectReport.original') : trans('projectReport.revision') . '-' . $projectReport->revision;

        $fileName = "{$projectReport->title} ({$revisionText})";

        $zipName = "{$fileName}.".\PCK\Helpers\Files::EXTENSION_ZIP;

        return \PCK\Helpers\Files::download(
            $pathToZipFile,
            $zipName,
            array(
                'Content-Type: application/zip',
            )
        );
    }
}