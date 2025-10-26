<?php

use PCK\Users\User;
use PCK\VendorManagement\VendorManagementUserPermission;
use Carbon\Carbon;
use PCK\Tag\Tag;
use PCK\Tag\ObjectTag;

class VendorManagementUsersController extends \BaseController
{
    public function index()
    {
        $permissionsList = VendorManagementUserPermission::getHeaders();

        return View::make('vendor_management.users.index', compact('permissionsList'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = User::select('users.id', 'users.name', 'users.username', 'companies.name as company_name')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->where('users.confirmed', '=', true);

        if($request->has('filters'))
        {
            $permissionTypes = VendorManagementUserPermission::getAllTypes();

            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value'])) continue;

                if(is_array($filters['value']))
                {
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'tags':
                            $userIds = ObjectTag::whereIn('tag_id', $filters['value'])
                                ->where('object_class', '=', get_class(new User))
                                ->lists('object_id');

                            $model->whereIn('users.id', $userIds);
                            break;
                    }
                }
                else
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'name':
                            if(strlen($val) > 0)
                            {
                                $model->where('users.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'username':
                            if(strlen($val) > 0)
                            {
                                $model->where('users.username', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'company':
                            if(strlen($val) > 0)
                            {
                                $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        default:
                            $fieldPermissionType = substr(trim(strtolower($filters['field'])), strlen("permission_type_"));

                            if(in_array($fieldPermissionType, $permissionTypes))
                            {
                                $userIds = VendorManagementUserPermission::where('type', '=', $fieldPermissionType)
                                    ->lists('user_id');

                                if($val === "true")
                                {
                                    $model->whereIn('users.id', $userIds);
                                }
                                elseif($val === "false")
                                {
                                    $model->whereNotIn('users.id', $userIds);
                                }
                            }
                    }
                }
            }
        }

        $model->orderBy('users.name');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $userPermissions = VendorManagementUserPermission::getUserPermissions();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $objectTagNames = ObjectTag::getTagNames($record, Tag::CATEGORY_VENDOR_MANAGEMENT_USERS);

            $row = [
                'id'            => $record->id,
                'counter'       => $counter,
                'username'      => $record->username,
                'name'          => $record->name,
                'company'       => $record->company_name,
                'tagsArray'     => $objectTagNames,
                'route:update'  => route('vendorManagement.users.updatePermission', array($record->id)),
            ];

            foreach(VendorManagementUserPermission::getAllTypes() as $type)
            {
                $row["permission_type_{$type}"] = in_array($type, $userPermissions[$record->id] ?? []);
            }

            $data[] = $row;
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function permissionsList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $permissionTypes = VendorManagementUserPermission::getTypeNames();

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $permissionTypes = array_filter($permissionTypes, function($value) use ($val) {
                                return str_contains(strtolower($value), strtolower($val));
                            });
                        }
                        break;
                }
            }
        }

        asort($permissionTypes);

        $rowCount = count($permissionTypes);

        $records = array_slice($permissionTypes, $limit * ($page - 1), $limit, true);

        $data = [];

        $count = 0;

        foreach($records as $permissionType => $permissionTypeName)
        {
            $counter = ($page-1) * $limit + $count + 1;
            $count++;

            $row = [
                'id'      => $permissionType,
                'counter' => $counter,
                'name'    => $permissionTypeName,
            ];

            $data[] = $row;
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function updatePermission($userId)
    {
        VendorManagementUserPermission::updatePermissions([$userId], [Input::get('type')], Input::get('grant') === 'true');

        $success = true;

        return array(
            'success' => $success,
        );
    }

    public function updatePermissions()
    {
        VendorManagementUserPermission::updatePermissions(Input::get('user_ids'), [Input::get('type')], Input::get('grant') === 'true');

        $success = true;

        return array(
            'success' => $success,
        );
    }

    public function batchUpdatePermissions()
    {
        $grant = Input::get('grant') === 'true';
        $userIds = Input::get('user_ids') ?? [];

        VendorManagementUserPermission::updatePermissions($userIds, Input::get('types') ?? [], $grant);

        $users = User::whereIn('id', $userIds)->get();

        if($grant)
        {
            foreach($users as $user)
            {
                ObjectTag::addTags($user, Tag::CATEGORY_VENDOR_MANAGEMENT_USERS, Input::get('tags') ?? []);
            }
        }
        else
        {
            foreach($users as $user)
            {
                ObjectTag::removeTags($user, Tag::CATEGORY_VENDOR_MANAGEMENT_USERS, Input::get('tags') ?? []);
            }
        }

        $success = true;

        return array(
            'success' => $success,
        );
    }

    public function getTagList()
    {
        $records = Tag::where('category', '=', Tag::CATEGORY_VENDOR_MANAGEMENT_USERS)
            ->orderBy('name', 'asc')
            ->lists('name', 'id');

        $data = [];

        foreach($records as $id => $name)
        {
            $data[] = [
                'id'   => $id,
                'text' => $name,
            ];
        }

        return $data;
    }
}