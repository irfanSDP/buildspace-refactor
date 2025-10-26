<?php namespace ProjectReport;

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportRepository;
use PCK\ProjectReport\ProjectReportColumnRepository;
use PCK\ProjectReport\ProjectReportTypeMapping;
use PCK\ProjectReport\ProjectReportChartRepository;
use PCK\ProjectReport\ProjectReportChartPlotRepository;
use PCK\Forms\ProjectReport\ProjectReportTemplateForm;
use PCK\Statuses\FormStatus;

class ProjectReportTemplatesController extends \Controller
{
    private $repository;
    private $form;
    private $columnRepository;
    private $chartRepository;
    private $plotRepository;

    public function __construct(
        ProjectReportRepository $repository,
        ProjectReportColumnRepository $columnRepository,
        ProjectReportTemplateForm $form,
        ProjectReportChartRepository $chartRepository,
        ProjectReportChartPlotRepository $plotRepository
    ) {
        $this->repository       = $repository;
        $this->columnRepository = $columnRepository;
        $this->form             = $form;
        $this->chartRepository  = $chartRepository;
        $this->plotRepository   = $plotRepository;
    }

    public function index()
    {
        return \View::make('project_report.template.index');
    }

    public function list()
    {
        $data = [];

        foreach($this->repository->listTemplates() as $record)
        {
            $temp = [
                'id'          => $record->id,
                'title'       => $record->title,
                'revision'    => $record->revision,
                'status'      => $record->status,
                'status_text' => ProjectReport::getStatusText($record->status),
            ];

            $temp['route:update'] = route('projectReport.template.update', [$record->id]);  // Update template's title

            $temp['route:show'] = route('projectReport.template.show', [$record->id]);
            $temp['route:clone'] = route('projectReport.template.clone', [$record->id]);

            if ($record->status === FormStatus::STATUS_DRAFT)
            {
                $temp['route:lockRevision'] = route('projectReport.template.lockRevision', [$record->id]);
            }

            if (in_array($record->status, array(FormStatus::STATUS_DRAFT)) || ! ProjectReport::hasProjectReports($record->id))
            {
                $temp['route:delete'] = route('projectReport.template.delete', [$record->id]);
            }

            /*if($record->status === FormStatus::STATUS_COMPLETED)
            {
                //$temp['route:newRevision'] = route('projectReport.template.newRevision', [$record->id]);
            }*/

            array_push($data, $temp);
        }

        return \Response::json($data);
    }

    public function show($templateId)
    {
        $template         = ProjectReport::find($templateId);
        if (! $template) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::back();
        }

        $canEditTemplate  = $template->isDraft();
        $columnSelections = $this->columnRepository->getColumnSelections();

        return \View::make('project_report.template.show', [
            'template'         => $template,
            'canEditTemplate'  => $canEditTemplate,
            'columnSelections' => $columnSelections,
        ]);
    }

    public function store()
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->repository->createNewTemplate($inputs['title']);

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $errors = $e->getErrors();
        }
        
        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update($templateId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);

            $template = ProjectReport::find($templateId);
            $template->title = trim($inputs['title']);
            $template->save();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $errors = $e->getErrors();
        }
        
        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }


    public function destroy($templateId)
    {
        $errors  = null;
        $success = false;

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();

            $record = ProjectReport::find($templateId);

            $mappings = ProjectReportTypeMapping::where('project_report_id', $record->id)->get();
            if ($mappings->count() > 0) {
                foreach ($mappings as $mapping) {
                    $reportType = $mapping->projectReportType;
                    if ($reportType) {
                        if ($reportType->is_locked) {
                            $reportType->is_locked = false; // Unlock report type
                            $reportType->save();
                        }

                        $this->chartRepository->getRecordsByMappingId($mapping->id)->each(function($chart) {
                            $this->plotRepository->getAllRecords($chart->id)->each(function($plot) {
                                $plot->delete();
                            });
                            $chart->delete();
                        });
                    }

                    $mapping->delete(); // Remove template mapping
                }
            }

            $record->delete();  // Delete template
    
            $transaction->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return \Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function lockRevision($templateId)
    {
        $errors  = null;
        $success = false;

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();
    
            $template         = ProjectReport::find($templateId);
            $template->status = FormStatus::STATUS_COMPLETED;
            $template->save();

            ProjectReportTypeMapping::updateMappedTemplateToLatestRevision($template);

            $transaction->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return \Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function cloneTemplate($templateId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();

            $this->form->validate($inputs);

            $template = ProjectReport::find($templateId);

            $this->repository->cloneNewForm($template, trim($inputs['title']));

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
            \Log::info($e);
        }

        return \Response::json([
            'success'  => $success,
            'message'   => $errors,
        ]);
    }

    public function createNewRevision($templateId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);

            $template = ProjectReport::find($templateId);

            $this->repository->createNewRevision($template, trim($inputs['title']));

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }

        return \Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }
}