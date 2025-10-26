<?php namespace ProjectReport;

use PCK\Exceptions\ValidationException;
use PCK\Forms\ProjectReport\ProjectReportChartTemplateForm;
use PCK\Helpers\DBTransaction;
use PCK\Projects\Project;
use PCK\ProjectReport\ProjectReportChartRepository;
use PCK\ProjectReport\ProjectReportChartPlotRepository;
use PCK\ProjectReport\ProjectReportTypeMapping;

class ProjectReportChartTemplateController extends \Controller
{
    private $chartRepository;
    private $plotRepository;
    private $form;

    public function __construct(
        ProjectReportChartRepository $chartRepository,
        ProjectReportChartPlotRepository $plotRepository,
        ProjectReportChartTemplateForm $form
    ) {
        $this->chartRepository = $chartRepository;
        $this->plotRepository = $plotRepository;
        $this->form = $form;
    }

    public function index()
    {
        return \View::make('project_report.template.chart.index');
    }

    public function getList()
    {
        $data = array();

        $records = $this->chartRepository->getAllRecords();

        foreach($records as $record)
        {
            $totalPlots = $record->chartPlots->count();

            $row = array(
                'id' => $record->id,
                'title' => $record->title,
                'chart_type' => $this->chartRepository->getLabel($record->chart_type),
                'chart_icon' => $this->chartRepository->getIcon($record->chart_type),
                'data_grouping' => $record->getPlotGroup(true),
                'report_type' => $record->projectReportTypeMapping->projectReportType->title,
                //'is_locked' => (bool)$record->is_locked,
                'is_published' => (bool)$record->is_published,
                'order' => $record->order,
                'total_plots' => $totalPlots
            );

            //if (! $record->is_locked) {
                $row['route:edit'] = route('projectReport.chart.template.edit', array($record->id));
            //}

            $row['route:plots'] = route('projectReport.chart.plot.template.index', array($record->id));

            /*if (! $record->is_locked && $totalPlots > 0) {
                $row['route:lock'] = route('projectReport.chart.template.lock', array($record->id));
            }*/
            if (/*$record->is_locked &&*/ $totalPlots > 0) {
                $row['route:publish'] = route('projectReport.chart.template.publish', array($record->id));
            }

            $row['route:delete'] = route('projectReport.chart.template.delete', array($record->id));

            $data[] = $row;
        }

        return \Response::json($data);
    }

    public function create()
    {
        $chartTypes = $this->chartRepository->getSelections();

        $reportTypeMappings = ProjectReportTypeMapping::with(array('projectReportType'))
            ->whereHas('projectReportType', function($query) {
                $query->where('is_locked', true);
            })
            ->where('project_type', Project::TYPE_MAIN_PROJECT)
            ->where('is_locked', true)
            ->orderBy('id', 'asc')
            ->select('id', 'project_report_type_id')
            ->get();

        return \View::make('project_report.template.chart.create', compact('chartTypes', 'reportTypeMappings'));
    }

    public function store()
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try {
            $this->form->validate($inputs);

            $mapping = ProjectReportTypeMapping::where('id', $inputs['reportTypeMapping'])->first();
            if ($mapping) {
                if ($this->chartRepository->createRecord($mapping->id, $inputs)) {
                    $success = true;
                }
            }
        } catch (ValidationException $e) {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success(trans('projectReportChart.templateSaved'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('projectReport.chart.template.index');
    }

    public function edit($id)
    {
        $record = $this->chartRepository->getRecord($id);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        $chartTypes = $this->chartRepository->getSelections();

        $reportTypeMappings = ProjectReportTypeMapping::with(array('projectReportType'))
            ->where('project_type', Project::TYPE_MAIN_PROJECT)
            ->where('is_locked', true)
            ->orderBy('id', 'asc')
            ->select('id', 'project_report_type_id')
            ->get();

        return \View::make('project_report.template.chart.edit', compact('chartTypes', 'reportTypeMappings', 'record'));
    }

    public function update($id)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);

            $record = $this->chartRepository->getRecord($id);
            if (! $record) {
                \Flash::error(trans('errors.recordNotFound'));
                return \Redirect::route('projectReport.chart.template.index');
            }

            $mapping = ProjectReportTypeMapping::where('id', $inputs['reportTypeMapping'])->first();
            if ($mapping) { // Mapping exists
                if (! $record->is_locked) { // Locked -> Unlocked
                    $this->chartRepository->lockRecord($record->id, false);
                }
                if ($this->chartRepository->updateRecord($record->id, $mapping->id, $inputs)) { // Update chart
                    if ($record->chart_type != trim($inputs['chartType'])) {    // Chart type has changed
                        $this->plotRepository->deleteAllRecords($record->id);   // Delete all plots

                        if ($record->is_published) {    // Published -> Unpublished
                            $this->chartRepository->publishRecord($record->id, false);
                        }
                    }
                    $success = true;
                }
            }
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success(trans('projectReportChart.templateUpdated'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('projectReport.chart.template.index');
    }

    public function destroy($id)
    {
        $errors  = null;
        $success = false;

        $record = $this->chartRepository->getRecord($id);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();

            $success = $this->chartRepository->deleteRecord($id);   // Delete chart
            if ($success) { // Success deleting chart
                $this->plotRepository->deleteAllRecords($id);    // Delete all plots
            }

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return \Response::json(array(
            'success'  => $success,
            'errors'   => $errors,
        ));
    }

    public function lock($id)
    {
        $errors  = null;
        $success = false;

        $record = $this->chartRepository->getRecord($id);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();

            if ($record->chartPlots->count() > 0) {
                $success = $this->chartRepository->lockRecord($record->id);
            }

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success(trans('projectReportChart.templateLocked'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Response::json(array(
            'success'  => $success,
            'errors'   => $errors,
        ));
    }

    public function publish($id)
    {
        $errors  = null;
        $success = false;

        $record = $this->chartRepository->getRecord($id);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        if ($record->is_published) {    // Published -> Unpublished
            $publish = false;
            $message = trans('projectReportChart.unpublished');
        } else {    // Unpublished -> Published
            $publish = true;
            $message = trans('projectReportChart.published');
        }
        if ($publish && $record->chartPlots->count() === 0) {   // No plots -> Publishing is not allowed
            \Flash::error(trans('projectReportChart.noPlotsPublishNotAllowed'));
            return \Response::json(array(
                'success'  => false,
                'errors'   => null,
            ));
        }

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();

            $success = $this->chartRepository->publishRecord($record->id, $publish);

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success($message);
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Response::json(array(
            'success'  => $success,
            'errors'   => $errors,
        ));
    }

    public function rearrange()
    {
        $errors  = null;
        $success = false;
        $inputs  = \Input::all();

        $transaction = new DBTransaction();

        try
        {
            if (isset($inputs['rows'])) {
                foreach ($inputs['rows'] as $row) {
                    $id = $row['id'];
                    $order = $row['order'];

                    $record = $this->chartRepository->getRecord($id);
                    if ($record) {
                        $this->chartRepository->updateOrder($record->id, $order);
                    }
                }
            }

            $transaction->commit();
            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        if ($success) {
            \Flash::success(trans('projectReportChart.orderUpdated'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Response::json(array(
            'success'  => $success,
            'errors'   => $errors,
        ));
    }
}