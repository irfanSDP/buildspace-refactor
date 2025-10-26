<?php namespace PCK\Subsidiaries;

use Illuminate\Database\Eloquent\Collection;
use PCK\Forms\SubsidiaryForm;
use PCK\Projects\ProjectRepository;
use PCK\Projects\Project;
use PCK\Users\User;
use Illuminate\Support\Facades\DB;

class SubsidiaryRepository {

    private $form;
    private $projectRepository;

    public function __construct(SubsidiaryForm $form, ProjectRepository $projectRepository)
    {
        $this->form = $form;
        $this->projectRepository = $projectRepository;
    }

    public function getAllSubsidiaries($topLevel = false)
    {
        $query = Subsidiary::orderBy('id', 'desc');

        if( $topLevel ) $query->whereNull('parent_id');

        return $query->get();
    }

    public function getHierarchicalCollection($subsidiaryId = null)
    {
        if($subsidiaryId)
        {
            $topLevelParents = [Subsidiary::find($subsidiaryId)];
        }
        else
        {
            $topLevelParents = Subsidiary::orderBy('name')->whereNull('parent_id')->get();
        }

        $list = new Collection();

        foreach($topLevelParents as $topLevelParent)
        {
            $this->addToHierarchicalCollection($list, $topLevelParent);
        }

        return $list;
    }

    private function addToHierarchicalCollection(Collection $list, $subsidiary, $level = 0)
    {
        $subsidiary->level = $level;
        $list->add($subsidiary);

        $children = Subsidiary::where('parent_id', $subsidiary->id)->orderBy('name')->get();

        foreach($children as $child)
        {
            $this->addToHierarchicalCollection($list, $child, $level + 1);
        }
    }

    public function lists($companyId = null)
    {
        $queryBuilder = Subsidiary::orderBy('id', 'desc');

        if( ! is_null($companyId) ) $queryBuilder->where('company_id', '=', $companyId);

        return $queryBuilder->lists('name', 'id');
    }

    public function store($input)
    {
        $resource = new Subsidiary();

        $resource->name       = $input['name'];
        $resource->identifier = $input['identifier'];
        $resource->parent_id  = $input['parent_id'];
        $resource->company_id = $input['company_id'];

        return $resource->save();
    }

    public function update($input)
    {
        $resource = Subsidiary::find($input['id']);

        $resource->name       = $input['name'];
        $resource->identifier = $input['identifier'];
        $resource->parent_id  = $input['parent_id'];

        return $resource->save();
    }

    public function isBeingUsedInBuildspaceProjects($id)
    {
        $count = Project::select('id')
                ->where('subsidiary_id', '=', $id)
                ->whereNull('deleted_at')
                ->count();

        return ($count);
    }

    public function isBeingUsedInBuildspaceProjectCodeSettings($id)
    {
        $projectCodeSettingsRecord = DB::connection('buildspace')
                ->table('bs_project_code_settings')
                ->select('id')
                ->where('eproject_subsidiary_id', $id)
                ->whereNull('deleted_at')
                ->first();

        return !is_null($projectCodeSettingsRecord);
    }

    public function delete($id)
    {
        return Subsidiary::find($id)->delete();
    }

    public function getRelevantSubsidiaries(User $user)
    {
        $relevantSubsidiaryIds = $this->projectRepository->getSubsidiaries($user)->lists('id');

        return $this->getHierarchicalCollection()->filter(function($subsidiary) use ($relevantSubsidiaryIds)
        {
            return in_array($subsidiary->id, $relevantSubsidiaryIds);
        });
    }
}