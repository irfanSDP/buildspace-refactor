<?php namespace ProjectReport;

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\ProjectReport\ProjectReportTypeRepository;
use PCK\ProjectReport\ProjectReportRepository;
use PCK\ProjectReport\ProjectReportType;
use PCK\Forms\ProjectReport\ProjectReportTypeForm;

class ProjectReportTypesController extends \Controller
{
    private $repository;
    private $projectReportRepository;
    private $form;

    public function __construct(ProjectReportTypeRepository $repository, ProjectReportRepository $projectReportRepository, ProjectReportTypeForm $form)
    {
        $this->repository              = $repository;
        $this->projectReportRepository = $projectReportRepository;
        $this->form                    = $form;
    }

    public function index()
    {
        return \View::make('project_report.template.mapping.index');
    }

    public function listLatestApprovedTemplates()
    {
        $data = $this->projectReportRepository->listLatestApprovedTemplates();

        return \Response::json($data);
    }

    public function list()
    {
        $data = $this->repository->listMappings();

        return \Response::json($data);
    }

    public function store()
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);
            $this->repository->createNewMapping($inputs['title']);

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

    public function update($reportTypeId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);

            $record = ProjectReportType::find($reportTypeId);

            $this->repository->updateMapping($record, $inputs['title']);

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

    public function delete($reportTypeId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $record = ProjectReportType::find($reportTypeId);
            $record->delete();
    
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