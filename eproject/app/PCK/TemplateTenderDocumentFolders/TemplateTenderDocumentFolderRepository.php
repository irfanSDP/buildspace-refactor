<?php namespace PCK\TemplateTenderDocumentFolders;

use DB;
use PCK\Helpers\Hierarchy\AdjacencyListsAndNestedSets;
use PCK\Helpers\Hierarchy\NestedSetNode;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;

class TemplateTenderDocumentFolderRepository extends BaseModuleRepository {

    protected $folder;

    protected $events;

    public function __construct(TemplateTenderDocumentFolder $folder, Dispatcher $events)
    {
        $this->folder = $folder;
        $this->events = $events;
    }

    public function find($folderId)
    {
        return $this->folder->findOrFail($folderId);
    }

    public function repositionFolders($foldersJson, int $rootId)
    {
        $converter = new AdjacencyListsAndNestedSets();
        $converter->setAdjacencyList($foldersJson);

        $root = TemplateTenderDocumentFolder::getRootFolder($rootId);

        if(!$root)
        {
            return false;
        }

        $folderStartIndex = $this->getFolderStartIndex($root);
        $folders          = $converter->convertAdjacencyListToNestedSet($root->id, $folderStartIndex);

        if( ! $this->updateFolderPositions($folders) ) return false;

        return $this->updateRootFolderRgt($root->id);
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
                DB::table(with(new TemplateTenderDocumentFolder)->getTable())
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
                'lft'        => $descendant['lft'],
                'fileCount'  => $descendant->files->count(),
            ));
            array_push($nestedSetArray, $nestedSetItem);
        }

        return $nestedSetArray;
    }

    /**
     * Get the start index for the nested sets of the folders under the root.
     *
     * @param $root
     *
     * @return mixed
     */
    public function getFolderStartIndex($root)
    {
        $descendantWithSmallestLft = TemplateTenderDocumentFolder::where('root_id', '=', $root->id)
            ->whereNotNull('parent_id')
            ->orderBy('lft', 'asc')
            ->first();

        if( ! $descendantWithSmallestLft ) return ( $root->lft + 1 );

        return $descendantWithSmallestLft->lft;
    }

    /**
     * Update the root folder's right boundary.
     * This is to remove empty spaces in the folder's set that is left when a folder is deleted.
     *
     * @param $rootFolderId
     *
     * @return bool
     */
    public function updateRootFolderRgt($rootFolderId)
    {
        $rootFolder      = TemplateTenderDocumentFolder::find($rootFolderId);
        $rootFolder->rgt = ( TemplateTenderDocumentFolder::where('root_id', '=', $rootFolderId)->where('id', '!=', $rootFolderId)->max('rgt') + 1 );

        return $rootFolder->save();
    }

    public function syncWorkCategories(int $rootId, array $workCategoryIds)
    {
        if( ! $root = TemplateTenderDocumentFolder::getRootFolder($rootId) ) return false;

        foreach($workCategoryIds as $key => $workCategoryId)
        {
            // Unavailable work categories are ignored.
            if( ! in_array($workCategoryId, $root->getAvailableWorkCategories()->lists('id')) ) unset( $workCategoryIds[ $key ] );
        }

        return $root->workCategories()->sync($workCategoryIds);
    }

    public function createNewSet()
    {
        $rootFolder = TemplateTenderDocumentFolder::create(array(
            'root_id' => 0,
            'depth'   => 0,
            'name'    => TemplateTenderDocumentFolder::ROOT_NAME,
        ));

        $rootFolder->update(array( 'root_id' => $rootFolder->id ));

        return $rootFolder;
    }

    public function deleteSet(int $rootId)
    {
        $root = TemplateTenderDocumentFolder::getRootFolder($rootId);

        if(!$root)
        {
            return false;
        }

        $root->workCategories()->sync(array());

        return $root->delete();
    }

}