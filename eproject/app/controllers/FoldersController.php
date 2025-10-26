<?php

use PCK\Folder\FileNode;
use PCK\Folder\FileNodePermission;
use PCK\Folder\FileNodeRepository;
use PCK\Users\User;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Forms\FileNodeForm;
use PCK\Forms\FileNodeMoveForm;
use PCK\Forms\FileNodeRepositionForm;
use PCK\Forms\FileNodePermissionForm;
use PCK\Base\Upload;
use PCK\Helpers\DBTransaction;

class FoldersController extends \BaseController {

    protected $fileNodeForm;
    protected $fileNodeMoveForm;
    protected $fileNodeRepository;
    protected $fileNodeRepositionForm;
    protected $fileNodePermissionForm;

    public function __construct(FileNodeForm $fileNodeForm, FileNodeRepository $fileNodeRepository, FileNodeMoveForm $fileNodeMoveForm, FileNodeRepositionForm $fileNodeRepositionForm, FileNodePermissionForm $fileNodePermissionForm)
    {
        $this->fileNodeForm           = $fileNodeForm;
        $this->fileNodeMoveForm       = $fileNodeMoveForm;
        $this->fileNodeRepositionForm = $fileNodeRepositionForm;
        $this->fileNodePermissionForm = $fileNodePermissionForm;
        $this->fileNodeRepository     = $fileNodeRepository;
    }

    public function index($fileNodeId)
    {
        $fileNode = FileNode::find($fileNodeId);

        $ancestors = [
            [
                'name'  => trans("folders.drive"),
                'route' => route('folders', array(0)),
            ]
        ];

        if($fileNode)
        {
            $viewableFileNodeIds = FileNodePermission::getViewableIds(\Confide::user()->id);

            foreach($fileNode->ancestorsAndSelf()->whereIn('id', $viewableFileNodeIds)->get()->sortBy('lft') as $ancestorFileNode)
            {
                $ancestors[] = [
                    'name'  => $ancestorFileNode->name,
                    'route' => route('folders', array($ancestorFileNode->id)),
                ];
            }

            $canEdit = $fileNode->canEdit(\Confide::user());
        }
        else
        {
            $fileNode = new FileNode();
            $fileNode->id = 0;

            $user    = \Confide::user();
            $canEdit = !$user->isCompanyTypeExternal();
        }

        return View::make('folders.index', compact('fileNode', 'ancestors', 'canEdit'));
    }

