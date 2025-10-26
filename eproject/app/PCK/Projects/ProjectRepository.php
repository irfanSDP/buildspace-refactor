<?php namespace PCK\Projects;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Support\MessageBag;
use PCK\Notifications\EmailNotifier;
use PCK\Buildspace\WorkCategory as BsWorkCategory;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Companies\Company;
use PCK\CompanyProject\CompanyProject;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission;
use PCK\ContractManagementModule\ProjectContractManagementModule;
use PCK\Contracts\Contract;
use PCK\ProjectDetails\IndonesiaCivilContractInformation;
use PCK\Subsidiaries\Subsidiary;
use PCK\Users\User;
use PCK\Helpers\DataTables;
use PCK\Users\UserRepository;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use Illuminate\Support\Collection;
use PCK\ContractGroups\Types\Role;
use PCK\ProjectDetails\PAM2006ProjectDetail;
use PCK\ContractGroups\ContractGroupRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\ProjectContractGroupTenderDocumentPermissions\ProjectContractGroupTenderDocumentPermission;
use PCK\Verifier\Verifier;
use PCK\WorkCategories\WorkCategory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PCK\Subsidiaries\SubsidiaryRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PCK\Helpers\SpreadsheetHelper;
use PCK\LetterOfAward\LetterOfAwardRepository;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Tenders\Tender;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PCK\Tenders\SubmitTenderRate;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;

class ProjectRepository extends BaseModuleRepository {

    private $project;

    private $contractGroupProjectUser;

    private $contractGroupRepo;

    private $userRepo;
    private $leterOfAwardRepository;
    private $formOfTenderRepository;

    protected $events;
    private   $contractGroupProjectUserRepository;

    protected $emailNotifier;

    public function __construct(
        Project $project,
        ContractGroupProjectUser $contractGroupProjectUser,
        ContractGroupRepository $contractGroupRepo,
        UserRepository $userRepo,
        Dispatcher $events,
        ContractGroupProjectUserRepository $contractGroupProjectUserRepository,
        LetterOfAwardRepository $letterOfAwardRepository,
        FormOfTenderRepository $formOfTenderRepository,
        EmailNotifier $emailNotifier
    )
    {
        $this->project                            = $project;
        $this->contractGroupProjectUser           = $contractGroupProjectUser;
        $this->contractGroupRepo                  = $contractGroupRepo;
        $this->userRepo                           = $userRepo;
        $this->events                             = $events;
        $this->contractGroupProjectUserRepository = $contractGroupProjectUserRepository;
        $this->letterOfAwardRepository            = $letterOfAwardRepository;
        $this->formOfTenderRepository             = $formOfTenderRepository;
        $this->emailNotifier                      = $emailNotifier;
    }

    public function find($id)
    {
        return $this->project->with(
            'country', 'state', 'pam2006Detail', 'indonesiaCivilContractInformation'
        )->findOrFail($id);
    }

    public function findWithSelectedCompanies($id)
    {
        return $this->project->findOrFail($id);
    }

    public function getVisibleOwnedProjectIds(User $user)
    {
        $query = \DB::table("{$this->project->getTable()} as p")->select(\DB::raw('p.id'))->whereNull('p.deleted_at');

        if( $user->isSuperAdmin() )
        {
            return $query->lists('id');
        }

        $query->join('company_project as cp', 'p.id', '=', 'cp.project_id')
            ->where(function($query) use ($user)
            {
                $query->where('cp.company_id', '=', $user->company->id)
                    ->orWhereIn('cp.company_id', $user->getFosterCompanyIds());
            });

        if( ! $user->isGroupAdmin() )
        {
            $query->join("{$this->contractGroupProjectUser->getTable()} as cgpu", 'p.id', '=', 'cgpu.project_id')
                ->where('cgpu.user_id', '=', $user->id);
        }

        return $query->lists('id');
    }

    public function getVisibleTenderingProjectIds(User $user)
    {
        if( ! $user->isGroupAdmin() )
        {
            return array();
        }

        $query = \DB::table("{$this->project->getTable()} AS p")
            ->select(\DB::raw('p.id'))
            ->whereNull('p.deleted_at')
            ->join('tenders AS t', 'p.id', '=', 't.project_id')
            ->join('tender_calling_tender_information AS tcti', 't.id', '=', 'tcti.tender_id')
            ->join('company_tender_calling_tender_information AS ctcti', 'tcti.id', '=', 'ctcti.tender_calling_tender_information_id')
            ->join('company_tender AS ct', function($join){
                $join->on('tcti.tender_id', '=', 'ct.tender_id');
                $join->on('ctcti.company_id','=', 'ct.company_id');
            })
            ->where('t.tender_starting_date', '<=', date("Y-m-d H:i:s"))
            ->where('t.tender_closing_date', '>', date("Y-m-d H:i:s"))
            ->where('ct.company_id', '=', $user->company_id)
            ->where('ct.can_login', '=', true)
            ->where('p.contractor_access_enabled', '=', true)
            ->where('ctcti.status', '=', \PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::TENDER_OK)
            ->groupBy('p.id');

        return $query->lists('id');
    }

    public function getVisibleProjectIds(User $user)
    {
        $ownedProjects     = $this->getVisibleOwnedProjectIds($user);
        $tenderingProjects = $this->getVisibleTenderingProjectIds($user);

        return array_merge($ownedProjects, $tenderingProjects);
    }

    public function sortProjects($inputs, $projects)
    {
        $customOrdering = array(
            1 => function($projectA, $projectB)
            {
                return strcmp($projectA->reference, $projectB->reference);
            },
            2 => function($projectA, $projectB)
            {
                return strcmp($projectA->title, $projectB->title);
            },
            3 => function($projectA, $projectB)
            {
                return strcmp(Project::getStatusById($projectA->status_id),
                    Project::getStatusById($projectB->status_id));
            }
        );
        return DataTables::arrayOrder($inputs, $projects, $customOrdering);
    }

    private function getAllSubSubsidiariesIds($subsidiary)
    {
        $subsidiaryIds = [];

        foreach($subsidiary->children as $child)
        {
            array_push($subsidiaryIds, $child->id);

            if( ! $child->children->isEmpty() )
            {
                array_push($subsidiaryIds, $this->getAllSubSubsidiariesIds($child));
            }
        }

        return $subsidiaryIds;
    }

