<?php

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Buildspace\ProjectStructureLocationCode;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListCategoryAdditionalField;
use PCK\Inspections\InspectionListItem;
use PCK\Inspections\RequestForInspection;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionGroupUser;
use PCK\Inspections\InspectionGroupInspectionListCategory;
use PCK\Inspections\InspectionVerifierTemplate;
use PCK\Inspections\InspectionSubmitter;
use PCK\Filters\InspectionFilters;
use PCK\Forms\RequestForInspectionForm;
use PCK\Notifications\EmailNotifier;

class RequestForInspectionController extends \BaseController {

    protected $requestForInspectionForm;
    protected $emailNotifier;

    public function __construct(RequestForInspectionForm $requestForInspectionForm, EmailNotifier $emailNotifier)
    {
        $this->requestForInspectionForm = $requestForInspectionForm;
        $this->emailNotifier            = $emailNotifier;
    }

    public function index(Project $project)
    {
        return View::make('request_for_inspection.index', array(
            'project'               => $project,
        ));
    }

    public function getRequestForInspections(Project $project)
    {
        $user = \Confide::user();

        $groupIds = InspectionGroupUser::where('user_id', '=', $user->id)->lists('inspection_group_id');
        $groupIds = array_merge($groupIds, InspectionVerifierTemplate::where('user_id', '=', $user->id)->lists('inspection_group_id'));
        $groupIds = array_merge($groupIds, InspectionSubmitter::where('user_id', '=', $user->id)->lists('inspection_group_id'));

        $assignedRootIds = InspectionGroupInspectionListCategory::whereIn('inspection_group_id', $groupIds)
            ->lists('inspection_list_category_id');

        if( empty($assignedRootIds) )
        {
            return array();
        }

        $assignedRequestsForInspection = \DB::select(\DB::raw("SELECT rfi.id
            FROM request_for_inspections rfi
            JOIN inspection_list_categories list ON list.id = rfi.inspection_list_category_id
            JOIN inspection_list_categories root ON root.lft <= list.lft AND root.rgt >= list.rgt AND root.depth = 0
            WHERE rfi.project_id = {$project->id}
            AND root.id IN (".implode(',', $assignedRootIds).")
        "));

        $requestForInspectionIds = array();

        foreach($assignedRequestsForInspection as $obj)
        {
            $requestForInspectionIds[] = $obj->id;
        }

        $requestsForInspection = RequestForInspection::where('project_id', '=', $project->id)
            ->whereIn('id', $requestForInspectionIds)
            ->orderBy('created_at', 'DESC')
            ->get();

        $requestsForInspection = $requestsForInspection->reject(function($item) use ($user) {
            return ( $item->isDraft() && ! in_array($user->id, $item->latestInspection->getRequesters()) );
        });

        $data = [];

        foreach($requestsForInspection as $record)
        {
            $completionPercentage = '-';

            if(InspectionFilters::readyForSubmission($record->latestInspection))
            {
                $completionPercentage = $record->latestInspection->getLowestCompletionPercentage();
            }

            array_push($data, [
                'id'                            => $record->id,
                'dateIssued'                    => Carbon::parse($project->getProjectTimeZoneTime($record->created_at))->format(\Config::get('dates.readable_timestamp')),
                'inspectionListCategoryId'      => $record->inspectionListCategory->id,
                'inspectionListCategoryName'    => $record->inspectionListCategory->name,
                'completion'                    => $completionPercentage,
                'readyForInspectionDate'        => $record->latestInspection->ready_for_inspection_date ? Carbon::parse($project->getProjectTimeZoneTime($record->latestInspection->ready_for_inspection_date))->format(\Config::get('dates.readable_timestamp')) : null,
                'status'                        => $record->latestInspection->getStatusText(),
                'route_show'                    => $record->getShowRoute($user),
            ]);
        }

        return Response::json($data);
    }

    public function create(Project $project)
    {
        JavaScript::put(
            array(
                'formData' => array(
                    'locations'        => Input::old('location_description'),
                    'inspectionLists'  => Input::old('inspection_list_name'),
                    'additionalFields' => Input::old('additional_fields'),
                )
            )
        );

        return View::make('request_for_inspection.create', array(
            'project' => $project,
        ));
    }

    public function store(Project $project)
    {
        $input = Input::all();

        $this->requestForInspectionForm->validate($input);

        $user = \Confide::user();

        $requestForInspection = RequestForInspection::create(array(
            'project_id'                  => $project->id,
            'location_id'                 => $input['location_id'],
            'inspection_list_category_id' => $input['inspection_list_category_id'],
            'submitted_by'                => $user->id,
        ));

        $additionalFields = $input['additional_fields'] ?? array();

        foreach($additionalFields as $fieldId => $fieldValue)
        {
            InspectionListCategoryAdditionalField::where('id', '=', $fieldId)->update(array('value' => $fieldValue));
        }

        $inspection = Inspection::create(array(
            'request_for_inspection_id' => $requestForInspection->id,
            'status'                    => isset($input['submit']) ? Inspection::STATUS_IN_PROGRESS : Inspection::STATUS_DRAFT,
            'ready_for_inspection_date' => $input['ready_for_inspection_date'],
        ));

        $requestForInspection->load('latestInspection');

        if( $inspection->status == Inspection::STATUS_IN_PROGRESS )
        {
            $requesters   = $requestForInspection->latestInspection->getRequesters();
			$inspectors   = $requestForInspection->latestInspection->getInspectors();
            $recipientIds = array_unique(array_merge($requesters, $inspectors));

            foreach($recipientIds as $recipientId)
            {
                $recipient = User::find($recipientId);

                $this->emailNotifier->sendRequestForInspectionEmail($project, $requestForInspection->latestInspection, $requestForInspection->submitter, $recipient, 'inspection.inspection_raised');
            }

            return Redirect::to(route('inspection.inspect', array($project->id, $requestForInspection->id, $inspection->id)));
        }

        return Redirect::to(route('inspection.request.edit', array($project->id, $requestForInspection->id)));
    }

    public function edit(Project $project, $requestForInspectionId)
    {
        $requestForInspection = RequestForInspection::where('project_id', '=', $project->id)
            ->where('id', '=', $requestForInspectionId)
            ->first();

        $location = ProjectStructureLocationCode::find($requestForInspection->location_id);

        $locationsDescription = ProjectStructureLocationCode::where('root_id', '=', $location->root_id)
            ->where('lft', '<=', $location->lft)
            ->where('rgt', '>=', $location->rgt)
            ->orderBy('lft', 'asc')
            ->lists('description');

        $inspectionListNames = InspectionListCategory::where('inspection_list_id', '=', $requestForInspection->inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $requestForInspection->inspectionListCategory->lft)
            ->where('rgt', '>=', $requestForInspection->inspectionListCategory->rgt)
            ->orderBy('lft', 'asc')
            ->lists('name');

        JavaScript::put(
            array(
                'formData' => array(
                    'locations'        => Input::old('location_description') ?? $locationsDescription,
                    'inspectionLists'  => Input::old('inspection_list_name') ?? $inspectionListNames,
                    'additionalFields' => Input::old('additional_fields'),
                )
            )
        );

        return View::make('request_for_inspection.create', array(
            'project'              => $project,
            'requestForInspection' => $requestForInspection,
        ));
    }

    public function update(Project $project, $requestForInspectionId)
    {
        $input = Input::all();

        $this->requestForInspectionForm->validate($input);

        $user = \Confide::user();

        $requestForInspection = RequestForInspection::where('project_id', '=', $project->id)
            ->where('id', '=', $requestForInspectionId)
            ->first();

        $requestForInspection->location_id                 = $input['location_id'];
        $requestForInspection->inspection_list_category_id = $input['inspection_list_category_id'];
        $requestForInspection->submitted_by                = $user->id;

        $requestForInspection->save();

        $additionalFields = $input['additional_fields'] ?? array();

        foreach($additionalFields as $fieldId => $fieldValue)
        {
            InspectionListCategoryAdditionalField::where('id', '=', $fieldId)->update(array('value' => $fieldValue));
        }

        $requestForInspection->latestInspection->update(array(
            'request_for_inspection_id' => $requestForInspection->id,
            'status'                    => isset($input['submit']) ? Inspection::STATUS_IN_PROGRESS : Inspection::STATUS_DRAFT,
            'ready_for_inspection_date' => $input['ready_for_inspection_date'],
        ));

        $requestForInspection->load('latestInspection');

        if( $requestForInspection->latestInspection->status == Inspection::STATUS_IN_PROGRESS )
        {
            foreach($requestForInspection->latestInspection->getInspectors() as $recipientId)
            {
                $recipient = User::find($recipientId);

                $this->emailNotifier->sendRequestForInspectionEmail($project, $requestForInspection->latestInspection, $requestForInspection->submitter, $recipient, 'inspection.inspection_raised');
            }

            return Redirect::to(route('inspection.inspect', array($project->id, $requestForInspection->id, $requestForInspection->latestInspection->id)));
        }

        return Redirect::back()->withInput();
    }

    public function getLocationByLevel(Project $project)
    {
        $input = Input::all();

        if( ! empty($input['id']) && $input['id'] > 0)
        {
            $location = ProjectStructureLocationCode::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
                ->where('id', '=', $input['id'])
                ->first();

            $records = ProjectStructureLocationCode::where("root_id", $location->root_id)
                ->where("lft", ">", $location->lft)
                ->where("rgt", "<", $location->rgt)
                ->where("level", $location->level + 1)
                ->orderBy("lft", "asc")
                ->orderBy("priority", "asc")
                ->get();
        }
        else
        {
            $records = ProjectStructureLocationCode::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
                ->whereRaw(\DB::raw('root_id = id'))
                ->where('level', '=', 0)
                ->get();
        }

        $data = [];

        foreach($records as $record)
        {
            $data[] = array(
                'id'          => $record->id,
                'description' => $record->description
            );
        }

        return $data;
    }

    public function getInspectionListByLevel(Project $project)
    {
        $user = \Confide::user();

        $input = Input::all();

        $usedListIds = RequestForInspection::whereHas('inspectionListCategory', function($query) use ($project){
            $query->whereHas('inspectionList', function($query) use ($project){
                $query->where('project_id', '=', $project->id);
            });
        })->lists('inspection_list_category_id');

        if( ! empty($input['id']) && $input['id'] > 0)
        {
            $inspectionListCategory = InspectionListCategory::whereHas('inspectionList', function($query) use ($project){
                $query->where('project_id', '=', $project->id);
            })
            ->where('id', '=', $input['id'])
            ->first();

            $records = InspectionListCategory::where("inspection_list_id", '=', $inspectionListCategory->inspection_list_id)
                ->where("lft", ">", $inspectionListCategory->lft)
                ->where("rgt", "<", $inspectionListCategory->rgt)
                ->where("depth", $inspectionListCategory->depth + 1)
                ->whereNotIn('id', $usedListIds)
                ->orderBy("lft", "asc")
                ->orderBy("priority", "asc")
                ->get();
        }
        else
        {
            $groupIds = InspectionGroupUser::where('user_id', '=', $user->id)
                ->whereHas('role', function($query){
                    $query->where('can_request_inspection', '=', true);
                })
                ->lists('inspection_group_id');

            $assignedRootListIds = InspectionGroupInspectionListCategory::whereIn('inspection_group_id', $groupIds)->lists('inspection_list_category_id');

            $records = InspectionListCategory::whereHas('inspectionList', function($query) use ($project){
                $query->where('project_id', '=', $project->id);
            })
            ->where('depth', '=', 0)
            ->whereIn('id', $assignedRootListIds)
            ->whereNotIn('id', $usedListIds)
            ->get();
        }

        $data = [];

        foreach($records as $record)
        {
            $data[] = array(
                'id'         => $record->id,
                'name'       => $record->name,
                'selectable' => ($record->type == InspectionListCategory::TYPE_INSPECTION_LIST),
            );
        }

        return $data;
    }

    public function listCategoryFormDetails(Project $project)
    {
       $listCategoryId = Input::get('list_category_id');

        $listCategory = InspectionListCategory::whereHas('inspectionList', function($query) use ($project){
               $query->where('project_id', '=', $project->id);
            })
            ->where('id', '=', $listCategoryId)
            ->get();

        $additionalFields = InspectionListCategoryAdditionalField::where('inspection_list_category_id', '=', $listCategoryId)
            ->orderBy('priority')
            ->get();

        $listItems = InspectionListItem::where('inspection_list_category_id', '=', $listCategoryId)
            ->orderBy('lft')
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

        $listItemsData = array();

        foreach($listItems as $listItem)
        {
            $listItemsData[] = array(
                'id'          => $listItem->id,
                'description' => $listItem->description,
                'depth'       => $listItem->depth,
                'type'        => $listItem->type,
            );
        }

        return array(
            'additionalFields' => $additionalFieldsData,
            'listItems'        => $listItemsData,
        );
    }
}