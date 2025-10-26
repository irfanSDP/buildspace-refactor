<?php namespace PCK\Inspections;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Verifier\Verifier;

class Inspection extends Model implements Verifiable{

    use ModuleAttachmentTrait;

    protected $table = 'inspections';

    protected $fillable = [
        'request_for_inspection_id',
        'status',
        'decision',
        'comments',
        'revision',
        'ready_for_inspection_date',
    ];

    const STATUS_DRAFT       = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_VERIFYING   = 3;
    const STATUS_COMPLETE    = 4;

    const DECISION_ALLOWED_TO_PROCEED                     = 1;
    const DECISION_ALLOWED_TO_PROCEED_WITH_REMEDIAL_WORKS = 2;
    const DECISION_NOT_ALLOWED_TO_PROCEED                 = 3;

    public function requestForInspection()
    {
        return $this->belongsTo('PCK\Inspections\RequestForInspection');
    }

    public function getPreviousInspection()
    {
        return self::where('request_for_inspection_id', '=', $this->request_for_inspection_id)
            ->where('revision', '=', $this->revision - 1)
            ->first();
    }

    public function inspectionResults()
    {
        return $this->hasMany('PCK\Inspections\InspectionResult');
    }

    public function isDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

    public function isInProgress()
    {
        return $this->status == self::STATUS_IN_PROGRESS;
    }

