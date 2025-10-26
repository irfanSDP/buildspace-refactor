<?php namespace PCK\ContractGroupCategory;

use PCK\ContractGroups\ContractGroupRepository;
use PCK\ContractGroups\Types\Role;

class ContractGroupCategoryRepository {

    private $contractGroupRepository;

    public function __construct(ContractGroupRepository $contractGroupRepository)
    {
        $this->contractGroupRepository = $contractGroupRepository;
    }

    /**
     * Finds an instance by id.
     *
     * @param $id
     *
     * @return ContractGroupCategory
     */
    public function find($id)
    {
        return ContractGroupCategory::find($id);
    }

    public function all()
    {
        return ContractGroupCategory::orderBy('editable', 'desc')->where('hidden', '=', false)->orderBy('id', 'desc')->get();
    }

    public function lists()
    {
        return ContractGroupCategory::orderBy('id', 'asc')->lists('name', 'id');
    }

    public function getPublicCategories()
    {
        return ContractGroupCategory::whereNotIn('name', ContractGroupCategory::getPrivateGroupNames())->orderBy('id', 'desc')->get();
    }

    /**
     * Saves a new Contract Group Category.
     *
     * @param $input
     *
     * @return bool
     */
    public function store($input)
    {
        $resource       = new ContractGroupCategory();
        $resource->name = $input['name'];

        return $resource->save();
    }

    /**
     * Updates an existing Contract Group Category.
     *
     * @param $input
     *
     * @return bool
     */
    public function update($input)
    {
        $resource = ContractGroupCategory::find($input['id']);

        if( ! $resource->editable )
        {
            return false;
        }
        $resource->name = $input['name'];

        return $resource->save();
    }

    /**
     * Deletes a Contract Group Category.
     *
     * @param $id
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id)
    {
        $resource = ContractGroupCategory::find($id);

        if( ! $resource->editable )
        {
            return false;
        }

        return ContractGroupCategory::find($id)->delete();
    }

    /**
     * Matches (links) the Contract Group Categories to Contract Groups.
     * This determines the Contract Groups (roles) for the companies under each Contract Group Category.
     *
     * @param $input
     */
    public function match($input)
    {
        $excludedGroups = array(
            Role::PROJECT_OWNER,
            Role::GROUP_CONTRACT,
            Role::PROJECT_MANAGER,
        );

        // The input only caters to fields that have been filled.
        // Empty fields are ignored (excluded from the form inputs)
        // so we manually remove existing relations before that.
        foreach($this->getPublicCategories() as $contractGroupCategory)
        {
            $contractGroupCategory->contractGroups()->sync(array());
        }

        foreach($input as $contractGroupId => $contractGroupCategoryIds)
        {
            // We don't process the excluded groups.
            if( in_array($contractGroupId, $excludedGroups) ) continue;

            $contractGroup = $this->contractGroupRepository->findById($contractGroupId);
            $contractGroup->contractGroupCategories()->sync($contractGroupCategoryIds);
        }
    }

    /**
     * Sets the default permission for BuildSpace access
     * for a Contract Group Category.
     *
     * @param      $id
     * @param bool $defaultAccess
     *
     * @return bool
     */
    public function setDefaultBuildSpaceAccess($id, $defaultAccess = true)
    {
        $category = $this->find($id);

        $category->default_buildspace_access = $defaultAccess;

        return $category->save();
    }

    /**
     * Toggles the state of the default permission for BuildSpace access.
     *
     * @param $id
     *
     * @return bool
     */
    public function toggleDefaultBuildSpaceAccess($id)
    {
        $access = $this->find($id)->default_buildspace_access ? false : true;

        return $this->setDefaultBuildSpaceAccess($id, $access);
    }

}