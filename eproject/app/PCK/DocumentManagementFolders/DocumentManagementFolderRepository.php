<?php namespace PCK\DocumentManagementFolders;

use DB;
use PCK\Helpers\Hierarchy\AdjacencyListsAndNestedSets;
use PCK\Helpers\Hierarchy\NestedSetNode;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;

class DocumentManagementFolderRepository extends BaseModuleRepository {

    protected $folder;

    protected $events;

    public function __construct(DocumentManagementFolder $folder, Dispatcher $events)
    {
        $this->folder = $folder;
        $this->events = $events;
    }

    public function sendUploadedFileNotification(DocumentManagementFolder $folder)
    {
        $project  = $folder->project;
        $viewName = 'project_document_new_upload';
        $route    = 'projectDocument.mySharedFolder';

        $this->sendProjectDocumentNotification($project, $folder, $viewName, $route);
    }

    public function sendDeleteFolderNotification(DocumentManagementFolder $folder)
    {
        $project  = $folder->project;
        $viewName = 'project_document_folder_delete';
        $route    = 'projectDocument.index';

        $this->sendProjectDocumentNotification($project, $folder, $viewName, $route, true);
    }

    private function sendProjectDocumentNotification(Project $project, DocumentManagementFolder $model, $viewName, $routeName, $returnToParentRoot = false)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendProjectDocumentSystemNotificationToSelectedGroupUsers', compact(
            'project', 'model', 'viewName', 'routeName', 'returnToParentRoot'
        ));
    }

    /**
     * Saves the new folder positions.
     *
     * @param $folders
     *
     * @return bool
     */
    public function saveNewFolderPositions($folders)
    {
        try
        {
            $this->updateFolderPositions($folders);
        }
        catch(\Exception $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Updates the folder positions in one transaction.
     *
     * @param $folders
     *
     * @return bool
     */
    public function updateFolderPositions($folders)
    {
        DB::transaction(function() use ($folders)
        {
            foreach($folders as $folder)
            {
                DB::table(with(new DocumentManagementFolder)->getTable())
                    ->where('id', '=', $folder['id'])
                    ->update(array(
                        'lft'       => $folder['lft'],
                        'rgt'       => $folder['rgt'],
                        'parent_id' => $folder['parentId'],
                        'depth'     => $folder['depth']
                    ));
            }
        });

        return true;
    }

    /**
     * Gets the folder count,
     * i.e. the number of files in each folder.
     *
     * @param $sharedFolders
     * @param $descendants
     *
     * @return array
     */
    public function getFolderCount($sharedFolders, $descendants)
    {
        $folderToCount = array();
        if( count($sharedFolders) )
        {
            $sharedFolderIds = array();

            foreach($sharedFolders as $k => $folders)
            {
                foreach($folders as $c => $folder)
                {
                    $sharedFolderIds[] = $folder['id'];
                }
            }

            if( count($sharedFolderIds) )
            {
                $projectDocumentFileObj = new ProjectDocumentFile();

                $fileCount = DB::table($projectDocumentFileObj->getTable() . ' AS f')
                    ->select("f.project_document_folder_id", DB::raw("count(f.id) AS file_count"))
                    ->whereRaw('f.project_document_folder_id IN (' . implode(',', $sharedFolderIds) . ')')
                    ->groupBy('f.project_document_folder_id')
                    ->get();

                foreach($fileCount as $count)
                {
                    $folderToCount[ $count->project_document_folder_id ] = $count->file_count;
                }
            }
        }

        if( count($descendants) )
        {
            $descendantIds = array();

            foreach($descendants as $k => $descendant)
            {
                $descendantIds[] = $descendant['id'];
            }

            if( count($descendantIds) )
            {
                $projectDocumentFileObj = new ProjectDocumentFile();

                $fileCount = DB::table($projectDocumentFileObj->getTable() . ' AS f')
                    ->select("f.project_document_folder_id", DB::raw("count(f.id) AS file_count"))
                    ->whereRaw('f.project_document_folder_id IN (' . implode(',', $descendantIds) . ')')
                    ->groupBy('f.project_document_folder_id')
                    ->get();

                foreach($fileCount as $count)
                {
                    $folderToCount[ $count->project_document_folder_id ] = $count->file_count;
                }
            }
        }

        return $folderToCount;
    }

    /**
     * Returns an array of nested set nodes.
     *
     * @param $descendants
     *
     * @return array
     */
    public function getNestedSetArray($descendants)
    {
        $nestedSetArray = array();
        foreach($descendants as $descendant)
        {
            $nestedSetItem = new NestedSetNode($descendant['id'], $descendant['root_id'], $descendant['parent_id'], $descendant['lft'], $descendant['rgt'], $descendant['depth']);
            $nestedSetItem->setData(array(
                'folderName' => $descendant['name'],
                'projectId'  => $descendant['project_id'],
                'shared'     => isset( $descendant['been_shared'] ) ? $descendant['been_shared'] : false,
                'lft'        => $descendant['lft'],
                'group_id'   => $descendant['contract_group_id'],
            ));
            array_push($nestedSetArray, $nestedSetItem);
        }

        return $nestedSetArray;
    }

    /**
     * Get an array of the folder ids.
     *
     * @param array $folders
     *
     * @return array
     */
    public function getFolderIds(array $folders)
    {
        $folderIds = array();
        foreach($folders as $folder)
        {
            array_push($folderIds, $folder['id']);
        }

        return $folderIds;
    }

    /**
     * Get the start index for the nested sets of the folders belonging to the group.
     *
     * @param $rootId
     * @param $contractGroup
     *
     * @return mixed
     */
    public function getFolderStartIndexByGroup($rootId, $contractGroup)
    {
        $groupFolderWithSmallestLft = DocumentManagementFolder::where('root_id', '=', $rootId)
            ->where('contract_group_id', '=', $contractGroup->id)
            ->orderBy('lft', 'asc')
            ->first();

        return $groupFolderWithSmallestLft->lft;
    }

    /**
     * Update the root folder's right boundary.
     * This is to remove empty spaces in the folder's set that is left when a folder is deleted.
     *
     * @param $rootFolderId
     */
    public function updateRootFolderRgt($rootFolderId)
    {
        $rootFolder = DocumentManagementFolder::find($rootFolderId);

        $tableName          = with(new DocumentManagementFolder)->getTable();
        $subFoldersEndIndex = DB::select(DB::raw('SELECT max(rgt) FROM ' . $tableName . ' WHERE root_id = ? and rgt < (SELECT max(rgt) FROM ' . $tableName . ' where root_id = ?)'), array( $rootFolderId, $rootFolderId ))[0]->max;
        $rootFolder->rgt    = ( $subFoldersEndIndex + 1 );
        $rootFolder->save();
    }

    /**
     * Repositions/Rearranges all folders under the root by groups
     * i.e. all group 1 folders will be in one subset etc.
     *
     * @param $rootId
     *
     * @return bool
     */
    public function repositionFoldersByRootId($rootId)
    {
        // Sorts records (excluding the root folder) by contract_group_id
        // The results of this query must already be sorted in the desired order.
        $allFolders = DocumentManagementFolder::where('root_id', '=', $rootId)
            ->where('id', '!=', $rootId)
            ->orderBy('contract_group_id', 'asc')
            ->orderBy('lft', 'asc')
            ->get();

        $rootFolder = DocumentManagementFolder::find($rootId);

        $converter = new AdjacencyListsAndNestedSets();

        $nestedSetArray = $this->getNestedSetArray($allFolders);
        $converter->setNestedSet($rootId, $nestedSetArray);

        // Records are already sorted by the query, in the desired order, so we convert
        // them to an adjacency list and back to nested set to update the record data (lft, rgt etc.).
        $allFoldersInAdjacencyList = $converter->convertNestedSetToAdjacencyList();
        $converter->setAdjacencyList($allFoldersInAdjacencyList);

        $subFolderStartIndex = ( $rootFolder->lft + 1 );
        $allFolders          = $converter->convertAdjacencyListToNestedSet($rootId, $subFolderStartIndex);

        $newFolderPositionsSaved = $this->saveNewFolderPositions($allFolders);

        $rootFolder->rgt = ( DocumentManagementFolder::where('root_id', '=', $rootId)->where('id', '!=', $rootId)->max('rgt') + 1 );

        $rootFolderSaved = $rootFolder->save();

        return ( $newFolderPositionsSaved && $rootFolderSaved );
    }

}