    public function list($fileNodeId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = \Confide::user();
        $data = [];

        $viewableFileNodeIds = FileNodePermission::getViewableIds($user->id);
        $editableFileNodeIds = FileNodePermission::getEditableIds($user->id);

        $displayedNodeIds = [];

        if(intval($fileNodeId) === 0)
        {
            $allViewableFileNodes = FileNode::whereIn('id', $viewableFileNodeIds)
                ->orderBy('root_id')
                ->orderBy('lft')
                ->orderBy('priority')
                ->get()
                ->groupBy('root_id');

            $fileNodesByRoot = [];

            foreach($allViewableFileNodes as $rootId => $viewableFileNodesInRoot)
            {
                $fileNodesByRoot[$rootId] = [];

                foreach($viewableFileNodesInRoot as $viewableFileNode)
                {
                    $fileNodesByRoot[$rootId][$viewableFileNode['id']] = $viewableFileNode;
                }
            }

            $displayedNodeIds = [];

            $descendantFileNodeIds = [];

            foreach($fileNodesByRoot as $rootId => $fileNodesInCurrentRoot)
            {
                foreach($fileNodesInCurrentRoot as $fileNodeId => $fileNode)
                {
                    if(in_array($fileNodeId, $descendantFileNodeIds)) continue;

                    $displayedNodeIds[] = $fileNodeId;

                    foreach($fileNodesInCurrentRoot as $comparisonFileNodeId => $comparisonFileNode)
                    {
                        if($comparisonFileNode->lft > $fileNode->lft && $comparisonFileNode->rgt < $fileNode->rgt)
                        {
                            $descendantFileNodeIds[] = $comparisonFileNodeId;
                        }
                    }
                }
            }
        }
        else
        {
            $displayedNodeIds = FileNode::where('parent_id', '=', $fileNodeId)
                ->whereIn('id', $viewableFileNodeIds)
                ->lists('id');
        }

        $model = FileNode::with('upload')
            ->whereIn('id', $displayedNodeIds);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('file_nodes.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'description':
                        if(strlen($val) > 0)
                        {
                            $model->where('file_nodes.description', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'type':
                        if(intval($val) === 1)
                        {
                            $model->where('file_nodes.type', '=', FileNode::TYPE_FOLDER);
                        }
                        elseif(intval($val) === 2)
                        {
                            $model->where('file_nodes.type', '=', FileNode::TYPE_FILE);
                        }
                        break;
                }
            }
        }

        $model->orderBy('file_nodes.root_id')
            ->orderBy('lft');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'name'              => $record->name,
                'description'       => $record->description,
                'editable'          => in_array($record->id, $editableFileNodeIds),
                'can_move'          => is_null($record->parent_id) || in_array($record->parent_id, $editableFileNodeIds),
                'is_folder'         => $record->type === FileNode::TYPE_FOLDER,
                'route:next'        => route('folders', array($record->id)),
                'route:delete'      => route('folders.delete', array($record->id)),
                'route:download'    => $record->upload ? $record->upload->download_url : null,
                'route:moveList'    => route('folders.moveOptionList', array($record->id)),
                'route:move'        => route('folders.move', array($record->id)),
                'route:permissions' => route('folders.permissions.list', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function storeOrUpdate($fileNodeId)
    {
        $request = Request::instance();

        $this->fileNodeForm->validate($input = $request->all());

        if($this->fileNodeForm->success)
        {
            if($input['file_node_id'] == -1)
            {
                $parentFileNode = FileNode::find($fileNodeId);

                unset($input['file_node_id']);

                if($parentFileNode)
                {
                    $input['root_id']   = $parentFileNode->root_id;
                    $input['parent_id'] = $parentFileNode->id;

                    $newFileNode = FileNode::create($input);

                    $newFileNode->makeChildOf($parentFileNode);

                    FileNodePermission::copyFileNodePermissions($newFileNode->id, $parentFileNode->id);
                }
                else
                {
                    $newFileNode = new FileNode($input);

                    $newFileNode->priority = $newFileNode->getNewPriority();

                    $newFileNode->save();

                    FileNodePermission::grant($newFileNode->id, \Confide::user()->id, true, true);
                }
            }
            else
            {
                $fileNode = FileNode::find($input['file_node_id']);

                unset($input['file_node_id']);

                $fileNode->update($input);
            }
        }

        return array(
            'success' => $this->fileNodeForm->success,
            'errors'  => $this->fileNodeForm->getErrorMessages(),
        );
    }

    public function delete($fileNodeId)
    {
        $transaction = new DBTransaction;

        $transaction->begin();

        try
        {
            $fileNode = FileNode::find($fileNodeId);
            $fileNode->delete();

            if(is_null($fileNode->parent_id))
            {
                \DB::statement("UPDATE file_nodes SET priority = priority - 1 WHERE parent_id IS NULL AND priority > {$fileNode->priority}");
            }
            else
            {
                \DB::statement("UPDATE file_nodes SET priority = priority - 1 WHERE parent_id = {$fileNode->parent_id} AND priority > {$fileNode->priority}");
            }

            $transaction->commit();

            \Flash::success(trans('forms.deleted'));
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::back();
    }

    public function getAttachments($fileNodeId)
    {
        return [];
    }

    public function upload($fileNodeId)
    {
        $transaction = new DBTransaction;

        $transaction->begin();

        try
        {
            $fileNode = FileNode::find($fileNodeId);

            $uploadedFiles = Upload::whereIn('id', Input::get('uploaded_files') ?? [])->get();

            foreach($uploadedFiles as $uploadedFile)
            {
                $newFileNode = FileNode::create([
                    'root_id'   => $fileNode->root_id ?? null,
                    'type'      => FileNode::TYPE_FILE,
                    'upload_id' => $uploadedFile->id,
                    'name'      => $uploadedFile->original_file_name,
                ]);

                if($fileNode)
                {
                    $newFileNode->makeChildOf($fileNode);

                    FileNodePermission::copyFileNodePermissions($newFileNode->id, $fileNode->id);
                }
                else
                {
                    \DB::statement("UPDATE file_nodes SET priority = {$newFileNode->getNewPriority()} WHERE id = {$newFileNode->id}");

                    FileNodePermission::grant($newFileNode->id, \Confide::user()->id, true, true);
                }
            }

            $transaction->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            $success = false;

            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        return array(
            'success' => $success,
        );
    }

    public function reposition($fileNodeId)
    {
        $success = false;

        $transaction = new DBTransaction;

        $transaction->begin();

        try
        {
            $input = Input::all();

            $input['parent_id'] = $fileNodeId;

            $this->fileNodeRepositionForm->validate($input);

            if($this->fileNodeRepositionForm->success)
            {
                $movingNode = FileNode::find(Input::get('node_id'));

                if(empty($newPreviousNodeId = Input::get('new_previous_node_id')))
                {
                    // Moving to first row.
                    $firstSiblingNode = $movingNode->siblings()->first();

                    $movingNode->moveToLeftOf($firstSiblingNode);
                }
                else
                {
                    $newPreviousNode = FileNode::find($newPreviousNodeId);

                    $movingNode->moveToRightOf($newPreviousNode);
                }

                $transaction->commit();

                $success = true;
            }
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error($e->getMessage());
        }

        return array(
            'success' => $success,
            'errors'  => $this->fileNodeRepositionForm->getErrorMessages(),
        );
    }

    public function moveOptionList($fileNodeId)
    {
        $data = [];

        $availableFileNodeIds = FileNodePermission::getEditableIds(\Confide::user()->id);

        $data = $this->fileNodeRepository->getTabulatorHierarchicalStructure($availableFileNodeIds, [FileNode::TYPE_FOLDER], $fileNodeId);

        return Response::json($data);
    }

    public function move($folderId)
    {
        $movingNode = FileNode::find($folderId);

        if(intval(Input::get('target_node_id')) == intval($movingNode->parent_id))
        {
            return array(
                'success' => true,
                'errors'  => null,
            );
        }

        $transaction = new DBTransaction;

        $transaction->begin();

        $input = Input::get();

        $input['moving_node_id'] = $movingNode->id;

        $this->fileNodeMoveForm->validate($input);

        try
        {
            if($this->fileNodeMoveForm->success)
            {
                if($input['target_node_id'] == 0)
                {
                    // Target is global space.
                    $movingNode->makeRoot();
                }
                else
                {
                    $targetNode = FileNode::find($input['target_node_id']);

                    $movingNode->makeChildOf($targetNode);

                    FileNodePermission::copyFileNodePermissions($movingNode->id, $targetNode->id);
                }

                $transaction->commit();
            }
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error($e->getMessage());
        }

        return array(
            'success' => $this->fileNodeMoveForm->success,
            'errors'  => $this->fileNodeMoveForm->getErrorMessages(),
        );
    }

    public function permissionsList($folderId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = User::select('users.id', 'users.name', 'users.username', 'companies.name as company_name', 'contract_group_categories.type as contract_group_category_type')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->leftJoin('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where('users.confirmed', '=', true)
            ->where('users.is_super_admin', '=', false);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

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
                    case 'viewer':
                        $userIds = FileNodePermission::getViewerIds($folderId);
                        if($val === "true")
                        {
                            $model->whereIn('users.id', $userIds);
                        }
                        elseif($val === "false")
                        {
                            $model->whereNotIn('users.id', $userIds);
                        }
                        break;
                    case 'editor':
                        $userIds = FileNodePermission::getEditorIds($folderId);
                        if($val === "true")
                        {
                            $model->whereIn('users.id', $userIds);
                        }
                        elseif($val === "false")
                        {
                            $model->whereNotIn('users.id', $userIds);
                        }
                        break;
                }
            }
        }

        $model->orderBy('users.name');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $viewerIds = FileNodePermission::getViewerIds($folderId);
        $editorIds = FileNodePermission::getEditorIds($folderId);

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'username'      => $record->username,
                'name'          => $record->name,
                'company'       => $record->company_name,
                'is_viewer'     => in_array($record->id, $viewerIds),
                'is_editor'     => in_array($record->id, $editorIds),
                'can_be_editor' => $record->contract_group_category_type != ContractGroupCategory::TYPE_EXTERNAL,
                'route:update'  => route('folders.permissions.update', array($folderId, $record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function updatePermission($folderId, $userId)
    {
        $applyToSubfolders = Input::get('apply_to_subfolders') === 'true';
        $setEditor         = Input::get('set_editor') === 'true';
        $grant             = Input::get('grant') === 'true';

        $this->fileNodePermissionForm->validate([
            'file_node_id' => $folderId,
            'user_id'      => $userId,
            'set_editor'   => $setEditor,
            'grant'        => $grant,
        ]);

        if($this->fileNodePermissionForm->success)
        {
            if($grant) $applyToSubfolders = true;

            FileNodePermission::updatePermissions($folderId, $userId, $applyToSubfolders, $setEditor, $grant);
        }

        return array(
            'success' => $this->fileNodePermissionForm->success,
            'errors'  => $this->fileNodePermissionForm->getErrorMessages(),
        );
    }

    public function overviewList()
    {
        ini_set('memory_limit','2048M');

        $data = [];

        $availableFileNodeIds = FileNodePermission::getViewableIds(\Confide::user()->id);

        $data = $this->fileNodeRepository->getTabulatorHierarchicalStructure($availableFileNodeIds, [FileNode::TYPE_FOLDER, FileNode::TYPE_FILE], 0);

        return Response::json($data);
    }
}
