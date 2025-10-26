<?php

use PCK\Subsidiaries\SubsidiaryRepository;

class ModulePermissionSubsidiariesController extends \BaseController
{
    private $repository;

    public function __construct(SubsidiaryRepository $subsidiaryRepository)
    {
        $this->repository = $subsidiaryRepository;
    }

    public function getSubsidiariesList()
    {
        $subsidiaryIds = $this->repository->getHierarchicalCollection()->lists('full_name', 'id');
        $subsidiariesData = array();
        $counter = 0;

        foreach($subsidiaryIds as $id => $name)
        {
            $subsidiariesData[] = array('id' => $id, 'name' => $name, 'no' => ++$counter);
        }

        return Response::json($subsidiariesData);
    }

    public function getAssignedSubsidiaries()
    {
        $user = \PCK\Users\User::find(Input::get('userId'));
        $moduleId = Input::get('mid');

        $modulePermission = $user->modulePermission($moduleId)->first();
        if (! $modulePermission) {
            return Response::json(array());
        }

        return Response::json($modulePermission->getSubsidiaryIds());
    }

    public function assignSubsidiariesToUser()
    {
        $request = \Request::instance();

        $user = \PCK\Users\User::find($request->input('userId'));
        $moduleId = Input::get('mid');

        $modulePermission = $user->modulePermission($moduleId)->first();
        if (! $modulePermission) {
            return Response::json(array());
        }

        // Update list of subsidiary IDs
        $modulePermission->subsidiaries()->sync($request->get('subsidiaryIds', array()));

        // Retrieve the updated list of subsidiary IDs
        return Response::json($modulePermission->getSubsidiaryIds());
    }

}

