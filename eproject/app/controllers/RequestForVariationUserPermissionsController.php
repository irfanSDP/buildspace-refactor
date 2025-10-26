<?php

use PCK\RequestForVariation\RequestForVariationUserPermissionGroup;
use PCK\RequestForVariation\RequestForVariationUserPermission;
use PCK\RequestForVariation\RequestForVariationUserPermissionRepository;
use PCK\Projects\Project;
use PCK\Users\User;

use PCK\Forms\RequestForVariationUserPermissionGroupForm;

class RequestForVariationUserPermissionsController extends BaseController {
    private $rfvUserPermissionsRepository;
    private $requestForVariationUserPermissionGroupForm;

    public function __construct(
        RequestForVariationUserPermissionGroupForm $requestForVariationUserPermissionGroupForm,
        RequestForVariationUserPermissionRepository $rfvUserPermissionsRepository
    ) {
        $this->requestForVariationUserPermissionGroupForm = $requestForVariationUserPermissionGroupForm;
        $this->rfvUserPermissionsRepository               = $rfvUserPermissionsRepository;
    }

    public function index(Project $project)
    {
        return View::make('request_for_variation.userPermissions.index', [
            'project' => $project,
        ]);
    }

    public function show(Project $project, $userPermissionGroupId)
    {
        $userPermissionGroup = RequestForVariationUserPermissionGroup::findOrFail($userPermissionGroupId);

        $roles = [
            RequestForVariationUserPermission::ROLE_SUBMIT_RFV                => RequestForVariationUserPermission::ROLE_SUBMIT_RFV_TEXT,
            RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION => RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION_TEXT,
            RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL       => RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL_TEXT,
        ];

        return View::make('request_for_variation.userPermissions.show', [
            'project'             => $project,
            'userPermissionGroup' => $userPermissionGroup,
            'roles'               => $roles
        ]);
    }

    public function create(Project $project)
    {
        $roles = [
            RequestForVariationUserPermission::ROLE_SUBMIT_RFV                => RequestForVariationUserPermission::ROLE_SUBMIT_RFV_TEXT,
            RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION => RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION_TEXT,
            RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL       => RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL_TEXT,
        ];

        return View::make('request_for_variation.userPermissions.create', [
            'userPermissionGroup' => null,
            'roles'               => $roles,
            'project'             => $project,
            'formRoute'           => ['requestForVariation.user.permissions.store', $project->id],
            'selectedRole'        => RequestForVariationUserPermission::ROLE_SUBMIT_RFV
        ]);
    }

