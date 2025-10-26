<?php

use Illuminate\Support\Facades\DB;
use PCK\Users\User;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\Forms\AssignCompaniesForm;
use PCK\Forms\RoleNamesForm;
use PCK\Projects\Project;
use PCK\Users\UserRepository;
use PCK\Projects\ProjectRepository;
use PCK\Companies\CompanyRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\ContractGroupRepository;
use PCK\AssignCompaniesLogs\AssignCompaniesLogRepository;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\ProjectRole\ProjectRole;

class ProjectCompaniesController extends \BaseController {

    private $projectRepo;
    private $companyRepository;
    private $contractGroupRepo;
    private $userRepo;
    private $assignCompaniesLogRepo;
    private $assignCompaniesForm;
    private $roleNamesForm;

    public function __construct(
        ProjectRepository $projectRepository,
        ContractGroupRepository $contractGroupRepository,
        CompanyRepository $companyRepository,
        UserRepository $userRepo,
        AssignCompaniesForm $assignCompaniesForm,
        AssignCompaniesLogRepository $assignCompaniesLogRepo,
        RoleNamesForm $roleNamesForm
    )
    {
        $this->projectRepo            = $projectRepository;
        $this->contractGroupRepo      = $contractGroupRepository;
        $this->companyRepository      = $companyRepository;
        $this->userRepo               = $userRepo;
        $this->assignCompaniesLogRepo = $assignCompaniesLogRepo;
        $this->assignCompaniesForm    = $assignCompaniesForm;
        $this->roleNamesForm          = $roleNamesForm;
    }

    /**
     * Show the form for creating a new resource.
     * Assign Companies to Project.
     *
     * @param $project
     *
     * @return Response
     */
    public function create($project)
    {
        $user   = \Confide::user();
        $groups = $this->contractGroupRepo->getGroupsByContractId($project, array( Role::CONTRACTOR ));

        $selectedCompanies = [];   

        foreach($project->selectedCompanies as $company)
        {
            $projectRoleId = $company->pivot->contract_group_id;

            $selectedCompanies[$projectRoleId]['id']   = $company->id;
            $selectedCompanies[$projectRoleId]['name'] = $company->name;
        }

        return View::make('project_companies.index', compact(
            'project',
            'groups',
            'selectedCompanies',
            'user'
        ));
    }

    public function getAssignableCompanies(Project $project, $contractGroupId)
    {
        $contractGroup = ContractGroup::find($contractGroupId);

        $companies = [];
        
        foreach($this->companyRepository->getByRoles([$contractGroup->group]) as $company)
        {
            array_push($companies, [
                'id'   => $company->id,
                'name' => $company->name,
            ]);
        }

        return Response::json($companies);
    }

    public function getSelectedCompaniesUsers(Project $project)
    {
        $user    = \Confide::user();
        $company = $user->getAssignedCompany($project);

        $excludedContractGroupIds = [];

        $data = [];

        // BU will see GCD's viewers, and also imported viewers
        if($company->hasProjectRole($project, Role::PROJECT_OWNER))
        {
            $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))->first();

            if($gcdCompany)
            {
                $gcdViewersQuery = "SELECT gcd_viewers.name, gcd_viewers.email FROM (
                                        SELECT u.company_id, u.id, u.name, u.email
                                        FROM users u
                                        WHERE u.company_id = {$gcdCompany->id}
                                        AND u.confirmed IS TRUE
                                        AND u.account_blocked_status IS FALSE
                                        AND u.id IN (
                                            SELECT cgpu.user_id
                                            FROM contract_group_project_users cgpu 
                                            WHERE cgpu.project_id = {$project->id}
                                            ORDER BY cgpu.user_id ASC
                                        )
                                        UNION
                                        SELECT ciu.company_id, u.id, u.name, u.email 
                                        FROM company_imported_users ciu
                                        INNER JOIN users u ON u.id = ciu.user_id 
                                        WHERE ciu.company_id = {$gcdCompany->id}
                                        AND u.confirmed IS TRUE
                                        AND u.account_blocked_status IS FALSE
                                        AND u.id IN (
                                            SELECT cgpu.user_id
                                            FROM contract_group_project_users cgpu 
                                            WHERE cgpu.project_id = {$project->id}
                                            ORDER BY cgpu.user_id ASC
                                        )
                                    ) gcd_viewers
                                    ORDER BY gcd_viewers.id ASC;";

                $gcdViewers = DB::select(DB::raw($gcdViewersQuery));

