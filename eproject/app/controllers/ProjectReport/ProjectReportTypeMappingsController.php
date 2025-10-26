<?php namespace ProjectReport;

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\ProjectReport\ProjectReportType;
use PCK\ProjectReport\ProjectReportTypeMapping;
use PCK\ProjectReport\ProjectReportTypeMappingRepository;

class ProjectReportTypeMappingsController extends \Controller
{
    private $repository;

    public function __construct(ProjectReportTypeMappingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index($reportTypeId)
    {
        $reportType = ProjectReportType::find($reportTypeId);

        return \View::make('project_report.template.mapping.bindings.index', [
            'reportType' => $reportType,
        ]);
    }

    public function list($reportTypeId)
    {
        $reportType = ProjectReportType::find($reportTypeId);

        $data = $this->repository->list($reportType);

        return \Response::json($data);
    }

    public function bind($reportTypeId, $mappingId)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $binding                    = ProjectReportTypeMapping::find($mappingId);
            $binding->project_report_id = $inputs['templateId'];
            $binding->save();

            $success = true;
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }
        
        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    // public function unbind($reportTypeId, $mappingId)
    // {
    //     $errors  = null;
    //     $success = false;

    //     try
    //     {
    //         $binding                    = ProjectReportTypeMapping::find($mappingId);
    //         $binding->project_report_id = null;
    //         $binding->save();

    //         $success = true;
    //     }
    //     catch(\Exception $e)
    //     {
    //         $errors = $e->getMessage();
    //     }
        
    //     return \Response::json([
    //         'success' => $success,
    //         'errors'  => $errors,
    //     ]);
    // }

    public function updateLatestRevSetting($reportTypeId, $mappingId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $record = ProjectReportTypeMapping::find($mappingId);
            if ($record->latest_rev) {
                $record->latest_rev = false;
            } else {
                $record->latest_rev = true;
            }
            $record->save();

            $success = true;
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }

        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function lock($reportTypeId, $mappingId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $record = ProjectReportTypeMapping::find($mappingId);
            $record->is_locked = true;
            $record->save();
            $success = true;

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