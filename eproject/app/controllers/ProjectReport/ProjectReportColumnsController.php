<?php namespace ProjectReport;

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\ProjectReport\ProjectReportColumnRepository;
use PCK\Forms\ProjectReport\ProjectReportTemplateColumnForm;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportColumn;

class ProjectReportColumnsController extends \Controller
{
    private $repository;
    private $form;

    public function __construct(ProjectReportColumnRepository $repository, ProjectReportTemplateColumnForm $form)
    {
        $this->repository = $repository;
        $this->form       = $form;
    }

    public function getColumns($projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);
        $columns       = $this->repository->getColumns($projectReport);

        return \Response::json($columns);
    }

    public function store($templateId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);

            $template = ProjectReport::find($templateId);

            $this->repository->createNewColumn($template, $inputs);

            $transaction->commit();

            $success = true;
        } catch (ValidationException $e) {
            $errors = $e->getMessageBag();
        } catch (\Exception $e) {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update($templateId, $columnId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $column = ProjectReportColumn::find($columnId);

            $this->form->validate($inputs);

            $template = ProjectReport::find($templateId);
            
            $this->repository->updateColumn($column, $inputs);

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
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function destroy($templateId, $columnId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $template = ProjectReport::find($templateId);
            $column   = ProjectReportColumn::find($columnId);

            $column->delete();

            $transaction->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function swap($templatId, $columnId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $draggedColumn = ProjectReportColumn::find($inputs['draggedColumnId']);
            $swappedColumn = ProjectReportColumn::find($inputs['swappedColumnId']);

            $this->repository->swap($draggedColumn, $swappedColumn);

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
}