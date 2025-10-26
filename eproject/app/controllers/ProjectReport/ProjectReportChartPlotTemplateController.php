<?php namespace ProjectReport;

use PCK\Exceptions\ValidationException;
use PCK\Forms\ProjectReport\ProjectReportChartPlotTemplateForm;
use PCK\Helpers\DBTransaction;
use PCK\ProjectReport\ProjectReportChartPlotRepository;
use PCK\ProjectReport\ProjectReportChartRepository;

class ProjectReportChartPlotTemplateController extends \Controller
{
    private $chartRepository;
    private $plotRepository;
    private $form;

    public function __construct(
        ProjectReportChartRepository $chartRepository,
        ProjectReportChartPlotRepository $plotRepository,
        ProjectReportChartPlotTemplateForm $form
    ) {
        $this->chartRepository = $chartRepository;
        $this->plotRepository = $plotRepository;
        $this->form = $form;
    }

    public function index($chartId)
    {
        $chart = $this->chartRepository->getRecord($chartId);
        if (! $chart) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        return \View::make('project_report.template.chart.plot.index', compact('chart'));
    }

    public function getList($chartId)
    {
        $data = array();

        $records = $this->plotRepository->getAllRecords($chartId);

        foreach($records as $record)
        {
            $row = array(
                'id' => $record->id,
                'plot_type' => $this->plotRepository->getTypeLabel($record->plot_type),
                'data_grouping' => $this->plotRepository->getGroupLabel($record->data_grouping),
                'category_column' => $record->categoryColumn->getColumnTitle(),
                'value_column' => $record->valueColumn->getColumnTitle(),
                'is_accumulated' => ($record->is_accumulated) ? trans('general.yes') : trans('general.no')
            );

            //if (! $record->projectReportChart->is_locked) {
                $row['route:edit'] = route('projectReport.chart.plot.template.edit', array($chartId, $record->id));
                $row['route:delete'] = route('projectReport.chart.plot.template.delete', array($chartId, $record->id));
            //}

            $data[] = $row;
        }

        return \Response::json($data);
    }

    public function create($chartId)
    {
        $chart = $this->chartRepository->getRecord($chartId);
        if (! $chart) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        $selections = $this->plotRepository->getSelections($chart);

        return \View::make('project_report.template.chart.plot.create', compact('chart', 'selections'));
    }

    public function getPartials($chartId) {
        $html = '';
        $plotId = null;
        $request = \Request::instance();
        if ($request->has('plotId')) {
            if (is_numeric($request->input('plotId'))) {
                $plotId = $request->input('plotId');
            }
        }
        if (empty($plotId)) {
            $view = 'create';
            $record = null;
        } else {
            $view = 'edit';
            $record = $this->plotRepository->getRecord($plotId);
        }

        if ($request->has('categoryColumnId')) {
            if (is_numeric($request->input('categoryColumnId'))) {
                $dataGrouping = $this->plotRepository->getGroupingSelections($request->input('categoryColumnId'));

                $html .= \View::make('project_report.template.chart.plot.partials.'.$view.'.grouping',
                    array(
                        'display' => count($dataGrouping) > 1,
                        'selections' => array(
                            'data_grouping' => $this->plotRepository->getGroupingSelections($request->input('categoryColumnId'))
                        ),
                        'record' => $record
                    )
                )->render();

                $html .= \View::make('project_report.template.chart.plot.partials.'.$view.'.value',
                    array(
                        'selections' => array(
                            'value_columns' => $this->plotRepository->getValueSelections($chartId, $request->input('categoryColumnId'))
                        ),
                        'record' => $record
                    )
                )->render();
            }
        }
        if ($request->has('valueColumnId')) {
            if (is_numeric($request->input('valueColumnId'))) {
                $html .= \View::make('project_report.template.chart.plot.partials.'.$view.'.accumulative',
                    array(
                        'display' => $this->plotRepository->getAccumulativeOption($request->input('valueColumnId')),
                        'record' => $record
                    )
                )->render();
            }
        }
        return \Response::json($html);
    }

    public function store($chartId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);

            $chart = $this->chartRepository->getRecord($chartId);
            if (! $chart) {
                \Flash::error(trans('errors.recordNotFound'));
                return \Redirect::route('projectReport.chart.template.index');
            }

            $plotId = $this->plotRepository->createRecord($chart->id, $inputs);
            if ($plotId) {
                $this->plotRepository->syncRecords($plotId);
                $success = true;
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
            \Flash::success(trans('projectReportChart.templateSaved'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('projectReport.chart.plot.template.index', array($chartId));
    }

    public function edit($chartId, $plotId)
    {
        $chart = $this->chartRepository->getRecord($chartId);
        if (! $chart) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::route('projectReport.chart.template.index');
        }

        $selections = $this->plotRepository->getSelections($chart);

        $record = $this->plotRepository->getRecord($plotId);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::back();
        }

        return \View::make('project_report.template.chart.plot.edit', compact('chart', 'selections', 'record'));
    }

    public function update($chartId, $plotId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);

            $record = $this->plotRepository->getRecord($plotId);
            if (! $record) {
                \Flash::error(trans('errors.recordNotFound'));
                return \Redirect::back();
            }

            if ($this->plotRepository->updateRecord($record->id, $inputs)) {
                $this->plotRepository->syncRecords($record->id);
                $success = true;
            }
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
            \Log::info($errors);
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
            \Log::info($errors);
        }

        if ($success) {
            \Flash::success(trans('projectReportChart.templateUpdated'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('projectReport.chart.plot.template.index', array($chartId));
    }

    public function destroy($chartId, $plotId)
    {
        $errors  = null;
        $success = false;

        $record = $this->plotRepository->getRecord($plotId);
        if (! $record) {
            \Flash::error(trans('errors.recordNotFound'));
            return \Redirect::back();
        }

        $transaction = new DBTransaction();

        try
        {
            $transaction->begin();

            $success = $this->plotRepository->deleteRecord($plotId);
            if ($success) {
                if ($this->plotRepository->getTotalPlots($record->project_report_chart_id) === 0) { // Chart has no more plots
                    $this->chartRepository->publishRecord($record->project_report_chart_id, false); // Unpublish chart
                }
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
}