    /**
     * Filters results based on user input.
     *
     * @param $query
     * @param $inputs
     *
     * @return mixed
     */
    public function projectFilteringQueryFilterStatement($query, $inputs)
    {
        if( $inputs['subsidiaryId'] !== '' )
        {
            $subsidiaryId = trim(isset( $inputs['subsidiaryId'] ) ? $inputs['subsidiaryId'] : null);

            $subsidiary = Subsidiary::find($subsidiaryId);

            $allChildSubsidiaryIds = array_flatten($this->getAllSubSubsidiariesIds($subsidiary));
            array_unshift($allChildSubsidiaryIds, (int)$subsidiaryId); //insert self's id into the search

            $query->where(function($query) use ($allChildSubsidiaryIds)
            {
                $query->whereIn('subsidiaries.id', $allChildSubsidiaryIds);
                $query->orWhereNull('subsidiaries.id');
            });
        }

        $includeProjects    = isset( $inputs['includeProjects'] ) ? ( $inputs['includeProjects'] == 'true' ) : false;
        $includeSubProjects = isset( $inputs['includeSubProjects'] ) ? ( $inputs['includeSubProjects'] == 'true' ) : false;

        if( $includeProjects || $includeSubProjects )
        {
            $query->where(function($query) use ($includeProjects, $includeSubProjects)
            {
                if( $includeProjects ) $query->orWhereNull('p.parent_project_id');
                if( $includeSubProjects ) $query->orWhereNotNull('p.parent_project_id');
            });
        }
        else
        {
            $query->where('p.id', '=', 0);
        }

        $query->where(function($newQuery) use ($inputs)
        {
            $searchStringGlobal        = trim(isset( $inputs['sSearch'] ) ? $inputs['sSearch'] : '');
            $searchStringGlobalPattern = '%' . $searchStringGlobal . '%';

            $newQuery->where('p.title', 'ILIKE', $searchStringGlobalPattern);
            $newQuery->orWhere('p.reference', 'ILIKE', $searchStringGlobalPattern);
            $newQuery->orWhere(function($query) use ($searchStringGlobalPattern)
            {
                $query->orWhere('subsidiaries.name', 'ILIKE', $searchStringGlobalPattern);
                $query->orWhereNull('subsidiaries.id');
            });

            $projectStatusTypeIdAndName = $this->getAllProjectStatusTypeIdAndName();
            DataTables::genericCustomGlobalFilteringFunction($newQuery, $searchStringGlobal,
                $projectStatusTypeIdAndName, 'p.status_id');
        });

        return $query;
    }

    public function getAllProjectStatusTypeIdAndName()
    {
        $projectStatusTypeIdAndName = array();

        foreach(Project::getStagesSequence() as $statusId)
        {
            $projectStatusTypeIdAndName[ $statusId ] = Project::getStatusById($statusId);
        }

        return $projectStatusTypeIdAndName;
    }

    public function getProjectByUserPermission(User $user, Project $project)
    {
        $query = $this->createProjectFilteringQuery($user);

        $normalProject = $query->where('p.id', '=', $project->id)->distinct()->first(array( 'p.id' ));

        $query = $this->createProjectFilteringQuery($user, true);

        $tenderingProject = $query->where('p.id', '=', $project->id)->distinct()->first(array( 'p.id' ));

        // Check if contractor access is disabled
        if( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) && ( ! $project->contractor_access_enabled ) )
        {
            throw new ModelNotFoundException;
        }

        // any which one that has properties available then allow them to access
        if( $normalProject OR $tenderingProject )
        {
            return true;
        }