                foreach($gcdViewers as $gcdViewer)
                {
                    array_push($data, [
                        'contract_group' => ProjectRole::getRoleName($project, Role::GROUP_CONTRACT),
                        'company'        => $gcdCompany->name,
                        'user'           => $gcdViewer->name,
                        'email'          => $gcdViewer->email,
                    ]);
                }
            }
        }

        // exclude BU, GCD, and Contractors
        $excludedContractGroupIds = implode(', ', [ContractGroup::getIdByGroup(Role::PROJECT_OWNER), ContractGroup::getIdByGroup(Role::GROUP_CONTRACT), ContractGroup::getIdByGroup(Role::CONTRACTOR)]);

        $companyIds = array_column(DB::select(DB::raw("SELECT company_id FROM company_project WHERE project_id = {$project->id} AND contract_group_id NOT IN ({$excludedContractGroupIds}) ORDER BY company_id ASC;")), 'company_id');

        if(count($companyIds) > 0)
        {
            $query = "SELECT pr.name AS contract_group, c.name AS company, u.name AS user, u.email
                      FROM users u
                      INNER JOIN companies c ON c.id = u.company_id
                      INNER JOIN company_project cp ON cp.company_id = u.company_id AND cp.project_id = {$project->id}
                      INNER JOIN project_roles pr ON pr.project_id = {$project->id} AND pr.contract_group_id = cp.contract_group_id 
                      WHERE u.company_id IN (" . implode(', ', $companyIds) . ")
                      AND u.confirmed IS TRUE
                      AND u.account_blocked_status IS FALSE
                      AND u.id IN (
                          SELECT cgpu.user_id
                          FROM contract_group_project_users cgpu 
                          WHERE cgpu.project_id = {$project->id}
                          ORDER BY cgpu.user_id ASC
                      )
                      ORDER BY u.company_id ASC, u.id ASC;";

            $otherCompanyUsers = DB::select(DB::raw($query));

            $data = array_merge($data, $otherCompanyUsers);
        }

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     * Assign Companies to Project.
     *
     * @param Project $project
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Project $project)
    {
        $user   = \Confide::user();
        $inputs = Input::all();

        // Remove unassigned groups.
        $inputs['group_id'] = array_diff($inputs['group_id'], [0]);

        try
        {
            $this->assignCompaniesForm->setParameters($project);
            $this->assignCompaniesForm->validate($inputs);
            $this->roleNamesForm->validate($inputs);
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            Flash::error($e->getErrors()->first());

            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        $previouslySelectedCompanies = $project->selectedCompanies;

        $tenderDocumentGroup = $this->projectRepo->syncTenderDocumentPermission($project, $inputs);

        $allCompanyIds = $this->projectRepo->attachSelectedCompanies($project, $inputs['group_id']);

        $project->load('selectedCompanies');
        $project->load('contractGroupTenderDocumentPermission');

        $newlyAssignedCompanies  = $project->selectedCompanies->diff($previouslySelectedCompanies);
        $newlyAssignedCompanyIds = $newlyAssignedCompanies->lists('id');

        $noLongerAssignedCompanies  = $previouslySelectedCompanies->diff($project->selectedCompanies);
        $noLongerAssignedCompanyIds = $noLongerAssignedCompanies->lists('id');

        $companyIds = array();

        // will be removing current user BU's company ID
        foreach($allCompanyIds as $companyKey => $companyId)
        {
            if( $companyId !== $user->company_id )
            {
                $companyIds[ $companyKey ] = $companyId;
            }
        }

        $this->assignCompaniesLogRepo->saveLog($project, $user, $tenderDocumentGroup, $companyIds);

        foreach($inputs['group_names'] as $group => $name)
        {
            \PCK\ProjectRole\ProjectRole::setRoleName($project, $group, $name);
        }

        $buCompany             = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();
        $gcdCompany            = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))->first();
        $tenderDocumentCompany = $project->selectedCompanies()->where('contract_group_id', '=', $project->contractGroupTenderDocumentPermission->contract_group_id)->first();

        if(in_array($project->getBsProjectMainInformation()->status, [
            BsProjectUserPermission::STATUS_PROJECT_BUILDER,
            BsProjectUserPermission::STATUS_TENDERING,
            BsProjectUserPermission::STATUS_POST_CONTRACT
        ]))
        {
            $this->projectRepo->revokeBsProjectAccessToCompanyUsers($project, $noLongerAssignedCompanyIds, $project->getBsProjectMainInformation()->status);
            $this->projectRepo->grantBsProjectAccessToCompanyUsers($project, $newlyAssignedCompanyIds, $project->getBsProjectMainInformation()->status);
        }

        if(($project->getBsProjectMainInformation()->status == BsProjectUserPermission::STATUS_TENDERING))
        {
            $isCurrentUserBuOrGcd  = $user->hasCompanyProjectRole($project, [Role::PROJECT_OWNER, Role::GROUP_CONTRACT]);

            if($isCurrentUserBuOrGcd)
            {
                $buHasTenderDocumentAccess = is_null($tenderDocumentCompany) ? false : ($buCompany->id === $tenderDocumentCompany->id);
                $includeBuCompany          = ($buHasTenderDocumentAccess || is_null($gcdCompany));  // TRUE if BU has tender document access or BU has no GCD assigned

                if($includeBuCompany)
                {
                    // include only those assigned as viewers/editors in Project Users page
                    $buContractGroup = $buCompany->getContractGroup($project);

                    $contractGroupProjectUserRecords = ContractGroupProjectUser::where('project_id', '=', $project->id)
                        ->where('contract_group_id', $buContractGroup->id)
                        ->get();
                    
                    foreach($contractGroupProjectUserRecords->lists('user_id') as $userId)
                    {
                        $user = User::find($userId);

                        $project->grantBsProjectPermissionToUser($user, $project->getBsProjectMainInformation()->status);
                    }
                }
                else
                {
                    $this->projectRepo->revokeBsProjectAccessToCompanyUsers($project, [$buCompany->id], $project->getBsProjectMainInformation()->status);
                }
            }
        }

        $companyAdminUsers = $this->userRepo->getAdminUserByCompanyIds($newlyAssignedCompanyIds);

        $this->projectRepo->sendSelectedCompanyAdminUserNotification($project, $companyAdminUsers->toArray(),
            'selected_company_to_project_inform_company_admin', 'projects.show');

        Flash::success('Successfully Updated Project Companies Permission!');

        return Redirect::route('projects.show', array( $project->id ));
    }

}