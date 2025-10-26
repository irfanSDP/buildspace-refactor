<?php namespace PCK\Folder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Users\User;
use PCK\ContractGroupCategory\ContractGroupCategory;

class FileNodePermission extends Model {

    use SoftDeletingTrait;

    protected $table = 'file_node_permissions';

    public static function updatePermissions($fileNodeId, $userId, $applyToDescendants, $setEditor, $grant)
    {
        if($grant)
        {
            self::grant($fileNodeId, $userId, $applyToDescendants, $setEditor);
        }
        else
        {
            self::revoke($fileNodeId, $userId, $applyToDescendants, $setEditor);
        }
    }

    public static function grant($fileNodeId, $userId, $applyToDescendants, $setEditor)
    {
        $currentUser = \Confide::user();
        $timestamp   = \Carbon\Carbon::now();

        $node = FileNode::find($fileNodeId);

        $relevantNodeIds = [$fileNodeId];

        if($applyToDescendants)
        {
            $relevantNodeIds = $node->descendantsAndSelf()->lists('id');
        }

        $viewableFileNodeIds = self::getViewableIds($userId);

        $idsToAssign = array_diff($relevantNodeIds, $viewableFileNodeIds);

        foreach($idsToAssign as $nodeId)
        {
            $rows[] = [
                'file_node_id' => $nodeId,
                'user_id'      => $userId,
                'created_at'   => $timestamp,
                'updated_at'   => $timestamp,
                'created_by'   => $currentUser->id,
                'updated_by'   => $currentUser->id,
                'is_editor'    => $setEditor,
            ];
        }

        if(!empty($rows)) \DB::table('file_node_permissions')->insert($rows);

        if($setEditor)
        {
            $idsToUpdate = array_diff($relevantNodeIds, $idsToAssign);

            if(!empty($idsToUpdate))
            {
                \DB::statement("UPDATE file_node_permissions
                    SET updated_at = '{$timestamp}', updated_by = {$currentUser->id}, is_editor = true
                    WHERE file_node_id IN (" . implode(',', $idsToUpdate) . ")
                    AND deleted_at IS NULL");
            }
        }
    }

    public static function revoke($fileNodeId, $userId, $applyToDescendants, $setEditor)
    {
        $currentUser = \Confide::user();
        $timestamp   = \Carbon\Carbon::now();

        $node = FileNode::find($fileNodeId);

        $relevantNodeIds = [$fileNodeId];

        if($applyToDescendants)
        {
            $relevantNodeIds = $node->descendantsAndSelf()->lists('id');
        }

        if($setEditor)
        {
            FileNodePermission::whereIn('file_node_id', $relevantNodeIds)
                ->where('user_id', '=', $userId)
                ->update([
                    'updated_at' => $timestamp,
                    'updated_by' => $currentUser->id,
                    'is_editor'  => false,
                ]);
        }
        else
        {
            FileNodePermission::whereIn('file_node_id', $relevantNodeIds)
                ->where('user_id', '=', $userId)
                ->update([
                    'updated_at' => $timestamp,
                    'updated_by' => $currentUser->id,
                    'deleted_at' => $timestamp,
                ]);
        }
    }

    /*
    * Will only add new permissions; will not remove existing permissions.
    */
    public static function copyFileNodePermissions($fileNodeId, $templateFileNodeId)
    {
        $viewerIds = self::getViewerIds($templateFileNodeId);
        $editorIds = self::getEditorIds($templateFileNodeId);

        $existingViewerIds = self::getViewerIds($fileNodeId);
        $existingEditorIds = self::getEditorIds($fileNodeId);

        $currentUser = \Confide::user();
        $timestamp   = \Carbon\Carbon::now();

        $editorIds   = array_diff($editorIds, $existingEditorIds);
        $viewOnlyIds = array_diff($viewerIds, $editorIds, $existingViewerIds);

        $superAdminIds = User::getSuperAdminIds();

        $rows = [];

        foreach($editorIds as $editorId)
        {
            if(in_array($editorId, $superAdminIds)) continue;

            $rows[] = [
                'file_node_id' => $fileNodeId,
                'user_id'      => $editorId,
                'created_at'   => $timestamp,
                'updated_at'   => $timestamp,
                'created_by'   => $currentUser->id,
                'updated_by'   => $currentUser->id,
                'is_editor'    => true,
            ];
        }

        foreach($viewOnlyIds as $viewerId)
        {
            if(in_array($viewerId, $superAdminIds)) continue;

            $rows[] = [
                'file_node_id' => $fileNodeId,
                'user_id'      => $viewerId,
                'created_at'   => $timestamp,
                'updated_at'   => $timestamp,
                'created_by'   => $currentUser->id,
                'updated_by'   => $currentUser->id,
                'is_editor'    => false,
            ];
        }

        if(!empty($rows)) \DB::table('file_node_permissions')->insert($rows);
    }

    public static function getViewerIds($fileNodeId)
    {
        $ids = self::where('file_node_id', '=', $fileNodeId)
            ->lists('user_id');

        return array_merge($ids, User::where('is_super_admin', '=', true)->lists('id'));
    }

    public static function getEditorIds($fileNodeId)
    {
        $ids = self::where('file_node_id', '=', $fileNodeId)
            ->join('users', 'users.id', '=', 'file_node_permissions.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->leftJoin('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where(function($query){
                $query->where('contract_group_categories.type', '!=', ContractGroupCategory::TYPE_EXTERNAL);
                $query->orWhereNull('contract_group_categories.type');
            })
            ->where('is_editor', '=', true)
            ->lists('user_id');

        return array_merge($ids, User::where('is_super_admin', '=', true)->lists('id'));
    }

    public static function getEditableIds($userId)
    {
        if(User::find($userId)->is_super_admin) return self::lists('file_node_id', 'file_node_id');

        return self::where('user_id', '=', $userId)
            ->join('users', 'users.id', '=', 'file_node_permissions.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->leftJoin('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where(function($query){
                $query->where('contract_group_categories.type', '!=', ContractGroupCategory::TYPE_EXTERNAL);
                $query->orWhereNull('contract_group_categories.type');
            })
            ->where('is_editor', '=', true)
            ->lists('file_node_id', 'file_node_id');
    }

    public static function getViewableIds($userId)
    {
        if(User::find($userId)->is_super_admin) return self::lists('file_node_id', 'file_node_id');

        return self::where('user_id', '=', $userId)
            ->lists('file_node_id', 'file_node_id');
    }

}