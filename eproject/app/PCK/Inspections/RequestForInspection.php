<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Buildspace\ProjectStructureLocationCode;
use PCK\Filters\InspectionFilters;
use PCK\Verifier\Verifier;

class RequestForInspection extends Model{

    protected $table = 'request_for_inspections';

    protected $fillable = [
        'project_id',
        'location_id',
        'inspection_list_category_id',
        'submitted_by',
    ];

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }

    public function submitter()
    {
        return $this->belongsTo('PCK\Users\User', 'submitted_by');
    }

    public function inspectionListCategory()
    {
    	return $this->belongsTo('PCK\Inspections\InspectionListCategory');
    }

    public function inspections()
    {
        return $this->hasMany('PCK\Inspections\Inspection')->orderBy('revision');
    }

    public function latestInspection()
    {
        return $this->hasOne('PCK\Inspections\Inspection')->orderBy('id', 'DESC');
    }

    public function isDraft()
    {
        return ( $this->latestInspection->revision == 0 && $this->latestInspection->status == Inspection::STATUS_DRAFT );
    }

    public function getFormInfo()
    {
        $location = ProjectStructureLocationCode::find($this->location_id);

        $locationsDescription = ProjectStructureLocationCode::where('root_id', '=', $location->root_id)
            ->where('lft', '<=', $location->lft)
            ->where('rgt', '>=', $location->rgt)
            ->orderBy('lft', 'asc')
            ->orderBy('priority', 'asc')
            ->lists('description');

        $inspectionListNames = InspectionListCategory::where('inspection_list_id', '=', $this->inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $this->inspectionListCategory->lft)
            ->where('rgt', '>=', $this->inspectionListCategory->rgt)
            ->orderBy('lft', 'asc')
            ->lists('name');

        $additionalFields = InspectionListCategoryAdditionalField::where('inspection_list_category_id', '=', $this->inspection_list_category_id)
            ->orderBy('priority')
            ->get();

        $additionalFieldsData = array();

        foreach($additionalFields as $additionalField)
        {
            $additionalFieldsData[] = array(
                'id'    => $additionalField->id,
                'name'  => $additionalField->name,
                'value' => $additionalField->value,
            );
        }

        return array(
            'locationsDescription' => $locationsDescription,
            'inspectionListNames'  => $inspectionListNames,
            'additionalFields'     => $additionalFieldsData,
        );
    }

    public function getInspectionItemResults()
    {
        $results = array();

        $itemIds = $this->inspectionListCategory->inspectionListItems()->lists('id');

        if( empty($itemIds) ) return $results;

        $itemResults = \DB::select(\DB::raw("SELECT ir.id as inspection_item_result_id, r.inspection_role_id, i.id as inspection_id, ir.inspection_list_item_id, ir.progress_status, ir.remarks
            FROM inspection_item_results ir
            JOIN inspection_results r ON r.id = ir.inspection_result_id
            JOIN inspections i ON i.id = r.inspection_id
            WHERE i.request_for_inspection_id = {$this->id}
            AND ir.inspection_list_item_id IN (".implode(',', $itemIds).");
        "));

        foreach($itemResults as $itemResult)
        {
            if( ! array_key_exists($itemResult->inspection_list_item_id, $results) )
            {
                $results[ $itemResult->inspection_list_item_id ] = array();
            }

            if( ! array_key_exists($itemResult->inspection_id, $results[ $itemResult->inspection_list_item_id ]) )
            {
                $results[ $itemResult->inspection_list_item_id ][ $itemResult->inspection_id ] = array();
            }

            $results[ $itemResult->inspection_list_item_id ][ $itemResult->inspection_id][ $itemResult->inspection_role_id ] = array(
                'inspection_item_result_id' => $itemResult->inspection_item_result_id,
                'progress_status'           => $itemResult->progress_status,
                'remarks'                   => $itemResult->remarks,
            );
        }

        return $results;
    }

    public function getShowRoute(User $user)
    {
        if( $this->isDraft() )
        {
            if( in_array($user->id, $this->latestInspection->getRequesters()) )
            {
                return route('inspection.request.edit', array($this->project_id, $this->id));
            }
            else
            {
                return false;
            }
        }

        $route = false;

        switch($this->latestInspection->status)
        {
            case Inspection::STATUS_DRAFT:
                if( in_array($user->id, $this->latestInspection->getRequesters()) )
                {
                    $route = route('inspection.edit', [$this->project->id, $this->id, $this->latestInspection->id]);
                }
                elseif( in_array($user->id, $this->latestInspection->getSubmitters()) )
                {
                    $route = route('inspection.submit.form', [$this->project->id, $this->id, $this->latestInspection->getPreviousInspection()->id]);
                }
                elseif( in_array($user->id, $this->latestInspection->getInspectors()) )
                {
                    $route = route('inspection.inspect', [$this->project->id, $this->id, $this->latestInspection->getPreviousInspection()->id]);
                }
                break;
            case Inspection::STATUS_IN_PROGRESS:
                $route = route('inspection.submit.form', [$this->project->id, $this->id, $this->latestInspection->id]);

                if(
                    ( $role = InspectionRole::getRole($this->latestInspection, $user) )
                    && ! $this->latestInspection->hasSubmitted($role)
                    && in_array($user->id, $this->latestInspection->getInspectors())
                )
                {
                    $route = route('inspection.inspect', [$this->project->id, $this->id, $this->latestInspection->id]);
                }
                break;
            case Inspection::STATUS_VERIFYING:
            case Inspection::STATUS_COMPLETE:
                if( Verifier::isAVerifier($user, $this->latestInspection) || in_array($user->id, $this->latestInspection->getSubmitters()) || in_array($user->id, $this->latestInspection->getInspectors()) )
                {
                    $route = route('inspection.submit.form', [$this->project->id, $this->id, $this->latestInspection->id]);
                }
                break;
        }

        return $route;
    }

    public static function getPendingRequestForInspections(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingRequestForInspections = new Collection;

        $requestForInspections = ($project) ? $project->requestForInspections : self::all();

        foreach($requestForInspections as $requestForInspection)
        {
            foreach($requestForInspection->inspections as $inspection)
            {
                if($inspection->isCompleted()) continue;

                if($inspection->isDraft() && (!$inspection->isFirstInspection()) && in_array($user->id, $inspection->getRequesters()))
                {
                    $pendingRequestForInspections->push($inspection);
                }

                if($inspection->isInProgress())
                {
                    $hasSubmitterTask = (in_array($user->id, $inspection->getSubmitters()) && InspectionFilters::readyForSubmission($inspection));

                    if($hasSubmitterTask)
                    {
                        $pendingRequestForInspections->push($inspection);
                    }
                    else
                    {
                        
                        if($role = InspectionRole::getRole($inspection, $user))
                        {
                            $roleResult                     = InspectionResult::where('inspection_role_id', '=', $role->id)->where('inspection_id', '=', $inspection->id)->first();
                            $hasNotUpdatedAnyProgress       = is_null($roleResult) && in_array($user->id, $inspection->getInspectors());
                            $updatedProgressButNotSubmitted = ($roleResult && $roleResult->isInProgress() && in_array($user->id, $inspection->getInspectors()));
    
                            if($hasNotUpdatedAnyProgress || $updatedProgressButNotSubmitted)
                            {
                                $pendingRequestForInspections->push($inspection);
                            }
                        }
                    }
                }

                if($inspection->isVerifying())
                {
                    $isCurrentVerifier = Verifier::isCurrentVerifier($user, $inspection);
                    $proceed           = $includeFutureTasks ? Verifier::isAVerifierInline($user, $inspection) : $isCurrentVerifier;

                    if($proceed)
                    {
                        $inspection['is_future_task'] = ! $isCurrentVerifier;

                        $pendingRequestForInspections->push($inspection);
                    }
                }
            }
        }

        return $pendingRequestForInspections;
    }
}