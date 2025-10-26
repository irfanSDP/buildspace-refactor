<?php

use PCK\Companies\CompanyRepository;
use PCK\ContractGroupCategory\ContractGroupCategoryPrivilege;
use PCK\ContractGroupCategory\ContractGroupCategoryRepository;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\ContractGroupRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\ContractGroupCategoryForm;
use PCK\Forms\RoleNamesForm;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Exceptions\ValidationException;

class ContractGroupCategoriesController extends \BaseController {

    private $repository;
    private $form;
    private $companyRepository;
    private $contractGroupRepository;
    private $roleNamesForm;

    public function __construct(
        ContractGroupCategoryRepository $repository,
        ContractGroupCategoryForm $form,
        CompanyRepository $companyRepository,
        ContractGroupRepository $contractGroupRepository,
        RoleNamesForm $roleNamesForm
    )
    {
        $this->repository              = $repository;
        $this->form                    = $form;
        $this->companyRepository       = $companyRepository;
        $this->contractGroupRepository = $contractGroupRepository;
        $this->roleNamesForm           = $roleNamesForm;
    }

    /**
     * Returns the view to match the Contract Group Categories to the Contract Groups.
     *
     * @return \Illuminate\View\View
     */
    public function match()
    {
        $excludedGroups = array(
            Role::PROJECT_OWNER,
            Role::GROUP_CONTRACT,
            Role::PROJECT_MANAGER,
        );

        return View::make('contract_group_categories.match', array(
            'groups'         => $this->contractGroupRepository->all(),
            'excludedGroups' => $excludedGroups,
            'categories'     => $this->repository->getPublicCategories(),
        ));
    }

    /**
     * Matches the Contract Group Categories to the Contract Groups.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function matchUpdate()
    {
        try
        {
            $this->roleNamesForm->validate(Input::all());
        }
        catch(ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput(Input::all());
        }

        foreach(Input::get('group_names') as $group => $name)
        {
            ContractGroup::findByGroup($group)->update(array( 'name' => $name ));
        }

        $this->repository->match(Input::get('group_id'));

        Flash::success(trans('contractGroupCategories.matchSuccess'));

        return Redirect::back();
    }

    /**
     * Toggles the default permission for access to BuildSpace.
     *
     * @return array
     */
    public function toggleDefaultBuildSpaceAccess()
    {
        $input = Input::all();

        $success = $this->repository->toggleDefaultBuildSpaceAccess($input['id']);

        return array( 'success' => $success );
    }

    /**
     * Returns the view to set the privileges of the contract groups.
     *
     * @return \Illuminate\View\View
     */
    public function privilegesPage()
    {
        $privilegeIdentifiers = array(
            'systemOverview' => ContractGroupCategoryPrivilege::DASHBOARD_SYSTEM_OVERVIEW,
            'designStage'    => ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_DESIGN_STAGE,
            'tenderingStage' => ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_TENDERING,
            'postContract'   => ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_POST_CONTRACT,
        );

        return View::make('contract_group_categories.privileges', array(
            'categories'           => $this->repository->all(),
            'privilegeIdentifiers' => $privilegeIdentifiers,
        ));
    }

    /**
     * Toggles the privileges of the contract groups.
     *
     * @return array
     */
    public function togglePrivileges()
    {
        $contractGroupCategoryId = Input::get('contract_group_category_id');
        $privilegeIdentifier     = Input::get('privilege_identifier');
        $contractGroupCategory   = $this->repository->find($contractGroupCategoryId);

        $permit  = ! $contractGroupCategory->hasPrivilege($privilegeIdentifier);
        $success = $contractGroupCategory->setPrivilege($privilegeIdentifier, $permit);

        return array( 'success' => $success );
    }

    public function getVendorCategoriesByVendorGroup()
    {
        $inputs = Input::all();

        $contractGroupCategoryId = $inputs['vendorGroupId'];
        $contractGroupCategory   = ContractGroupCategory::find($contractGroupCategoryId);

        $data = [];

        foreach($contractGroupCategory->vendorCategories()->where('hidden', false)->orderBy('id', 'ASC')->get() as $vendorCategory) {
            array_push($data, [
                'id'   => $vendorCategory->id,
                'name' => $vendorCategory->name,
            ]);
        }

        return Response::json($data);
    }
}