    public function isVerifying()
    {
        return $this->status == self::STATUS_VERIFYING;
    }

    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETE;
    }

    public function isFirstInspection()
    {
        return $this->revision == 0;
    }

    public function getStatusText()
    {
        if( $this->status == self::STATUS_DRAFT && $this->revision > 0 )
        {
            return trans('inspection.rejectedN', array('no' => $this->revision));
        }

        $statusText = [
            self::STATUS_DRAFT       => trans('inspection.draft'),
            self::STATUS_IN_PROGRESS => trans('inspection.inProgress'),
            self::STATUS_VERIFYING   => trans('inspection.verifying'),
            self::STATUS_COMPLETE    => trans('inspection.complete'),
        ];

        return $statusText[$this->status];
    }

    public function getSubmitters()
    {
        $rootList = InspectionListCategory::where('inspection_list_id', '=', $this->requestForInspection->inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $this->requestForInspection->inspectionListCategory->lft)
            ->where('rgt', '>=', $this->requestForInspection->inspectionListCategory->rgt)
            ->where('depth', '=', 0)
            ->first();

        $listGroupPivot = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $rootList->id)
            ->first();

        if( ! $listGroupPivot ) return array();

        return InspectionSubmitter::where('inspection_group_id', '=', $listGroupPivot->inspection_group_id)
            ->lists('user_id');
    }

    public function getRequesters()
    {
        $rootList = InspectionListCategory::where('inspection_list_id', '=', $this->requestForInspection->inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $this->requestForInspection->inspectionListCategory->lft)
            ->where('rgt', '>=', $this->requestForInspection->inspectionListCategory->rgt)
            ->where('depth', '=', 0)
            ->first();

        $listGroupPivot = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $rootList->id)
            ->first();

        if( ! $listGroupPivot ) return array();

        return InspectionGroupUser::whereHas('role', function($query){
                $query->where('can_request_inspection', '=', true);
            })
            ->where('inspection_group_id', '=', $listGroupPivot->inspection_group_id)
            ->lists('user_id');
    }

    public function getInspectors()
    {
        $rootList = InspectionListCategory::where('inspection_list_id', '=', $this->requestForInspection->inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $this->requestForInspection->inspectionListCategory->lft)
            ->where('rgt', '>=', $this->requestForInspection->inspectionListCategory->rgt)
            ->where('depth', '=', 0)
            ->first();

        $listGroupPivot = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $rootList->id)
            ->first();

        if( ! $listGroupPivot ) return array();

        return InspectionGroupUser::where('inspection_group_id', '=', $listGroupPivot->inspection_group_id)
            ->lists('user_id');
    }

    // gets the lowest average across all roles
    public function getLowestCompletionPercentage()
    {
        $inspectionResultAverages = [];

        foreach($this->inspectionResults as $inspectionResult)
        {
            array_push($inspectionResultAverages, number_format(InspectionItemResult::where('inspection_result_id', $inspectionResult->id)->avg('progress_status'), 2, '.', ''));
        }

        return min($inspectionResultAverages);
    }

    public static function getInspectionItemResults(Inspection $inspection)
    {
        $results = array();

        $itemIds = $inspection->requestForInspection->inspectionListCategory->inspectionListItems()->lists('id');

        if( empty($itemIds) ) return $results;

		$itemResults = \DB::select(\DB::raw("SELECT ir.id as inspection_item_result_id, r.inspection_role_id, ir.inspection_list_item_id, ir.progress_status, ir.remarks
			FROM inspection_item_results ir
			JOIN inspection_results r ON r.id = ir.inspection_result_id
			WHERE r.inspection_id = {$inspection->id}
            AND ir.inspection_list_item_id IN (".implode(',', $itemIds).");
		"));

		foreach($itemResults as $itemResult)
		{
			if( ! array_key_exists($itemResult->inspection_list_item_id, $results) )
			{
				$results[ $itemResult->inspection_list_item_id ] = array();
			}

			$results[ $itemResult->inspection_list_item_id ][ $itemResult->inspection_role_id ] = array(
                'inspection_item_result_id' => $itemResult->inspection_item_result_id,
				'progress_status'           => $itemResult->progress_status,
				'remarks'                   => $itemResult->remarks,
			);
		}

        return $results;
    }

    public function issueReInspection()
    {
        $newInspection = self::create(array(
            'request_for_inspection_id' => $this->request_for_inspection_id,
            'status'                    => self::STATUS_DRAFT,
            'revision'                  => $this->revision + 1,
        ));

        $inspectionResults = InspectionResult::where('inspection_id', '=', $this->id)->get();

        $timestamp = \Carbon\Carbon::now();

        foreach($inspectionResults as $inspectionResult)
        {
            $newInspectionResult = InspectionResult::create(array(
                'inspection_id'      => $newInspection->id,
                'inspection_role_id' => $inspectionResult->inspection_role_id,
            ));

            $newInspectionItemResults = InspectionItemResult::where('inspection_result_id', '=', $inspectionResult->id)->get();

            $rows = array();

            foreach($newInspectionItemResults as $newInspectionItemResult)
            {
                $rows[] = array(
                    'inspection_result_id'    => $newInspectionResult->id,
                    'inspection_list_item_id' => $newInspectionItemResult->inspection_list_item_id,
                    'progress_status'         => $newInspectionItemResult->progress_status,
                    'created_at'              => $timestamp,
                    'updated_at'              => $timestamp,
                );
            }
            if( ! empty($rows) ) \DB::table('inspection_item_results')->insert($rows);
        }
    }

    public function getOnApprovedFunction()
    {
        $this->status = self::STATUS_COMPLETE;
        $this->save();

        $emailNotifier = \App::make('PCK\Notifications\EmailNotifier');

        $view = null;

        if( $this->decision == self::DECISION_ALLOWED_TO_PROCEED )
        {
            $view = 'inspection.allowed_to_proceed';
        }

        if( $this->decision == self::DECISION_ALLOWED_TO_PROCEED_WITH_REMEDIAL_WORKS )
        {
            $view = 'inspection.allowed_to_proceed_remedial_work_to_be_completed';
        }

        if( $this->decision == self::DECISION_NOT_ALLOWED_TO_PROCEED )
        {
            $this->issueReInspection();

            $view = 'inspection.not_allowed_to_proceed';
        }

        $requestors   = $this->getRequesters();
        $inspectors   = $this->getInspectors();
        $recipientIds = array_unique(array_merge($requestors, $inspectors));

        foreach($recipientIds as $recipientId)
        {
            $recipient = User::find($recipientId);

            $emailNotifier->sendRequestForInspectionEmail($this->requestForInspection->project, $this, null, $recipient, $view);
        }
    }

    public function getOnRejectedFunction()
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->save();
    }

    public function getOnPendingFunction()
    {
        return function()
        {
            if($this->hasApprovals() && Verifier::isBeingVerified($this))
            {
                $emailNotifier = \App::make('PCK\Notifications\EmailNotifier');
                $view          = 'inspection.approved';
                $user          = \Confide::user();
    
                foreach($this->getSubmitters() as $recipientId)
                {
                    $recipient = User::find($recipientId);
    
                    $emailNotifier->sendRequestForInspectionEmail($this->requestForInspection->project, $this, $user, $recipient, $view);
                }
            }
        };
    }

    public function getOnApprovedView()
    {
        return 'inspection.approved';
    }

    public function getOnRejectedView()
    {
        return 'inspection.rejected';
    }

    public function getOnPendingView()
    {
        return 'inspection.pending';
    }

    public function getRoute()
    {
        return $this->requestForInspection->getShowRoute(\Confide::user());
    }

    public function getViewData($locale)
    {
        $viewData = [
            'senderName'	  => \Confide::user()->name,
            'project_title'   => $this->requestForInspection->project->title,
            'description'     => $this->getObjectDescription(),
            'toRoute' 		  => $this->getRoute(),
            'recipientLocale' => $locale,
        ];

        if($currentVerifier = Verifier::getCurrentVerifier($this))
        {
            $viewData['recipientName'] = $currentVerifier->name;
        }

        return $viewData;
    }

    public function getOnApprovedNotifyList()
    {
        $submitterIds = $this->getSubmitters();

        $verifierIds = Verifier::where('object_id', '=', $this->id)
            ->where('object_type', '=', get_class($this))
            ->whereNotNull('approved')
            ->lists('verifier_id');

        return User::whereIn('id', $this->getSubmitters())
            ->orWhereIn('id', $verifierIds)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getOnRejectedNotifyList()
    {
        return $this->getOnApprovedNotifyList();
    }

    public function onReview(){}

    public function getEmailSubject($locale)
    {
        return trans('inspection.requestForInspection');
    }

    public function getProject()
    {
        return $this->requestForInspection->project;
    }

    public function getSubmitterId()
    {
        return $this->requestForInspection->submitter->id;
    }

    public function getModuleName()
    {
        return trans('modules.requestForInspection');
    }

    public function getRevisionText()
    {
        return trans('inspection.inspection') . ' ' . ($this->revision + 1);
    }

    public function getObjectDescription()
    {
        return $this->requestForInspection->inspectionListCategory->name . ' (' . $this->getRevisionText() . ')';
    }

    public function getDaysPendingAttribute()
    {
        $then = Carbon::parse($this->updated_at);
        $now = Carbon::now();

        return $then->diffInDays($now);
    }

    // at least (1) approval has been completed
    public function hasApprovals()
    {
        $verifiedRecords = Verifier::getAssignedVerifierRecords($this);

        foreach($verifiedRecords as $verifiedRecord)
        {
            if($verifiedRecord->approved) return true;
        }

        return false;
    }

    public function hasSubmitted(InspectionRole $role)
    {
        $recordsCount = InspectionResult::where('inspection_id', '=', $this->id)
            ->where('inspection_role_id', '=', $role->id)
            ->where('status', '=', InspectionResult::STATUS_SUBMITTED)
            ->count();

        return $recordsCount > 0;
    }
}