    public function store(Project $project)
    {
        $input = Input::all();

        try
        {
            $this->requestForVariationUserPermissionGroupForm->validate($input);

            $userPermissionGroup = new RequestForVariationUserPermissionGroup();
            $userPermissionGroup->name = $input['name'];
            $userPermissionGroup->project_id = $project->id;

            $userPermissionGroup->save();

            if(array_key_exists('user_role', $input))
            {
                $insertData = [];

                $creator = Confide::user();

                foreach($input['user_role'] as $roleId => $formData)
                {
                    foreach($formData as $data)
                    {
                        $insertData[] = [
                            'request_for_variation_user_permission_group_id' => $userPermissionGroup->id,
                            'user_id' => $data['uid'],
                            'module_id' => $roleId,
                            'is_editor' => array_key_exists('ie', $data),
                            'can_view_cost_estimate' => array_key_exists('vce', $data),
                            'can_view_vo_report' => array_key_exists('vvor', $data),
                            'added_by' => $creator->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }

                if(!empty($insertData))
                {
                    RequestForVariationUserPermission::insert($insertData);
                }
            }
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success(trans('requestForVariation.userPermissionGroupSuccessCreated', ['name'=>$input['name']]));

        return Redirect::route('requestForVariation.user.permissions.show', [$project->id, $userPermissionGroup->id]);
    }

    public function edit(Project $project, $userPermissionGroupId)
    {
        $userPermissionGroup = RequestForVariationUserPermissionGroup::findOrFail($userPermissionGroupId);

        $roles = [
            RequestForVariationUserPermission::ROLE_SUBMIT_RFV                => RequestForVariationUserPermission::ROLE_SUBMIT_RFV_TEXT,
            RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION => RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION_TEXT,
            RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL       => RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL_TEXT,
        ];

        return View::make('request_for_variation.userPermissions.create', [
            'userPermissionGroup' => $userPermissionGroup,
            'roles'               => $roles,
            'project'             => $project,
            'formRoute'           => ['requestForVariation.user.permissions.update', $project->id],
            'selectedRole'        => RequestForVariationUserPermission::ROLE_SUBMIT_RFV
        ]);
    }

    public function update(Project $project)
    {
        $input = Input::all();

        $userPermissionGroup = RequestForVariationUserPermissionGroup::findOrFail($input['id']);

        try
        {
            $this->requestForVariationUserPermissionGroupForm->validate($input);

            $userPermissionGroup->name = $input['name'];

            $userPermissionGroup->save();

            $userPermissionGroup->userPermissions()->delete();

            if(array_key_exists('user_role', $input))
            {
                $insertData = [];

                $creator = Confide::user();

                foreach($input['user_role'] as $roleId => $formData)
                {
                    foreach($formData as $data)
                    {
                        $insertData[] = [
                            'request_for_variation_user_permission_group_id' => $userPermissionGroup->id,
                            'user_id' => $data['uid'],
                            'module_id' => $roleId,
                            'is_editor' => array_key_exists('ie', $data),
                            'can_view_cost_estimate' => array_key_exists('vce', $data),
                            'can_view_vo_report' => array_key_exists('vvor', $data),
                            'added_by' => $creator->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }

                if(!empty($insertData))
                {
                    RequestForVariationUserPermission::insert($insertData);
                }
            }
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success(trans('requestForVariation.userPermissionGroupSuccessUpdated', ['name'=>$input['name']]));

        return Redirect::route('requestForVariation.user.permissions.show', [$project->id, $userPermissionGroup->id]);
    }

    public function getAssignableUsers(Project $project)
    {
        return Response::json($this->rfvUserPermissionsRepository->getAssignableUsers($project, Input::all()));
    }

    public function getUserInfo(Project $project)
    {
        $inputs = Input::all();

        $ids = isset($inputs['ids']) ? $inputs['ids'] : [];
        $roleId = $inputs['role'];

        $userPermissionGroup = (isset($inputs['gid']) && !empty($inputs['gid'])) ? RequestForVariationUserPermissionGroup::find($inputs['gid']) : null;

        $rows = [];

        if(!empty($ids))
        {
            $idsOrdered = implode(',', $ids);

            $orderBy[] = "CASE";
            foreach($ids as $index => $id)
            {
                $orderBy[] = " WHEN id=".$id." THEN ".$index." ";
            }

            $orderBy[] = "END";
            
            $users = User::whereIn('id', $ids)
            ->orderByRaw(\DB::raw(implode(" ", $orderBy)))
            ->get();

            $existingUserRoles = [];

            if($userPermissionGroup)
            {
                $dbh=\DB::getPdo();

                $sth = $dbh->prepare("SELECT p.user_id, p.id, p.can_view_cost_estimate, p.can_view_vo_report, p.is_editor
                    FROM request_for_variation_user_permissions p
                    WHERE p.request_for_variation_user_permission_group_id = ".$userPermissionGroup->id."
                    AND p.module_id = ".$roleId."
                    ORDER BY p.user_id"
                );
                
                $sth->execute();
                
                $existingUserRoles = $sth->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_ASSOC|\PDO::FETCH_UNIQUE);
            }
            
            foreach($users as $idx => $user)
            {
                if($user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER))
                {
                    $companyName = $project->subsidiary->name;
                }
                else
                {
                    $companyName = ($company = $user->getAssignedCompany($project)) ? $company->name : "-";
                }

                $userPermissionId = (array_key_exists($user->id, $existingUserRoles)) ? $existingUserRoles[$user->id]['id'] : -1;
                $userPermission = RequestForVariationUserPermission::find($userPermissionId);

                $viewCostEstimateCheck = (array_key_exists($user->id, $existingUserRoles) && $existingUserRoles[$user->id]['can_view_cost_estimate']) ? "checked" : null;
                $viewVOReportCheck = (array_key_exists($user->id, $existingUserRoles) && $existingUserRoles[$user->id]['can_view_vo_report']) ? "checked" : null;
                $isEditorCheck = (array_key_exists($user->id, $existingUserRoles) && $existingUserRoles[$user->id]['is_editor']) ? "checked" : null;

                $rowStr = '<tr id="assignedUser-'.$roleId.'-'.$user->id.'">
                <td class="text-middle text-center text-nowrap">'.($idx+1).'</td>
                <td class="text-middle text-left">'.$user->name.'<br /><div class="companyLabel bg-color-teal">'.$companyName.'</div>
                <input type="hidden" name="user_role['.$roleId.']['.$idx.'][uid]" value='.$user->id.'></td>
                <td class="text-middle text-center text-nowrap">'.$user->email.'</td>
                <td class="text-middle text-center text-nowrap"><input type="checkbox" name="user_role['.$roleId.']['.$idx.'][vce]" value=1 '.$viewCostEstimateCheck.'></td>
                <td class="text-middle text-center text-nowrap"><input type="checkbox" name="user_role['.$roleId.']['.$idx.'][vvor]" value=1 '.$viewVOReportCheck.'></td>';

                if($roleId == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL)
                {
                    $rowStr .= '<td class="text-middle text-center text-nowrap"><input type="checkbox" name="user_role['.$roleId.']['.$idx.'][ie]" value=1 '.$isEditorCheck.'></td>';
                }

                $rowStr .= '<td class="text-middle text-center text-nowrap">';
                if($userPermission)
                {
                    if($userPermission->canDelete())
                    {
                        $rowStr .= '<a href="'.route('requestForVariation.user.permissions.delete', [$project->id, $userPermission->id]).'" data-user-permission-id="'.$userPermission->id.'" data-role-id="'.$roleId.'" data-user-id="'.$user->id.'" class="user_permission-delete btn btn-xs btn-danger">
                        <i class="fa fa-fw fa-lg fa-times"></i>
                        </a>';
                    }
                    else
                    {
                        $rowStr .= '&nbsp;';
                    }
                }
                else
                {
                    $rowStr .= '<a href="#" data-user-permission-id="-1" data-role-id="'.$roleId.'" data-user-id="'.$user->id.'" class="user_permission-delete btn btn-xs btn-danger">
                    <i class="fa fa-fw fa-lg fa-times"></i>
                    </a>';
                }
                
                $rowStr .= '</td></tr>';

                $rows[] = $rowStr;
            }
        }

        return Response::json([
            'rows' => $rows
        ]);
    }

    public function userPermissionDelete(Project $project, $userPermissionId)
    {
        $userPermission = RequestForVariationUserPermission::findOrFail($userPermissionId);

        $success = false;

        if($userPermission->canDelete())
        {
            $userPermission->delete();

            $success = true;
        }

        return Response::json([
            'success' => $success
        ]);
    }

    public function userPermissionGroupDelete(Project $project, $userPermissionGroupId)
    {
        $userPermissionGroup = RequestForVariationUserPermissionGroup::findOrFail($userPermissionGroupId);

        $success = false;

        if($userPermissionGroup->canDelete())
        {
            $userPermissionGroup->delete();

            $success = true;
        }

        return Response::json([
            'success' => $success
        ]);
    }
}