        throw new ModelNotFoundException;
    }

    /**
     * Determines if the user is allowed to view the tender information.
     * ( by checking whether the user still has the project assigned to them )
     * ( Modified from getProjectByUserPermission() )
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function getSubmitTenderByUserPermission(User $user, Project $project)
    {
        $query = $this->createProjectFilteringQuery($user);

        $normalProject = $query->where('p.id', '=', $project->id)->distinct()->first(array( 'p.id' ));

        $query = $this->createProjectFilteringQuery($user, true);

        $tenderingProject = $query->where('p.id', '=', $project->id)->distinct()->first(array( 'p.id' ));

        // any which one that has properties available then allow them to access
        return $normalProject || $tenderingProject;
    }

    public function add(User $user, array $inputs)
    {
        $project = $this->project->newInstance(array(
            'business_unit_id' => $user->company->id,
            'contract_id'      => $inputs['contract_id'],
            'title'            => trim($inputs['title']),
            'reference'        => $inputs['reference'],
            'reference_suffix' => $inputs['reference_suffix'],
            'address'          => trim($inputs['address']),
            'country_id'       => $inputs['country_id'],
            'state_id'         => $inputs['state_id'],
            'work_category_id' => $inputs['work_category_id'],
            'description'      => trim($inputs['description']),
            'running_number'   => $inputs['running_number'],
            'subsidiary_id'    => $inputs['subsidiary_id'],
            'status_id'        => Project::getDefaultStatusId(),
            'open_tender'      => $inputs["open_tender"],
            'created_by'       => $user->id,
            'updated_by'       => $user->id,
        ));

        $project = $this->save($project);

        // will sync for the first time assigning BU with project permission
        $project->selectedCompanies()->sync(array(
            $user->company->id => array(
                'contract_group_id' => $user->company->contractGroupCategory->contractGroups->first()->id
            )
        ));

        return $project;
    }

    public function savePostContractInformation(Project $project, array $inputs)
    {
        switch($project->contract->type)
        {
            case Contract::TYPE_PAM2006:
                return $this->attachPAM2006ProjectDetails($project, $inputs);
            case Contract::TYPE_INDONESIA_CIVIL_CONTRACT:
                return $this->saveIndonesiaCivilContractInformation($project, $inputs);
            default:
                throw new \Exception('Invalid contract type');
        }
    }

    private function attachPAM2006ProjectDetails(Project $project, array $inputs)
    {
        $projectDetail = new PAM2006ProjectDetail([
            'commencement_date'                                               => date('Y-m-d', strtotime($inputs['commencement_date'])),
            'completion_date'                                                 => date('Y-m-d', strtotime($inputs['completion_date'])),
            'contract_sum'                                                    => empty( $inputs['contract_sum'] ) ? null : $inputs['contract_sum'],
            'liquidate_damages'                                               => empty( $inputs['liquidate_damages'] ) ? null : $inputs['liquidate_damages'],
            'amount_performance_bond'                                         => empty( $inputs['amount_performance_bond'] ) ? null : $inputs['amount_performance_bond'],
            'interim_claim_interval'                                          => $inputs['interim_claim_interval'] ?? 1,
            'period_of_honouring_certificate'                                 => $inputs['period_of_honouring_certificate'] ?? 21,
            'min_days_to_comply_with_ai'                                      => $inputs['min_days_to_comply_with_ai'] ?? 7,
            'deadline_submitting_notice_of_intention_claim_eot'               => $inputs['deadline_submitting_notice_of_intention_claim_eot'] ?? 28,
            'deadline_submitting_final_claim_eot'                             => $inputs['deadline_submitting_final_claim_eot'] ?? 28,
            'deadline_architect_request_info_from_contractor_eot_claim'       => $inputs['deadline_architect_request_info_from_contractor_eot_claim'] ?? 28,
            'deadline_architect_decide_on_contractor_eot_claim'               => $inputs['deadline_architect_decide_on_contractor_eot_claim'] ?? 6,
            'deadline_submitting_note_of_intention_claim_l_and_e'             => $inputs['deadline_submitting_note_of_intention_claim_l_and_e'] ?? 28,
            'deadline_submitting_final_claim_l_and_e'                         => $inputs['deadline_submitting_final_claim_l_and_e'] ?? 28,
            'deadline_submitting_note_of_intention_claim_ae'                  => $inputs['deadline_submitting_note_of_intention_claim_ae'] ?? 28,
            'deadline_submitting_final_claim_ae'                              => $inputs['deadline_submitting_final_claim_ae'] ?? 28,
            'percentage_of_certified_value_retained'                          => $inputs['percentage_of_certified_value_retained'] ?? 10,
            'limit_retention_fund'                                            => $inputs['limit_retention_fund'] ?? 5,
            'percentage_value_of_materials_and_goods_included_in_certificate' => $inputs['percentage_value_of_materials_and_goods_included_in_certificate'] ?? 100,
            'period_of_architect_issue_interim_certificate'                   => $inputs['period_of_architect_issue_interim_certificate'] ?? 21,
            'pre_defined_location_code_id'                                    => $inputs['trade'],
            'cpc_date'                                                        => (array_key_exists('cpc_date', $inputs) && !empty($inputs['cpc_date'])) ? date('Y-m-d', strtotime($inputs['cpc_date'])) : null,
            'extension_of_time_date'                                          => (array_key_exists('extension_of_time_date', $inputs) && !empty($inputs['extension_of_time_date'])) ? date('Y-m-d', strtotime($inputs['extension_of_time_date'])) : null,
            'certificate_of_making_good_defect_date'                          => (array_key_exists('certificate_of_making_good_defect_date', $inputs) && !empty($inputs['certificate_of_making_good_defect_date'])) ? date('Y-m-d', strtotime($inputs['certificate_of_making_good_defect_date'])) : null,
            'cnc_date'                                                        => (array_key_exists('cnc_date', $inputs) && !empty($inputs['cnc_date'])) ? date('Y-m-d', strtotime($inputs['cnc_date'])) : null,
            'performance_bond_validity_date'                                  => (array_key_exists('performance_bond_validity_date', $inputs) && !empty($inputs['performance_bond_validity_date'])) ? date('Y-m-d', strtotime($inputs['performance_bond_validity_date'])) : null,
            'insurance_policy_coverage_date'                                  => (array_key_exists('insurance_policy_coverage_date', $inputs) && !empty($inputs['insurance_policy_coverage_date'])) ? date('Y-m-d', strtotime($inputs['insurance_policy_coverage_date'])) : null,
            'defect_liability_period'                                         => (array_key_exists('defect_liability_period', $inputs) && strlen($inputs['defect_liability_period'])) ? (int)$inputs['defect_liability_period'] : 24,
            'defect_liability_period_unit'                                    => (array_key_exists('defect_liability_period_unit', $inputs)) ? (int)$inputs['defect_liability_period_unit'] : PAM2006ProjectDetail::DLP_PERIOD_UNIT_MONTH,
        ]);

        $project->pam2006Detail()->save($projectDetail);

        $project->status_id = Project::STATUS_TYPE_POST_CONTRACT;

        $project->save();

        return $project;
    }

    private function saveIndonesiaCivilContractInformation(Project $project, array $inputs)
    {
        $projectDetail = new IndonesiaCivilContractInformation(array(
            'commencement_date'            => date('Y-m-d', strtotime($inputs['commencement_date'])),
            'completion_date'              => date('Y-m-d', strtotime($inputs['completion_date'])),
            'contract_sum'                 => empty( $inputs['contract_sum'] ) ? null : $inputs['contract_sum'],
            'pre_defined_location_code_id' => $inputs['trade']
        ));

        $project->indonesiaCivilContractInformation()->save($projectDetail);

        $project->status_id = Project::STATUS_TYPE_POST_CONTRACT;
        $project->save();

        return $project;
    }

    /**
     * Updates the completion_date for projects table and pam2006Details table
     * also updates project status to 'completed'
     *
     * @param Project $project
     * @param         $inputs
     *
     * @return Project
     */
    public function updateCompletionDate(Project $project, $inputs)
    {
        $completion_date = date('Y-m-d', strtotime($inputs['completion_date']));

        if( $project->pam2006Detail )
        {
            $project->pam2006Detail->completion_date = $completion_date;

            $project->pam2006Detail->save();
        }

        if( $project->indonesiaCivilContractInformation )
        {
            $project->indonesiaCivilContractInformation->completion_date = $completion_date;

            $project->indonesiaCivilContractInformation->save();
        }

        $project->status_id = Project::STATUS_TYPE_COMPLETED;

        $project->completion_date = $completion_date;

        $project->save();

        return $project;
    }

    public function getLatestRunningNumber(Subsidiary $subsidiary, $referenceSuffix)
    {
        return \DB::table($this->project->getTable())
            ->where('subsidiary_id', '=', $subsidiary->id)
            ->where('reference_suffix', '=', $referenceSuffix)
            ->whereNull('deleted_at')
            ->max('running_number') + 1;
    }

    public function save(Project $project)
    {
        $project->save();

        return $project;
    }

    public function attachSelectedCompanies(Project $project, array $companyIds, $assignAllUsers = true)
    {
        $previouslySelectedCompanyIds = $project->selectedCompanies->lists('id');

        $companyIds = $this->addPreviouslyAssignedFinalContractor($project, $companyIds);

        //remove all previous associations
        $project->selectedCompanies()->sync(array());

        foreach($companyIds as $groupId => $companyId)
        {
            $project->selectedCompanies()->attach($companyId, array( 'contract_group_id' => $groupId ));

            // Automatically assign all users as editor if it is a newly registered company.
            if( $assignAllUsers && ( ! in_array($companyId, $previouslySelectedCompanyIds) ) ) $this->contractGroupProjectUserRepository->assignAllUsers($project, Company::find($companyId), true);
        }

        if(SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
        {
            $evaluations = VendorPerformanceEvaluation::where('project_id', '=', $project->id)
                ->where('status_id', '=', VendorPerformanceEvaluation::STATUS_DRAFT)
                ->get();

            foreach($evaluations as $evaluation)
            {
                $evaluation->syncVendorWorkCategorySetups();
            }
        }

        return $companyIds;
    }

    public function assignFinalContractor(Project $project, $companyId, bool $sendNotifications)
    {
        $company = Company::find($companyId);

        if(!$company)
        {
            return false;
        }

        $project->selectedCompanies()->attach($company->id, array(
            'contract_group_id' => ContractGroup::getIdByGroup(Role::CONTRACTOR)
        ));

        $this->contractGroupProjectUserRepository->assignAllUsers($project, $company, true);

        if( $sendNotifications )
        {
            // will send a email no notify the company's admin that their company has been selected
            $companyAdminUsers = $this->userRepo->getAdminUserByCompanyIds(array( $company->id ));

            $this->sendEmailNotificationByUsers($project, $project, $companyAdminUsers->toArray(),
                'selected_for_post_contract', 'projects.show');
        }
    }

    public function syncTenderDocumentPermission(Project $project, array $inputs)
    {
        $groupId = $inputs['role_access_to_tender_document'];

        $group = $this->contractGroupRepo->getGroupByGroupKeyAndContractId($groupId);

        $model = $project->contractGroupTenderDocumentPermission ?: new ProjectContractGroupTenderDocumentPermission();

        $model->contract_group_id = $group->id;

        $project->contractGroupTenderDocumentPermission()->save($model);

        return $model;
    }

    /**
     * @deprecated
     * Use visibleProjectIds() instead.
     */
    public function createProjectFilteringQuery(User $user, $getTenderingProject = false)
    {
        $projectTableName = $this->project->getTable();
        $query            = \DB::table("{$projectTableName} as p")->whereNull('p.deleted_at');

        // super admin will be seeing all the project regardless it has been assigned or not
        if( $user->isSuperAdmin() )
        {
            return $query;
        }

        // if current logged in user is company admin, then we will query assigned company's project(s)
        if( $user->isGroupAdmin() )
        {
            if( $getTenderingProject AND $user->hasCompanyRoles(array( Role::CONTRACTOR )) )
            {
                $query->join('tenders as t', 'p.id', '=', 't.project_id')
                    ->join('company_tender as ct', 't.id', '=', 'ct.tender_id')
                    ->where('t.tender_starting_date', '<=', date("Y-m-d H:i:s"))
                    ->where('t.tender_closing_date', '>', date("Y-m-d H:i:s"))
                    ->where('ct.company_id', '=', $user->company_id)
                    ->where('ct.can_login', '=', true);
            }
            else
            {
                $query->join('company_project as cp', 'p.id', '=', 'cp.project_id')
                    ->where(function($query) use ($user)
                    {
                        $query->where('cp.company_id', '=', $user->company->id)
                            // Include projects of foster companies.
                            ->orWhereIn('cp.company_id', $user->getFosterCompanyIds());
                    });
            }
        }
        // query for normal staff user, check which project(s) has been assigned
        else
        {
            $cgpuTableName = $this->contractGroupProjectUser->getTable();

            $query->join("{$cgpuTableName} as cgpu", 'p.id', '=', 'cgpu.project_id')
                ->join('company_project as cp', 'p.id', '=', 'cp.project_id')
                ->where(function($query) use ($user)
                {
                    $query->where('cp.company_id', '=', $user->company->id)
                        // Include projects of foster companies.
                        ->orWhereIn('cp.company_id', $user->getFosterCompanyIds());
                })
                ->where('cgpu.user_id', '=', $user->id);
        }

        if( $user->hasCompanyRoles(array( Role::CONTRACTOR )) ) $query->where('p.contractor_access_enabled', '=', true);

        return $query;
    }

    public function updateProjectStatus(Project $project, $status)
    {
        // will only update Project's Current Tender Status if the
        // current status is one of the type of Tender Status
        if( in_array($status, Project::tenderingStagesStatus()) )
        {
            $project->current_tender_status = $status;
        }

        $project->status_id = $status;

        if($status == Project::STATUS_TYPE_CALLING_TENDER)
        {
            $project->contractor_access_enabled = true;
            $project->contractor_contractual_claim_access_enabled = true;
        }

        $this->save($project);

        \Log::info("Updated Project status", ["Project id: {$project->id}", "status_id: {$project->status_id}", "current_tender_status: {$project->current_tender_status}"]);

        if($status == Project::STATUS_TYPE_CLOSED_TENDER)
        {
            $users       = [];
            $contractors       = $project->latestTender->selectedFinalContractors->lists('id');
			$companyAdminUsers = $this->userRepo->getAdminUserByCompanyIds($contractors);

            foreach($companyAdminUsers as $user)
            {
                array_push($users, User::find($user['id']));
            }

            $this->emailNotifier->sendTenderClosedNotification($project, $project->latestTender, $users);
        }
    }

    public function getWithCurrentTenderStatus($status)
    {
        return $this->project->with('latestTender')
            ->where('current_tender_status', '=', $status)
            ->get();
    }

    public function sendSelectedCompanyAdminUserNotification(Project $project, array $users, $viewName, $routeName)
    {
        $this->sendEmailNotificationByUsers($project, $project, $users, $viewName, $routeName);

        $this->sendSystemNotificationByUsers($project, $project, $users, $viewName, $routeName);
    }

    /**
     * Validates the uniqueness of the running number for a Business Unit.
     *
     * @param $inputs
     *
     * @return MessageBag
     */
    public function createRunningNumberUniquenessValidator($inputs)
    {
        $messageBag = new MessageBag();

        $exists = Project::where('running_number', '=', $inputs['running_number'])
            ->where('subsidiary_id', '=', $inputs['subsidiary_id'])
            ->where('reference_suffix', '=', $inputs['reference_suffix'])
            ->whereNull('deleted_at')
            ->exists();

        if( $exists )
        {
            $messageBag->add('running_number', 'This contract number is already in use.');
        }

        return $messageBag;
    }

    /**
     * Returns true if ... clears all conditions.
     *
     * @param User $user
     * @param      $project
     *
     * @return bool
     */
    public function isGroupAdminAndHasCompanyAndIsContractorAndProjectInTenderingStage(User $user, $project)
    {
        $project = Project::find($project->id);

        return $user->isGroupAdmin() && $user->getAssignedCompany($project) && ! ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) && in_array($project->status_id, Project::tenderingStagesStatus()) );
    }

    /**
     * Deletes the project.
     *
     * @param $project
     */
    public function delete($project)
    {
        $project->delete();
    }

    /**
     * Adds the previously assigned final contractor
     * into the array of companies to be associated with the project.
     *
     * @param Project $project
     * @param         $companyIds
     *
     * @return mixed
     */
    protected function addPreviouslyAssignedFinalContractor(Project $project, $companyIds)
    {
        // only the assigned final contractor should be added
        $relation = CompanyProject::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', Role::CONTRACTOR)
            ->first();

        if( $relation )
        {
            $companyIds[ ContractGroup::getIdByGroup(Role::CONTRACTOR) ] = $relation->company_id;
        }

        return $companyIds;
    }

    /**
     * Grant BuildSpace project permission to all users of a company.
     *
     * @param Project $project
     * @param array   $companyIds
     * @param         $projectStatus
     */
    public function grantBsProjectAccessToCompanyUsers(Project $project, array $companyIds, $projectStatus)
    {
        foreach($companyIds as $companyId)
        {
            if($projectStatus == BsProjectUserPermission::STATUS_TENDERING)
            {
                $projectManagerCompany = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_MANAGER))->first();

                if($projectManagerCompany)
                {
                    if($companyId == $projectManagerCompany->id)
                    {
                        continue;
                    }
                }

                $project->grantBsProjectPermissionToCompanyUsers(Company::find($companyId), $projectStatus);
            }
        }
    }

    /**
     * Revokes BuildSpace project permission to all users of a company.
     *
     * @param Project $project
     * @param array   $companyIds
     * @param         $projectStatus
     */
    public function revokeBsProjectAccessToCompanyUsers(Project $project, array $companyIds, $projectStatus)
    {
        foreach($companyIds as $companyId)
        {
            foreach(Company::find($companyId)->getActiveUsers() as $user)
            {
                $project->revokeBsProjectPermission($user, $projectStatus);
            }
        }
    }

    /**
     * Returns true if a project with the given reference exists.
     *
     * @param $reference
     *
     * @return bool
     */
    public function referenceExists($reference)
    {
        return \DB::table($this->project->getTable())
            ->where('reference', '=', $reference)
            ->whereNull('deleted_at')
            ->first() ? true : false;
    }

    /**
     * Returns the subsidiaries for the projects the user is assigned to.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function getSubsidiaries(User $user)
    {
        $allSubsidiaries = array();

        // Non tendering projects.
        $query = $this->createProjectFilteringQuery($user);
        $query->join('subsidiaries', 'p.subsidiary_id', '=', 'subsidiaries.id');
        $subsidiaries = $query->distinct()->get(array( 'subsidiaries.id' ));

        foreach($subsidiaries as $subsidiary)
        {
            $allSubsidiaries[] = $subsidiary->id;
        }

        // Tendering projects.
        $query = $this->createProjectFilteringQuery($user, true);
        $query->join('subsidiaries', 'p.subsidiary_id', '=', 'subsidiaries.id');
        $subsidiaries = $query->distinct()->get(array( 'subsidiaries.id' ));

        foreach($subsidiaries as $subsidiary)
        {
            $allSubsidiaries[] = $subsidiary->id;
        }

        $allSubsidiaries = array_unique($allSubsidiaries);

        return Subsidiary::whereIn('id', $allSubsidiaries)->get();
    }

    public function getProjectsDashboardData(User $user = null)
    {
        if( ! $user ) $user = \Confide::user();

        $projectIds = array();
        foreach($this->createProjectFilteringQuery($user)->distinct()->get(array( 'p.id' )) as $project)
        {
            $projectIds[] = $project->id;
        }
        $projects = Project::whereIn('id', $projectIds)->get();

        $designCount = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_DESIGN;
        })->count();

        $closedTenderCount = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_CLOSED_TENDER;
        })->count();

        $postContractCount = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_POST_CONTRACT;
        })->count();

        $completedCount = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_COMPLETED;
        })->count();

        $callingTenderCount = $projects->filter(function($project)
        {
            return in_array($project->status_id, array(
                StatusType::STATUS_TYPE_RECOMMENDATION_OF_TENDERER,
                StatusType::STATUS_TYPE_LIST_OF_TENDERER,
                StatusType::STATUS_TYPE_CALLING_TENDER,
            ));
        })->count();

        return array(
            'design'        => $designCount,
            'callingTender' => $callingTenderCount,
            'closedTender'  => $closedTenderCount,
            'postContract'  => $postContractCount,
            'completed'     => $completedCount,
        );
    }

    public function generateTender(Project $project)
    {
        $user = \Confide::user();

        $tender             = new Tender();
        $tender->project_id = $project->id;
        $tender->created_by = $user->id;
        $tender->updated_by = $user->id;

        $tender->save();

        return $tender;
    }

    public function import(User $user, array $inputs, Project $parentProject, UploadedFile $file)
    {
        $project = $this->project->newInstance(array(
            'business_unit_id'  => $user->company->id,
            'contract_id'       => $inputs['contract_id'],
            'reference'         => $inputs['reference'],
            'reference_suffix'  => $inputs['reference_suffix'],
            'running_number'    => $inputs['running_number'],
            'subsidiary_id'     => $inputs['subsidiary_id'],
            'description'       => $inputs['description'],
            'status_id'         => Project::getDefaultStatusId(),
            'created_by'        => $user->id,
            'updated_by'        => $user->id,
            'parent_project_id' => $parentProject->id,
        ));

        // Placeholder values. These values should be overwritten when importing project to BuildSpace.
        $project->title            = '';
        $project->address          = '';
        $project->country_id       = $parentProject->country_id;
        $project->state_id         = $parentProject->state_id;
        $project->work_category_id = $inputs['work_category_id'];

        if( ! $project->save() )
        {
            \Log::error('Failed to save project with id: ' . $project->id);

            return false;
        }

        $tender = $this->generateTender($project);
        $project->load('tenders');

        $this->letterOfAwardRepository->createEntry($project, $parentProject->letterOfAward->id);
        $this->formOfTenderRepository->createNewResources($tender, $parentProject->latestTender->formOfTender->id);

        \Log::info('Saved project with id: ' . $project->id);
        \Log::info('Starting import for project with id: ' . $project->id);

        try
        {
            if( ! $project->importProjectToBuildspace($file) )
            {
                \Log::error('Failed to import project with id: ' . $project->id);

                throw new \Exception('Failed to import project');
            }

            // Re-query to get instance with latest attributes after being populated in BuildSpace.
            $project = Project::find($project->id);

            // Update work category.
            $project->work_category_id = $inputs['work_category_id'];
            $project->save();

            $bsProject = $project->getBsProjectMainInformation();

            $bsProject->work_category_id = BsWorkCategory::initialise(WorkCategory::find($inputs['work_category_id']))->id;
            $bsProject->save();

            \Log::info('Imported project with id: ' . $project->id);

            $this->syncProjectPermissions($project, $parentProject);
        }
        catch(\Exception $e)
        {
            $project->delete();

            return false;
        }

        return $project;
    }

    private function syncProjectPermissions(Project $project, Project $templateProject)
    {
        $companies     = array();
        $assignedUsers = array();

        $contractorGroupId = ContractGroup::getIdByGroup(Role::CONTRACTOR);

        foreach($templateProject->selectedCompanies as $company)
        {
            // Sync all non-contractor companies.
            if( $company->pivot->contract_group_id == $contractorGroupId ) continue;

            $companies[ $company->pivot->contract_group_id ] = array( $company->pivot->company_id );

            $assignedUsers = $assignedUsers + $this->contractGroupProjectUserRepository->getAssignedUsersByProjectAndContractGroup($templateProject, ContractGroup::find($company->pivot->contract_group_id));
        }

        \Log::info('Syncing selected companies for project with id: ' . $project->id);

        $this->attachSelectedCompanies($project, $companies, false);

        \Log::info('Synced selected companies for project with id: ' . $project->id);

        \Log::info('Syncing user permissions for project with id: ' . $project->id);

        foreach($assignedUsers as $userId => $isEditor)
        {
            $this->contractGroupProjectUserRepository->addRole($project, User::find($userId), $isEditor);
        }

        \Log::info('Synced user permissions for project with id: ' . $project->id);

        \Log::info('Syncing Contract Management user permissions for project with id: ' . $project->id);

        foreach(ContractManagementUserPermission::where('project_id', '=', $templateProject->id)->get() as $record)
        {
            $newRecord = $record->replicate(array( 'id', 'project_id' ));

            $newRecord->project_id = $project->id;
            $newRecord->save();
        }

        foreach(ProjectContractManagementModule::where('project_id', '=', $templateProject->id)->get() as $templateProjectModule)
        {
            Verifier::setVerifiers(Verifier::getAssignedVerifierRecords($templateProjectModule)->lists('verifier_id'), ProjectContractManagementModule::getRecord($project->id, $templateProjectModule->module_identifier));
        }

        \Log::info('Synced Contract Management user permissions for project with id: ' . $project->id);
    }

    public function generateProjectDataSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();
        $projectData = $this->generateProjectData();

        $this->generateProjectOverviewWorksheet($spreadsheet->getActiveSheet(), $projectData);

        foreach($projectData['projectData'] as $projectStageKey => $projData)
        {
            $sheetTitle = $projectData['projectCountOverview'][ $projectStageKey ]['title'];
            $totalCount = $projectData['projectCountOverview'][ $projectStageKey ]['count'];
            $sheet      = new Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($sheet);
            $isPostContractStage = ( $projectStageKey === 'postContract' );

            $this->generateProjectStageDataSheet($sheet, $projectData['projectData'][ $projectStageKey ], $projectData['overallTotalByProject'], $sheetTitle, $totalCount, $isPostContractStage);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function generateProjectOverviewWorksheet($activeSheet, $projectData)
    {
        $totalProjectCount = 0;
        $rowIndex          = 1;

        $activeSheet->setTitle(trans('projects.projectOverview'));

        $activeSheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 8, $rowIndex));
        $activeSheet->setCellValueByColumnAndRow(1, $rowIndex, trans('projects.projectOverview'));
        $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, Color::COLOR_BLACK));

        ++$rowIndex;
        ++$rowIndex;

        $startRowIndex = $rowIndex;

        $activeSheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 8, $rowIndex));
        $activeSheet->setCellValueByColumnAndRow(1, $rowIndex, trans('projects.totalProjects'));
        $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');
        $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(false, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));
        $activeSheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $startRowIndex, 8, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

        $startRowIndex = $rowIndex;

        foreach($projectData['projectCountOverview'] as $projectStageKey => $overview)
        {
            ++$rowIndex;

            $activeSheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 4, $rowIndex));
            $activeSheet->setCellValueByColumnAndRow(1, $rowIndex, $overview['title']);
            $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

            $activeSheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(5, 8, $rowIndex));
            $activeSheet->setCellValueByColumnAndRow(5, $rowIndex, $overview['count'] . ' ' . trans('projects.project_s'));
            $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(5, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

            $totalProjectCount += $overview['count'];
        }

        ++$rowIndex;

        $activeSheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 4, $rowIndex));
        $activeSheet->setCellValueByColumnAndRow(1, $rowIndex, trans('projects.total'));
        $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

        $activeSheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(5, 8, $rowIndex));
        $activeSheet->setCellValueByColumnAndRow(5, $rowIndex, $totalProjectCount . ' ' . trans('projects.project_s'));
        $activeSheet->getStyle($activeSheet->getCellByColumnAndRow(5, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

        $activeSheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $startRowIndex, 8, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

        return $activeSheet;
    }

    private function generateProjectStageDataSheet($sheet, $projectData, $overallTotalByProject, $sheetTitle, $totalCount, $isPostContractStage = false)
    {
        $rowIndex = 1;

        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 5, $rowIndex));
        $sheet->setCellValueByColumnAndRow(1, $rowIndex, $sheetTitle . ' ' . trans('projects.stage'));

        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(6, 8, $rowIndex));
        $sheet->setCellValueByColumnAndRow(6, $rowIndex, $totalCount . ' ' . trans('projects.project_s'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(6, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, 1, 8, 1))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));
        $sheet->getStyle($sheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, Color::COLOR_BLACK));

        ++$rowIndex;
        ++$rowIndex;

        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 4, $rowIndex));
        $sheet->setCellValueByColumnAndRow(1, $rowIndex, trans('projects.development'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

        $sheet->setCellValueByColumnAndRow(5, $rowIndex, trans('projects.no'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(5, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(6, 16, $rowIndex));
        $sheet->setCellValueByColumnAndRow(6, $rowIndex, trans('projects.projectName'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(6, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

        $referenceNumberText = $isPostContractStage ? trans('projects.referenceNumber') : trans('projects.reference');

        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(17, 19, $rowIndex));
        $sheet->setCellValueByColumnAndRow(17, $rowIndex, $referenceNumberText);
        $sheet->getStyle($sheet->getCellByColumnAndRow(17, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

        $endColIndex = 19;

        if( $isPostContractStage )
        {
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(20, 24, $rowIndex));
            $sheet->setCellValueByColumnAndRow(20, $rowIndex, trans('tenders.workCategory'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(20, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(25, 29, $rowIndex));
            $sheet->setCellValueByColumnAndRow(25, $rowIndex, trans('tenders.awardedContractor'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(25, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(30, 32, $rowIndex));
            $sheet->setCellValueByColumnAndRow(30, $rowIndex, trans('tenders.awardedContractSum'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(30, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(33, 35, $rowIndex));
            $sheet->setCellValueByColumnAndRow(33, $rowIndex, trans('tenders.pteBudget'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(33, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(36, 38, $rowIndex));
            $sheet->setCellValueByColumnAndRow(36, $rowIndex, trans('tenders.amountDifference'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(36, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(39, 40, $rowIndex));
            $sheet->setCellValueByColumnAndRow(39, $rowIndex, trans('tenders.difference') . ' (%)');
            $sheet->getStyle($sheet->getCellByColumnAndRow(39, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(41, 42, $rowIndex));
            $sheet->setCellValueByColumnAndRow(41, $rowIndex, trans('tenders.dateOfAward'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(41, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(43, 47, $rowIndex));
            $sheet->setCellValueByColumnAndRow(43, $rowIndex, trans('tenders.contractPeriod'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(43, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(48, 52, $rowIndex));
            $sheet->setCellValueByColumnAndRow(48, $rowIndex, trans('tenders.contractCommencementDate'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(48, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(53, 56, $rowIndex));
            $sheet->setCellValueByColumnAndRow(53, $rowIndex, trans('tenders.contractCompletionDate'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(53, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(57, 60, $rowIndex));
            $sheet->setCellValueByColumnAndRow(57, $rowIndex, trans('tenders.procurementMethod'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(57, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(61, 63, $rowIndex));
            $sheet->setCellValueByColumnAndRow(61, $rowIndex, trans('general.remarks'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(61, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(64, 67, $rowIndex));
            $sheet->setCellValueByColumnAndRow(64, $rowIndex, trans('projects.publishToPostContractDate'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(64, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $endColIndex = 67;
        }

        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $rowIndex, $endColIndex, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

        ++$rowIndex;

        foreach($projectData as $subsidiaryId => $projData)
        {
            $subsidiary    = Subsidiary::find($subsidiaryId);
            $startRowIndex = $rowIndex;

            $awardedContractSumTotal = 0.0;
            $pteTotal                = 0.0;
            $amountDifferenceTotal   = 0.0;

            foreach($projData as $data)
            {
                $projectTitleRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                $projectTitleRichText->createText(trim($data['project_title']));

                if( $data['parent_project_title'] )
                {
                    $mainProjecText = $projectTitleRichText->createTextRun(' (' . trans('projects.mainProjectText') . ' : ' . $data['parent_project_title'] . ')');
                    $mainProjecText->getFont()->setColor(new Color(Color::COLOR_BLUE));
                }

                $sheet->setCellValueByColumnAndRow(5, $rowIndex, $data['count']);
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(5, $rowIndex, 5, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));
                $sheet->getStyle($sheet->getCellByColumnAndRow(5, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

                $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(6, 16, $rowIndex));
                $sheet->setCellValueByColumnAndRow(6, $rowIndex, $projectTitleRichText);
                $sheet->getStyle($sheet->getCellByColumnAndRow(6, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(6, $rowIndex, 16, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(17, 19, $rowIndex));
                $sheet->setCellValueByColumnAndRow(17, $rowIndex, $data['reference']);
                $sheet->getStyle($sheet->getCellByColumnAndRow(17, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('left');
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(17, $rowIndex, 19, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                if( $isPostContractStage )
                {
                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(20, 24, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(20, $rowIndex, $data['work_category']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(20, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(20, $rowIndex, 24, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(25, 29, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(25, $rowIndex, $data['awarded_contractor']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(25, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(25, $rowIndex, 29, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(30, 32, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(30, $rowIndex, $data['awarded_contract_sum']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(30, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(30, $rowIndex, 32, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $pte                  = 0.0;
                    $amountDifference     = 0.0;
                    $percentageDifference = 0.0;

                    if( $data['project_structure_id'] && array_key_exists($data['project_structure_id'], $overallTotalByProject) )
                    {
                        $pte              = $overallTotalByProject[ $data['project_structure_id'] ];
                        $amountDifference = $pte - $data['awarded_contract_sum'];

                        if( $pte > 0.0 )
                        {
                            $percentageDifference = number_format(( ( $amountDifference / $pte ) * 100.0 ), 2, '.', ',');
                        }
                    }

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(33, 35, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(33, $rowIndex, $pte);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(33, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(33, $rowIndex, 35, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(36, 38, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(36, $rowIndex, $amountDifference);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(36, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(36, $rowIndex, 38, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(39, 40, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(39, $rowIndex, $percentageDifference);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(39, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(39, $rowIndex, 40, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(41, 42, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(41, $rowIndex, $data['date_of_award']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(41, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(41, $rowIndex, 42, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(43, 47, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(43, $rowIndex, $data['contract_period']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(43, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(43, $rowIndex, 47, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(48, 52, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(48, $rowIndex, $data['contract_commencement_date']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(48, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(48, $rowIndex, 52, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(53, 56, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(53, $rowIndex, $data['contract_completion_date']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(53, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(53, $rowIndex, 56, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(57, 60, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(57, $rowIndex, $data['procurement_method']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(57, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(57, $rowIndex, 60, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(61, 63, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(61, $rowIndex, '');
                    $sheet->getStyle($sheet->getCellByColumnAndRow(61, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(61, $rowIndex, 63, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                    $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(64, 67, $rowIndex));
                    $sheet->setCellValueByColumnAndRow(64, $rowIndex, $data['publish_to_post_contract_date']);
                    $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(64, $rowIndex, 67, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));
                    $sheet->getStyle($sheet->getCellByColumnAndRow(64, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

                    $awardedContractSumTotal += $data['awarded_contract_sum'];
                    $pteTotal += $pte;
                    $amountDifferenceTotal += $amountDifference;
                }

                ++$rowIndex;
            }

            $sheet->mergeCells(SpreadsheetHelper::getRangeByColumnAndRow(1, $startRowIndex, 4, $rowIndex - 1));
            $sheet->setCellValueByColumnAndRow(1, $startRowIndex, trim($subsidiary->getFullNameAttribute()));
            $sheet->getStyle($sheet->getCellByColumnAndRow(1, $startRowIndex)->getCoordinate())->getAlignment()->setVertical('center');
            $sheet->getStyle($sheet->getCellByColumnAndRow(1, $startRowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $startRowIndex, 4, $rowIndex - 1))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

            if( $isPostContractStage )
            {
                ++$rowIndex;

                $sheet->mergeCells(SpreadsheetHelper::getRangeByColumnAndRow(1, $rowIndex - 1, 29, $rowIndex - 1));
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $rowIndex - 1, 29, $rowIndex - 1))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                $sheet->mergeCells(SpreadsheetHelper::getRangeByColumnAndRow(30, $rowIndex - 1, 32, $rowIndex - 1));
                $sheet->setCellValueByColumnAndRow(30, $rowIndex - 1, $awardedContractSumTotal);
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(30, $rowIndex - 1, 32, $rowIndex - 1))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                $sheet->mergeCells(SpreadsheetHelper::getRangeByColumnAndRow(33, $rowIndex - 1, 35, $rowIndex - 1));
                $sheet->setCellValueByColumnAndRow(33, $rowIndex - 1, $pteTotal);
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(33, $rowIndex - 1, 35, $rowIndex - 1))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                $sheet->mergeCells(SpreadsheetHelper::getRangeByColumnAndRow(36, $rowIndex - 1, 38, $rowIndex - 1));
                $sheet->setCellValueByColumnAndRow(36, $rowIndex - 1, $amountDifferenceTotal);
                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(36, $rowIndex - 1, 38, $rowIndex - 1))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

                $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $rowIndex - 1, 67, $rowIndex - 1))->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'F0FF70'));

                ++$rowIndex;
            }
        }

        return $sheet;
    }

    private function generateProjectData()
    {
        $allProjects            = Project::all();
        $projectsGroupedByStage = $this->getProjectsGroupByStage($allProjects);

        $projectData         = [];
        $projectStructureIds = [];

        foreach($projectsGroupedByStage as $projectStageKey => $projects)
        {
            if( empty( $projects ) ) continue;

            $data  = [];
            $temp  = null;
            $count = 0;

            foreach($projects as $project)
            {
                if( ! array_key_exists($project->subsidiary_id, $data) )
                {
                    $temp  = [];
                    $count = 0;
                }

                $awardedContractor         = null;
                $publishToPostContractDate = null;
                $awardedContractSum        = null;
                $dateOfAward               = null;
                $contractPeriod            = null;
                $contractCommencementDate  = null;
                $contractCompletionDate    = null;
                $procurementMethod         = null;
                $years                     = null;
                $months                    = null;
                $days                      = null;
                $bsProjectStructure        = null;

                $isPostContractStage = ( $project->status_id == StatusType::STATUS_TYPE_POST_CONTRACT );

                if( $isPostContractStage )
                {
                    $bsProjectStructure = $project->getBsProjectMainInformation()->projectStructure;

                    if( $bsProjectStructure )
                    {
                        array_push($projectStructureIds, $bsProjectStructure->id);
                    }

                    if( $bsProjectStructure && $project->getBsProjectMainInformation()->getAwardedEProjectCompany() )
                    {
                        $awardedContractor = $project->getBsProjectMainInformation()->getAwardedEProjectCompany()->name;
                    }

                    if( $bsProjectStructure && $bsProjectStructure->postContract )
                    {
                        $publishToPostContractDate = Carbon::parse($bsProjectStructure->postContract->published_at)->format(\Config::get('dates.published_to_post_contract_date_formatting'));
                        $awardedContractSum        = $bsProjectStructure->postContract->getContractSum();
                    }

                    if( $bsProjectStructure && $bsProjectStructure->letterOfAward )
                    {
                        $dateOfAward              = Carbon::parse($bsProjectStructure->letterOfAward->awarded_date)->format(\Config::get('dates.published_to_post_contract_date_formatting'));
                        $contractCommencementDate = Carbon::parse($bsProjectStructure->letterOfAward->contract_period_from)->format(\Config::get('dates.published_to_post_contract_date_formatting'));
                        $contractCompletionDate   = Carbon::parse($bsProjectStructure->letterOfAward->contract_period_to)->format(\Config::get('dates.published_to_post_contract_date_formatting'));

                        $fromDate  = new DateTime($bsProjectStructure->letterOfAward->contract_period_from);
                        $toDate    = new DateTime($bsProjectStructure->letterOfAward->contract_period_to);
                        $toDate    = $toDate->add(new DateInterval('P1D'));
                        $dmyString = date_diff($fromDate, $toDate)->format("%y,%m,%d");
                        $dmyArray  = explode(',', $dmyString);

                        if( $dmyArray[0] > 0 )
                        {
                            $uom   = $dmyArray[0] > 1 ? 'years(s)' : 'year';
                            $years = $dmyArray[0] . ' ' . $uom;
                        }

                        if( $dmyArray[1] > 0 )
                        {
                            $uom    = $dmyArray[1] > 1 ? 'month(s)' : 'month';
                            $months = $dmyArray[1] . ' ' . $uom;
                        }

                        if( $dmyArray[2] > 0 )
                        {
                            $uom  = $dmyArray[2] > 1 ? 'day(s)' : 'day';
                            $days = $dmyArray[2] . ' ' . $uom;
                        }

                        $contractPeriod = trim($years . ' ' . $months . ' ' . $days);
                    }

                    if( $project->latestTender && $project->latestTender->listOfTendererInformation && $project->latestTender->listOfTendererInformation->procurementMethod )
                    {
                        $procurementMethod = $project->latestTender->listOfTendererInformation->procurementMethod->name;
                    }
                }

                $tempData                         = [];
                $tempData['count']                = ++$count;
                $tempData['project_id']           = $project->id;
                $tempData['project_title']        = $project->title;
                $tempData['reference']            = $project->reference;
                $tempData['parent_project_title'] = $project->parentProject ? $project->parentProject->title : null;

                if( $isPostContractStage )
                {
                    $tempData['reference']                     = ($bsProjectStructure && $bsProjectStructure->letterOfAward) ? $bsProjectStructure->letterOfAward->reference : null;
                    $tempData['project_structure_id']          = $bsProjectStructure ? $bsProjectStructure->id : null;
                    $tempData['publish_to_post_contract_date'] = $publishToPostContractDate;
                    $tempData['work_category']                 = $project->workCategory->name;
                    $tempData['awarded_contractor']            = $awardedContractor;
                    $tempData['awarded_contract_sum']          = $awardedContractSum ?? 0.0;
                    $tempData['date_of_award']                 = $dateOfAward;
                    $tempData['contract_period']               = $contractPeriod;
                    $tempData['contract_commencement_date']    = $contractCommencementDate;
                    $tempData['contract_completion_date']      = $contractCompletionDate;
                    $tempData['procurement_method']            = $procurementMethod;
                }

                array_push($temp, $tempData);
                $data[ $project->subsidiary_id ] = $temp;
            }

            $projectData[ $projectStageKey ] = $data;
        }

        if( ! empty( $projectsGroupedByStage['design'] ) )
        {
            $results['projectCountOverview']['design']['count'] = count($projectsGroupedByStage['design']);
            $results['projectCountOverview']['design']['title'] = trans('projects.design');
        }

        if( ! empty( $projectsGroupedByStage['callingTender'] ) )
        {
            $results['projectCountOverview']['callingTender']['count'] = count($projectsGroupedByStage['callingTender']);
            $results['projectCountOverview']['callingTender']['title'] = trans('projects.callingTender');
        }

        if( ! empty( $projectsGroupedByStage['closedTender'] ) )
        {
            $results['projectCountOverview']['closedTender']['count'] = count($projectsGroupedByStage['closedTender']);
            $results['projectCountOverview']['closedTender']['title'] = trans('projects.closedTender');
        }

        if( ! empty( $projectsGroupedByStage['postContract'] ) )
        {
            $results['projectCountOverview']['postContract']['count'] = count($projectsGroupedByStage['postContract']);
            $results['projectCountOverview']['postContract']['title'] = trans('projects.postContract');
        }

        if( ! empty( $projectsGroupedByStage['completed'] ) )
        {
            $results['projectCountOverview']['completed']['count'] = count($projectsGroupedByStage['completed']);
            $results['projectCountOverview']['completed']['title'] = trans('projects.completed');
        }

        $results['projectData']           = $projectData;
        $results['overallTotalByProject'] = ProjectStructure::getOverallTotalByProjects($projectStructureIds);

        return $results;
    }

    private function getProjectsGroupByStage($projects)
    {
        $designStageProjects = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_DESIGN;
        })->sortBy('subsidiary_id')->values()->all();

        $closedTenderStageProjects = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_CLOSED_TENDER;
        })->sortBy('subsidiary_id')->values()->all();

        $postContractStageProjects = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_POST_CONTRACT;
        })->sortBy('subsidiary_id')->values()->all();

        $completedStageProjects = $projects->filter(function($project)
        {
            return $project->status_id == StatusType::STATUS_TYPE_COMPLETED;
        })->sortBy('subsidiary_id')->values()->all();

        $callingTenderStageProjects = $projects->filter(function($project)
        {
            return in_array($project->status_id, array(
                StatusType::STATUS_TYPE_RECOMMENDATION_OF_TENDERER,
                StatusType::STATUS_TYPE_LIST_OF_TENDERER,
                StatusType::STATUS_TYPE_CALLING_TENDER,
            ));
        })->sortBy('subsidiary_id')->values()->all();

        return array(
            'design'        => $designStageProjects,
            'callingTender' => $callingTenderStageProjects,
            'closedTender'  => $closedTenderStageProjects,
            'postContract'  => $postContractStageProjects,
            'completed'     => $completedStageProjects,
        );
    }

    public function submitClaims(Project $project, UploadedFile $file)
    {
        $user = \Confide::user();

        $claimRevision = $project->getBsProjectMainInformation()->projectStructure->postContract->postContractClaimRevisions->first();

        $response = array(
            'running' => false
        );

        $client = new \GuzzleHttp\Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => getenv('BUILDSPACE_URL'),
        ));

        try
        {
            $request = $client->post('claimTransfer/importClaims', array(
                'multipart' => [
                    [
                        'name'     => 'uploadedfile',
                        'filename' => $file->getClientOriginalName(),
                        'contents' => fopen($file->getPathname(), 'r'),
                    ],
                    [
                        'name'     => 'user_id',
                        'contents' => $user->getBsUser()->id,
                    ],
                    [
                        'name'     => 'revision_id',
                        'contents' => $claimRevision->id,
                    ],
                ]
            ));

            $response = json_decode($request->getBody()->getContents(), true);
        }
        catch(\Exception $e)
        {
            \Log::info("Claims submission failed => {$e->getMessage()}");
        }

        return $response;
    }
}
