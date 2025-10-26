<?php namespace PCK\Inspections;

use PCK\Projects\Project;

class InspectionListRepository
{
    public function createIfNotExists(Project $project)
    {
        if($project->inspectionLists->count() == 0)
        {
            $inspectionList             = new InspectionList();
            $inspectionList->project_id = $project->id;
            $inspectionList->name       = '';
            $inspectionList->priority   = InspectionList::getNextFreePriority($project->id);
            $inspectionList->save();
        }
    }

    public function getInspectionLists(Project $project = null)
    {
        return $project ? $project->inspectionLists : InspectionList::whereNull('project_id')->orderBy('priority', 'ASC')->get();
    }

    public function create($inputs)
    {
        $projectId = isset($inputs['projectId']) ? $inputs['projectId'] : null;

        $inspectionList                     = new InspectionList();
        $inspectionList->project_id         = $projectId;
        $inspectionList->{$inputs['field']} = $inputs['val'];
        $inspectionList->priority           = InspectionList::getNextFreePriority($projectId);
        $inspectionList->save();

        return InspectionList::find($inspectionList->id);
    }

    public function update($inspectionListId, $inputs)
    {
        InspectionList::where('id', $inspectionListId)->update([$inputs['field'] => $inputs['val']]);

        return InspectionList::find($inspectionListId);
    }

    public function destroy($inspectionListId)
    {
        $inspectionList = InspectionList::find($inspectionListId);
        $inspectionList->delete();

        InspectionList::updatePriority($inspectionList);
    }
}

