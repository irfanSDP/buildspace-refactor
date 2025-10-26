<?php

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Users\LmsUser;
use PCK\Users\UserRepository;
use PCK\Companies\CompanyRepository;
use PCK\Users\UserCompanyLog;
use PCK\Projects\Project;
use PCK\ContractGroups\Types\Role;
use PCK\Companies\Company;
use PCK\SystemModules\SystemModuleConfiguration;

class UsersController extends \BaseController {

    private $companyRepository;
    private $userRepo;

    public function __construct(CompanyRepository $companyRepository, UserRepository $userRepo)
    {
        $this->companyRepository = $companyRepository;
        $this->userRepo = $userRepo;
    }

    public function allUsersIndex()
    {
        $backRoute = route('projects.index');

        return View::make('users.all_users_index', compact('backRoute'));
    }

    public function getAllUsers()
    {
        $inputs = Input::all();

        $limit = $inputs['size'];
        $page  = $inputs['page'];

        $query = "SELECT
                    u.id,
                    u.name,
                    u.designation,
                    u.email,
                    u.contact_number,
                    c.id AS company_id,
                    c.name AS company_name,
                    cgc.name AS company_role,
                    u.is_admin,
                    u.is_super_admin,
                    u.confirmed,
                    u.account_blocked_status
                    FROM users u
                    LEFT OUTER JOIN companies c ON c.id = u.company_id
                    LEFT OUTER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id";

        if(isset($inputs['filters']))
        {
            $whereClauseFound = false;
            $whereClause      = ' WHERE';

            foreach($inputs['filters'] as $filter)
            {
                $field = trim($filter['field']);
                $value = trim($filter['value']);

                if($whereClauseFound)
                {
                    $whereClause = ' AND';
                }

                if(strlen($value) == 0) continue;

                switch($field)
                {
                    case 'name':
                        $query .= " {$whereClause} u.name ILIKE '%{$value}%'";

                        $whereClauseFound = true;

                        break;
                    case 'designation':
                        $query .= " {$whereClause} u.designation ILIKE '%{$value}%'";

                        $whereClauseFound = true;

                        break;
                    case 'email':
                        $query .= " {$whereClause} u.email ILIKE '%{$value}%'";

                        $whereClauseFound = true;

                        break;
                    case 'contactNumber':
                        $query .= " {$whereClause} u.contact_number ILIKE '%{$value}%'";

                        $whereClauseFound = true;

                        break;
                    case 'companyName':
                        $query .= " {$whereClause} c.name ILIKE '%{$value}%'";

                        $whereClauseFound = true;

                        break;
                    case 'companyRole':
                        $query .= " {$whereClause} cgc.name ILIKE '%{$value}%'";

                        $whereClauseFound = true;

                        break;
                    case 'isUserConfirmed_text':
                        if($value > 0)
                        {
                            if($value == 1)
                            {
                                $query .= " {$whereClause} u.confirmed IS TRUE";
                            }
                            else
                            {
                                $query .= " {$whereClause} u.confirmed IS FALSE";
                            }
                        }

                        $whereClauseFound = true;

                        break;
                    case 'isUserBlocked_text':
                        if($value > 0)
                        {
                            if($value == 1)
                            {
                                $query .= " {$whereClause} u.account_blocked_status IS TRUE";
                            }
                            else
                            {
                                $query .= " {$whereClause} u.account_blocked_status IS FALSE";
                            }
                        }

                        $whereClauseFound = true;

                        break;
                    case 'isAdmin_text':
                        if($value > 0)
                        {
                            if($value == 1)
                            {
                                $query .= " {$whereClause} u.is_admin IS TRUE";
                            }
                            else
                            {
                                $query .= " {$whereClause} u.is_admin IS FALSE";
                            }
                        }

                        $whereClauseFound = true;

                        break;
                }
            }
        }

        $query .= " ORDER BY u.id ASC";
        $query .= " LIMIT " . $limit . " OFFSET " . ($limit * ($page - 1));

        $records = DB::select(DB::raw($query));

        $rowCount = User::count();

        $data  = [];

        foreach($records as $key => $user)
        {
            $count = (($page - 1) * $limit) + ($key + 1);

            $company = Company::find($user->company_id);

            array_push($data, [
                'indexNo'                       => $count,
                'id'                            => $user->id,
                'name'                          => $user->name,
                'designation'                   => $user->designation,
                'email'                         => $user->email,
                'contactNumber'                 => $user->contact_number,
                'companyName'                   => $user->company_name,
                'companyRole'                   => $user->company_role,
                'isUserConfirmed'               => $user->confirmed,
                'isUserConfirmed_text'          => $user->confirmed ? trans('users.confirmed') : trans('users.pending'),
                'isUserBlocked_text'            => $user->account_blocked_status ? trans('users.yes') : trans('users.no'),
                'isSuperAdmin'                  => $user->is_super_admin,
                'isAdmin_text'                  => $user->is_admin ? trans('users.yes') : trans('users.no'),
                'route_edit_user'               => $user->is_super_admin ? null : route('user.edit', [$user->id]),
                'route_delete_user'             => ( $user->is_super_admin || $user->confirmed ) ? null : route('user.delete', [$user->id]),
                'route_resend_validation_email' => $user->is_super_admin ? null : route('user.validation.email.resend', [$user->id]),
                'route_switch_company'          => $user->is_super_admin ? null : route('users.company.switch', [$user->id]),
                'route_get_foster_companies'    => $user->is_super_admin ? null : route('user.foster.companies.get', [$user->id]),
                'userCanBeTransferred'          => $user->is_super_admin ? false : $company->usersCanBeTransferred(),
                'csrf_token'                    => csrf_token(),
            ]);
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getListOfFosterCompanies($userId)
    {
        $user = User::find($userId);
        $fosterCompanies = [];
        $count = 0;
        
        foreach($user->fosterCompanies as $fosterCompany)
        {
            array_push($fosterCompanies, [
                'indexNo' => ++$count,
                'id'      => $fosterCompany->id,
                'name'    => $fosterCompany->name,
                'type'    => $fosterCompany->contractGroupCategory->name,
                'roc'     => $fosterCompany->reference_no,
            ]);
        }

        return Response::json($fosterCompanies);
    }

    public function exportAllUsers()
    {
        $reportGenerator = new \PCK\Reports\AllUsersReportGenerator();

        return $reportGenerator->generate();
    }

    public function delete($userId)
    {
        $user = User::find($userId);

        if( $user->confirmed )
        {
            Flash::error(trans('users.userCannotBeDeleted') . " ({$user->email})");

            return Redirect::route('users.all.index');
        }

        try
        {
            $user->delete();

            \Flash::success(trans('users.userDeleted') . " ({$user->email})");
        }
        catch(Exception $e)
        {
            Flash::error(trans('general.somethingWentWrong'));

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        return Redirect::route('users.all.index');
    }

    public function edit($userId)
    {
        $user = User::find($userId);
        $currentUser = \Confide::user();

        return View::make('users.show', array(
            'pageTitle'   => 'Update User',
            'currentUser' => $currentUser,
            'company'     => $user->company,
            'user'        => $user,
            'type'        => 'update',
            'backRoute'   => route('users.all.index'),
        ));
    }

    public function update($userId)
    {
        $input       = Input::all();
        $currentUser = \Confide::user();
        $user        = User::find($userId);
        $user = $this->userRepo->update($user, $input);
        
        if( isset( $user->errors ) )
        {
            Flash::error('Form Validation Error');

            return Redirect::back()->withInput()->with('errors', $user->errors);
        }

        Flash::success(trans('users.userUpdated'));

        if( ( ! $currentUser->isGroupAdmin() ) && ( ! $currentUser->isSuperAdmin() ) )
        {
            // Redirect to to projects page if the current user no longer has access to the module.
            return Redirect::route('projects.index');
        }

        return Redirect::route('users.all.index');
    }

    public function resendValidationEmail($userId)
    {
        $error   = null;
        $success = false;

        try
        {
            $user = User::find($userId);

            if($user->confirmed)
            {
                $error = trans('users.userAlreadyConfirmed');
            }
            else
            {
                $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

                if($vendorManagementModuleEnabled)
                {
                    if($user->company->contractGroupCategory->isTypeInternal())
                    {
                        \Event::fire('user.newlyRegistered', $user);
                    }
                    else
                    {
                        \Event::fire('vendor.newlyRegistered', $user);
                    }
                }
                else
                {
                    \Event::fire('user.newlyRegistered', $user);
                }

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
        }
        
        return Response::json([
            'error'   => $error,
            'success' => $success,
        ]);
    }

    public function switchCompany($userId)
    {
        $user = User::find($userId);

        if($user->hasCompanyRoles(array( Role::PROJECT_OWNER )))
        {
            $role = Role::PROJECT_OWNER;
        }

        if($user->hasCompanyRoles(array( Role::PROJECT_MANAGER )))
        {
            $role = Role::PROJECT_MANAGER;
        }

        if($user->hasCompanyRoles(array( Role::GROUP_CONTRACT )))
        {
            $role = Role::GROUP_CONTRACT;
        }

        $companiesList = $this->companyRepository->getByRoles(array( $role ))->lists('name', 'id');

        $log = UserCompanyLog::where('user_id', '=', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return View::make('users.switchCompany.form', compact('user', 'companiesList', 'log'));
    }

    public function switchCompanyUpdate($userId)
    {
        $user = User::find($userId);

        if( ( $companyId = Input::get('company_id') ) != $user->company_id )
        {
            \DB::table('contract_group_project_users')
                ->where('user_id', '=', $user->id)
                ->delete();

            \DB::table('company_imported_users')
                ->where('user_id', '=', $user->id)
                ->where('company_id', '=', $companyId)
                ->delete();

            $bsGroup = $user->getBsCompanyGroup();
            $bsGroup->removeBsUser($user->getBsUser());

            $user->company_id = $companyId;
            $user->save();
            $user->load('company');

            $bsGroup = $user->getBsCompanyGroup();
            $bsGroup->addBsUser($user->getBsUser());
        }

        Flash::success(trans('users.userIsNowInCompany', array( 'user' => $user->name, 'company' => $user->company->name )));

        return Redirect::back();
    }

    public function checkProjectUserIsTransferable(Project $project, $userId)
    {
        $user = User::find($userId);
        $isTransferable = $user->isTransferable($project);

        return Response::json([
            'isTransferable' => $isTransferable,
        ]);
    }

    public function checkProjectEditorRemovable(Project $project ,$userId)
    {
        $user = User::find($userId);
        $isEditorRemovable = empty($user->getPendingReviewTenderResubmissions($project));

        return Response::json([
            'isEditorRemovable' => $isEditorRemovable,
        ]);
    }

    public function checkUserIsTransferable($userId)
    {
        $user               = User::find($userId);
        $isTransferable     = $user->isTransferable();

        return Response::json([
            'isTransferable' => $isTransferable,
        ]);
    }

    public function getPendingTenderingTasks($userId, Project $project = null)
    {
        $includeFutureTasks    = true;
        $user                  = User::find($userId);
        $pendingTenderingTasks = $user->getListOfTenderingPendingReviewTasks($includeFutureTasks, $project);

        return Response::json($pendingTenderingTasks);
    }

    public function getPendingPostContractTasks($userId, Project $project = null)
    {
        $includeFutureTasks       = true;
        $user                     = User::find($userId);
        $pendingPostContractTasks = $user->getListOfPostContractPendingReviewTasks($includeFutureTasks, $project);

        return Response::json($pendingPostContractTasks);
    }

    public function getPendingSiteModuleTasks($userId, Project $project = null)
    {
        $includeFutureTasks       = true;
        $user                     = User::find($userId);
        $pendingSiteModuleTasks   = $user->getListOfSiteModulePendingReviewTasks($includeFutureTasks, $project);

        return Response::json($pendingSiteModuleTasks);
    }

    public function getAssignedLetterOfAwardPermissions($userId, Project $project = null)
    {
        $user = User::find($userId);
        $letterOfAwardUserPermissions = $user->getUserPermissionsInLetterOfAward($project);

        return Response::json($letterOfAwardUserPermissions);
    }

    public function getAssignedRequestForVariationPermissions($userId, Project $project = null)
    {
        $user = User::find($userId);
        $requestForVariationUserPermissions = $user->getUserPermissionsInRequestOfVariation($project);

        return Response::json($requestForVariationUserPermissions);
    }

    public function getAssignedContractManagementPermissions($userId, Project $project = null)
    {
        $user = User::find($userId);
        $contractManagementUserPermissions = $user->getUserPermissionsInContractManagement($project);

        return Response::json($contractManagementUserPermissions);
    }

    public function getAssignedSiteManagementPermissions($userId, Project $project = null)
    {
        $user = User::find($userId);
        $siteManagementUserPermissions = $user->getUserPermissionsInSiteManagement($project);

        return Response::json($siteManagementUserPermissions);
    }

    public function getAssignedRequestForInspectionPermissions($userId, Project $project = null)
    {
        $user = User::find($userId);
        $requestForInspectionUserPermissions = $user->getUserPermissionsInRequestForInspection($project);

        return Response::json($requestForInspectionUserPermissions);
    }

    public function getVendorPerformanceEvaluationApprovals($userId, Project $project = null)
    {
        $user = User::find($userId);
        $pendingVendorPerformanceEvaluationFormApprovals = $user->getPendingVendorPerformanceEvaluationFormApprovals($project);

        return Response::json($pendingVendorPerformanceEvaluationFormApprovals);
    }

    public function listLmsUsers($companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $query = "SELECT u.id, u.name, u.email, u.contact_number, lms.lms_course_name, lms.lms_course_completed, lms.lms_course_completed_at
                  FROM users u
                  LEFT JOIN lms_users lms
                  ON lms.user_id = u.id
                  WHERE company_id = {$company->id} ";

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))
                {
                    $val = trim($filters['value']);

                    switch(trim($filters['field']))
                    {
                        case 'name':
                        case 'email':
                        case 'contact_number':
                            if(strlen(trim($val)) > 0)
                            {
                                $query .= " AND {$filters['field']} ILIKE '%{$val}%'";
                            }
                            break;
                    }
                }
            }
        }

        $query .= "ORDER BY id DESC ";

        $rowCount = count(DB::select(DB::raw($query)));

        $query .= "LIMIT {$limit} OFFSET " . $limit * ($page - 1) . ";";

        $queryResults = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResults as $key => $result)
        {
            $counter = ($page-1) * $limit + $key + 1;

            array_push($data, [
                'id'                            => $result->id,
                'counter'                       => $counter,
                'name'                          => $result->name,
                'email'                         => $result->email,
                'contact_number'                => $result->contact_number,
                'course_name'                   => $result->lms_course_name,
                'course_completed'              => $result->lms_course_completed ? trans('general.yes') : trans('general.no'),
                'course_completed_at'           => date('d-m-Y', strtotime($result->lms_course_completed_at)),
            ]);
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function listCompanyUsers($companyId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $company = Company::find($companyId);

        $query = "SELECT id, name, email, contact_number, is_admin, confirmed, account_blocked_status 
                  FROM users
                  WHERE company_id = {$company->id} ";

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))
                {
                    $val = trim($filters['value']);

                    switch(trim($filters['field']))
                    {
                        case 'name':
                        case 'email':
                        case 'contact_number':
                            if(strlen(trim($val)) > 0)
                            {
                                $query .= " AND {$filters['field']} ILIKE '%{$val}%'";
                            }
                            break;
                        case 'is_admin':
                            if((int) $val > 0)
                            {
                                if($val == 1)
                                {
                                    $query .= " AND is_admin IS TRUE ";
                                }
                                else
                                {
                                    $query .= " AND is_admin IS FALSE ";
                                }
                            }
                            break;
                        case 'confirmed':
                            if((int) $val > 0)
                            {
                                if($val == 1)
                                {
                                    $query .= " AND confirmed IS TRUE ";
                                }
                                else
                                {
                                    $query .= " AND confirmed IS FALSE ";
                                }
                            }
                            break;
                        case 'account_blocked_status':
                            if((int) $val > 0)
                            {
                                if($val == 1)
                                {
                                    $query .= " AND account_blocked_status IS TRUE ";
                                }
                                else
                                {
                                    $query .= " AND account_blocked_status IS FALSE ";
                                }
                            }
                            break;
                    }
                }
            }
        }

        $query .= "ORDER BY id DESC ";

        $rowCount = count(DB::select(DB::raw($query)));

        $query .= "LIMIT {$limit} OFFSET " . $limit * ($page - 1) . ";";

        $queryResults = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResults as $key => $result)
        {
            $counter = ($page-1) * $limit + $key + 1;

            array_push($data, [
                'id'                            => $result->id,
                'counter'                       => $counter,
                'name'                          => $result->name,
                'email'                         => $result->email,
                'contact_number'                => $result->contact_number,
                'is_admin'                      => $result->is_admin ? trans('general.yes') : trans('general.no'),
                'confirmed'                     => $result->confirmed ? trans('users.confirmed') : trans('users.pending'),
                'account_blocked_status'        => $result->account_blocked_status ? trans('general.yes') : trans('general.no'),
                'route:resend_validation_email' => $result->confirmed ? null : route('processor.validation.email.resend', [$result->id]),
            ]);
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}
