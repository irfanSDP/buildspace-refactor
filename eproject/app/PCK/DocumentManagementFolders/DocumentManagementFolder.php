<?php namespace PCK\DocumentManagementFolders;

use Baum\Node;
use PCK\Projects\Project;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractGroups\ContractGroup;
use PCK\Users\User;

class DocumentManagementFolder extends Node {

    use TimestampFormatterTrait;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'document_management_folders';

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $node)
        {
            if( $node->depth == 0 && is_null($node->parent_id) )
            {
                $node->root_id = $node->id;
                $node->lft     = 1;
                $node->rgt     = 2;

                $node->save();
            }
        });

        static::deleting(function(self $node)
        {
            if( $node->isRoot() )
            {
                $repo = \App::make('PCK\DocumentManagementFolders\DocumentManagementFolderRepository');

                $repo->repositionFoldersByRootId($node->id);
            }

            $node->augmentedDestroyDescendants();

            // remove shared folder to groups
            $node->contractGroups()->detach();

            foreach($node->files as $file)
            {
                $file->delete();
            }
        });
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function files()
    {
        return $this->hasMany('PCK\DocumentManagementFolders\ProjectDocumentFile', 'project_document_folder_id')->orderBy('id', 'desc');
    }

    public function getUploadedFiles()
    {
        return $this->hasMany('PCK\DocumentManagementFolders\ProjectDocumentFile', 'project_document_folder_id')->orderBy('created_at', 'DESC');
    }

    public function contractGroup()
    {
        return $this->belongsTo('PCK\ContractGroups\ContractGroup')->withTimestamps();
    }

    public function contractGroups()
    {
        return $this->belongsToMany('PCK\ContractGroups\ContractGroup')->withTimestamps();
    }

    /**
     * Static query scope. Returns a query scope with all root nodes.
     *
     * @param Project $project
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getRootsByProject(Project $project)
    {
        $instance = new static;

        return $instance
            ->whereNull($instance->getParentColumnName())
            ->where($instance->getDepthColumnName(), '=', 0)
            ->whereRaw('root_id = id')
            ->where('project_id', '=', $project->id)
            ->orderBy('priority')
            ->get();
    }

    public function ancestorsAndSelf()
    {
        return $this->newNestedSetQuery()
            ->where($this->getLeftColumnName(), '<=', $this->getLeft())
            ->where($this->getRightColumnName(), '>=', $this->getRight())
            ->where('root_id', '=', $this->root_id)
            ->where('project_id', '=', $this->project_id)
            ->where('folder_type', '=', $this->folder_type);
    }

    public function descendantsAndSelf()
    {
        return $this->newNestedSetQuery()
            ->where($this->getLeftColumnName(), '>=', $this->getLeft())
            ->where($this->getLeftColumnName(), '<', $this->getRight())
            ->where('root_id', '=', $this->root_id)
            ->where('project_id', '=', $this->project_id)
            ->where('folder_type', '=', $this->folder_type);
    }

    public function getDescendantsByContractGroup(ContractGroup $contractGroup)
    {
        $results = \DB::select(\DB::raw("SELECT DISTINCT p.* FROM " . $this->getTable() . " i
            JOIN " . $this->getTable() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND i.root_id = p.root_id)
            WHERE i.root_id = " . $this->root_id . " AND i.project_id = " . $this->project_id . " AND i.folder_type = " . $this->folder_type . "
            AND i.root_id = p.root_id AND i.project_id = p.project_id AND i.folder_type = p.folder_type AND i.contract_group_id = :contractGroupId
            AND p.depth > 0
            ORDER BY p.priority, p.lft, p.depth"), array(
            'contractGroupId' => $contractGroup->id,
        ));

        return json_decode(json_encode($results), true);
    }

    public static function getSharedFoldersByContractGroup(ContractGroup $contractGroup, Project $project, $folderType)
    {
        $instance = new static;

        $results = \DB::select(\DB::raw("SELECT DISTINCT p.*, p.depth - 1 AS depth FROM " . $instance->getTable() . " i
		JOIN " . $instance->getTable() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND i.root_id = p.root_id)
		JOIN contract_group_document_management_folder xref ON i.id = xref.document_management_folder_id
		WHERE i.contract_group_id != :contractGroupId AND xref.contract_group_id = :contractGroupId
		AND i.project_id = :projectId AND i.folder_type = :folderType
		AND i.root_id = p.root_id AND i.project_id = p.project_id AND i.folder_type = p.folder_type
		AND p.depth > 0
		ORDER BY p.contract_group_id, p.priority, p.lft, p.depth"), array(
            'contractGroupId' => $contractGroup->id,
            'projectId'       => $project->id,
            'folderType'      => $folderType
        ));

        $pivotResults = \DB::table('contract_group_document_management_folder AS xref')
            ->join($instance->getTable() . " AS f", 'f.id', '=', 'xref.document_management_folder_id')
            ->select('xref.id', 'f.id AS folder_id')
            ->where('f.project_id', '=', $project->id)
            ->where('f.folder_type', '=', $folderType)
            ->where('f.contract_group_id', '<>', $contractGroup->id)
            ->get();

        $sharedFolderIds = array();

        foreach($pivotResults as $record)
        {
            $sharedFolderIds[ $record->folder_id ] = $record->folder_id;
        }

        unset( $pivotResults );

        $records         = array();
        $contractGroupId = -1;

        foreach($results as $result)
        {
            if( array_key_exists($result->id, $sharedFolderIds) )
            {
                $result->isShared = true;

                unset( $sharedFolderIds[ $result->id ] );
            }
            else
            {
                $result->isShared = false;
            }

            if( $contractGroupId != $result->contract_group_id )
            {
                $contractGroupId             = $result->contract_group_id;
                $records[ $contractGroupId ] = array();
            }

            $records[ $contractGroupId ][] = $result;
        }

        return json_decode(json_encode($records), true);
    }

    public function getSharedChildrenByContractGroup(ContractGroup $contractGroup)
    {
        $instance = new static;

        return $instance
            ->select($this->getTable() . '.*')
            ->join('contract_group_document_management_folder AS xref', $this->getTable() . '.id', '=', 'xref.document_management_folder_id')
            ->where($instance->getParentColumnName(), '=', $this->id)
            ->where('xref.contract_group_id', '=', $contractGroup->id)
            ->where($this->getTable() . '.root_id', '=', $this->root_id)
            ->where($this->getTable() . '.project_id', '=', $this->project_id)
            ->orderBy($this->getLeftColumnName())
            ->get();
    }

    public function shareToContractGroups(Array $contractGroupList)
    {
        $this->contractGroups()->sync($contractGroupList);

        foreach($this->getDescendants() as $descendant)
        {
            $descendant->contractGroups()->sync($contractGroupList);
        }
    }

    public function belongsToContractGroup(ContractGroup $contractGroup)
    {
        return ( $contractGroup->id == $this->contract_group_id );
    }

    public function isSharedForContractGroup(ContractGroup $contractGroup)
    {
        return ( \DB::table('contract_group_document_management_folder AS xref')
                ->select('xref.id')
                ->where('xref.document_management_folder_id', '=', $this->id)
                ->where('xref.contract_group_id', '=', $contractGroup->id)
                ->count() > 0 ) ? true : false;
    }

    protected function moveTo($target, $position)
    {
        return DocumentManagementFolderMove::to($this, $target, $position);
    }

    // Overwrite the normal destroyDescendants function to prevent BAUM's model deleting from amending tree structure
    public function destroyDescendants()
    {
        return false;
    }

    /**
     * Prunes a branch off the tree, shifting all the elements on the right
     * back to the left so the counts work.
     *
     * @return void;
     */
    public function augmentedDestroyDescendants()
    {
        if( is_null($this->getRight()) || is_null($this->getLeft()) )
        {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function() use ($self)
        {
            $self->reload();

            $self->applyLock();

            $self->pruneChildren();

            $self->recalculateBoundaries();
        });
    }

    /**
     * Apply a lock to the rows which fall past the deletion point.
     */
    public function applyLock()
    {
        list( $lftCol, $rgtCol, $lft, $rgt ) = $this->getRightAndLeftParams();

        $this->newNestedSetQuery()
            ->where($lftCol, '>=', $lft)
            ->where('root_id', '=', $this->root_id)
            ->where('project_id', '=', $this->project_id)
            ->select($this->getKeyName())
            ->lockForUpdate()
            ->get();
    }

    /**
     * Get all children, then proceed with delete file(s) associated with the children nodes.
     */
    public function pruneChildren()
    {
        list( $lftCol, $rgtCol, $lft, $rgt ) = $this->getRightAndLeftParams();

        $children = $this->newNestedSetQuery()
            ->with('files')
            ->where($lftCol, '>', $lft)
            ->where($rgtCol, '<', $rgt)
            ->where('root_id', '=', $this->root_id)
            ->where('parent_id', '=', $this->id)
            ->where('project_id', '=', $this->project_id)
            ->get();

        foreach($children as $child)
        {
            foreach($child->files as $file)
            {
                $file->delete();
            }
            $child->delete();
        }
    }

    /**
     * Recalculates left and right indexes for the other nodes.
     */
    public function recalculateBoundaries()
    {
        // Current object properties may out of date.
        $this->reload();

        list( $lftCol, $rgtCol, $lft, $rgt ) = $this->getRightAndLeftParams();

        $diff = $rgt - $lft + 1;

        // ancestor folders + folders on the right of "folder to be deleted"
        $this->newNestedSetQuery()
            ->where($lftCol, '>', $rgt)
            ->where('root_id', '=', $this->root_id)
            ->where('project_id', '=', $this->project_id)
            ->decrement($lftCol, $diff);

        $this->newNestedSetQuery()
            ->where($rgtCol, '>', $rgt)
            ->where('root_id', '=', $this->root_id)
            ->where('project_id', '=', $this->project_id)
            ->decrement($rgtCol, $diff);
    }

    /**
     * Returns the column names and values for rgt and lft.
     *
     * @return array
     */
    public function getRightAndLeftParams()
    {
        return array(
            $this->getLeftColumnName(),
            $this->getRightColumnName(),
            $this->getLeft(),
            $this->getRight()
        );
    }

    /**
     * Returns files in descendant folders with
     * files shared by other parties.
     *
     * @param ContractGroup $contractGroup
     *
     * @return array
     */
    public function getAllAccessibleFiles(ContractGroup $contractGroup)
    {
        $files = $this->getOwnFiles($contractGroup);

        // Get shared folders.
        foreach(self::getSharedFoldersByContractGroup($contractGroup, $this->project, $this->folder_type) as $contractGroupId => $sharedFolders)
        {
            foreach($sharedFolders as $sharedFolder)
            {
                $folderObject = self::find($sharedFolder['id']);
                $folderFiles  = $folderObject->files;

                foreach($folderFiles as $folderFile)
                {
                    $files[] = $folderFile;
                }
            }
        }

        return $files;
    }

    /**
     * Returns files in descendant folders.
     *
     * @param ContractGroup $contractGroup
     *
     * @return array
     */
    public function getOwnFiles(ContractGroup $contractGroup)
    {
        $files = array();

        foreach($this->getDescendantsByContractGroup($contractGroup) as $descendant)
        {
            $descendantObj   = self::find($descendant['id']);
            $descendantFiles = $descendantObj->files;

            foreach($descendantFiles as $descendantFile)
            {
                $files[] = $descendantFile;
            }
        }

        return $files;
    }

    public function hasAccess(User $user)
    {
        if( ! $company = $user->getAssignedCompany($this->project) ) return false;

        $userContractGroup = $company->getContractGroup($this->project);

        return ( $this->belongsToContractGroup($userContractGroup) || $this->isSharedForContractGroup($userContractGroup) );
    }

    public function isEditor(User $user)
    {
        if( ! $company = $user->getAssignedCompany($this->project) ) return false;

        $userContractGroup = $company->getContractGroup($this->project);

        return ( $this->belongsToContractGroup($userContractGroup) && $user->isEditor($this->project) );
    }

}