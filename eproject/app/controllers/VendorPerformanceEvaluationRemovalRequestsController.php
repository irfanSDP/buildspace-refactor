<?php

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\ProjectRemovalReason;
use PCK\VendorPerformanceEvaluation\RemovalRequest;
use PCK\Settings\SystemSettings;
use PCK\Notifications\EmailNotifier;

class VendorPerformanceEvaluationRemovalRequestsController extends \BaseController
{
    protected $emailNotifier;

    public function __construct(EmailNotifier $emailNotifier)
    {
        $this->emailNotifier = $emailNotifier;
    }

    public function index()
    {
        $query = "SELECT vperr.id AS id
                  FROM vendor_performance_evaluation_removal_requests vperr 
                  INNER JOIN vendor_performance_evaluations vpe ON vpe.id = vperr.vendor_performance_evaluation_id 
                  JOIN vendor_performance_evaluation_cycles c on c.id = vpe.vendor_performance_evaluation_cycle_id
                  INNER JOIN projects p ON p.id = vpe.project_id 
                  WHERE vpe.deleted_at IS NULL
                  AND c.is_completed IS FALSE
                  AND vpe.status_id = " . VendorPerformanceEvaluation::STATUS_IN_PROGRESS . "
                  AND vperr.deleted_at IS NULL
                  AND vperr.evaluation_removed IS FALSE
                  AND p.deleted_at IS NULL
                  ORDER BY vperr.created_at ASC;";

        $removalRequestIds = array_column(DB::select(DB::raw($query)), 'id');

        $data = [];

        foreach(RemovalRequest::whereIn('id', $removalRequestIds)->get() as $removalRequest)
        {
            if(is_null($removalRequest->vendorPerformanceEvaluation->project)) continue;

            $data[] = [
                'id'              => $removalRequest->id,
                'title'           => $removalRequest->vendorPerformanceEvaluation->project->title,
                'userName'        => $removalRequest->user->name,
                'userEmail'       => $removalRequest->user->username,
                'company'         => $removalRequest->company->name,
                'reason'          => $removalRequest->projectRemovalReason ? $removalRequest->projectRemovalReason->name : $removalRequest->vendor_performance_evaluation_project_removal_reason_text,
                'remarks'         => $removalRequest->request_remarks,
                'route:view'      => route('vendorPerformanceEvaluation.setups.index', array("evaluations[]={$removalRequest->vendorPerformanceEvaluation->id}")),
                'route:cycleEdit' => route('vendorPerformanceEvaluation.cycle.edit', array($removalRequest->vendorPerformanceEvaluation->vendor_performance_evaluation_cycle_id)),
                'route:destroy'   => route('vendorPerformanceEvaluation.evaluations.removalRequest.destroy', array($removalRequest->id)),
            ];
        }

        return View::make('vendor_performance_evaluation.removal_requests.index', compact('data'));
    }

    public function create($evaluationId)
    {
        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $reasons = ProjectRemovalReason::orderBy('name', 'asc')->where('hidden', '=', false)->lists('name', 'id');

        if( SystemSettings::getValue('allow_other_vpe_project_removal_reasons') ) $reasons['others'] = trans('forms.othersPleaseSpecify');

        return View::make('vendor_performance_evaluation.removal_requests.create', compact('evaluation', 'reasons'));
    }

    public function store($evaluationId)
    {
        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $input = Input::get();

        $remarks = trim($input['remarks']);

        if( $input['vendor_performance_evaluation_project_removal_reason_id'] == 'others' ) $input['vendor_performance_evaluation_project_removal_reason_id'] = null;

        RemovalRequest::create(array(
            'company_id'                                                => $user->getAssignedCompany($evaluation->project)->id,
            'vendor_performance_evaluation_id'                          => $evaluation->id,
            'user_id'                                                   => $user->id,
            'vendor_performance_evaluation_project_removal_reason_id'   => $input['vendor_performance_evaluation_project_removal_reason_id'],
            'vendor_performance_evaluation_project_removal_reason_text' => $input['vendor_performance_evaluation_project_removal_reason_text'] ?? null,
            'request_remarks'                                           => $remarks,
        ));

        $this->emailNotifier->sendVpeProjectRemovalFromEvaluationRequestNotification($evaluation, $remarks);

        \Flash::success(trans('vendorManagement.sentRequest'));

        return Redirect::route('vendorPerformanceEvaluation.index');
    }

    public function destroy($removalRequestId)
    {
        $errors  = null;
        $success = false;
        $inputs  = Input::all();
        $remarks = trim($inputs['remarks']);
        $user    = \Confide::user();

        try
        {
            $removalRequest = RemovalRequest::find($removalRequestId);

            if($removalRequest)
            {
                $removalRequest->update([
                    'deleted_at'        => \Carbon\Carbon::now()->toDateTimeString(),
                    'action_by'         => $user->id,
                    'dismissal_remarks' => $remarks,
                ]);
            }

            $success = true;
        }
        catch(Exception $e)
        {
            \Log::error('VendorPerformanceEvaluationRemovalRequestsController@destroy : ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
        
        if($success)
        {
            $this->emailNotifier->sendVpeProjectRemovalRequestDismissedNotification($removalRequest, $remarks);
        }

        return Response::json([
            'errors'  => $errors,
            'success' => $success,
        ]);
    }
}