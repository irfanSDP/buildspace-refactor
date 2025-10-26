<?php namespace PCK\TemplateTenderDocumentFolders;

use Baum\Node;
use Illuminate\Database\Eloquent\Collection;
use PCK\Helpers\ModelOperations;
use PCK\StructuredDocument\StructuredDocument;
use PCK\WorkCategories\WorkCategory;

class TemplateTenderDocumentFolder extends Node {

    CONST ROOT_NAME = 'Tender Documents';

    protected $table = 'template_tender_document_folders';

    protected $fillable = [
        'root_id',
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'name',
        'work_category_id',
    ];

    protected $appends = [ 'serial_number' ];

    protected static function boot()
    {
        parent::boot();

        static::created(function(TemplateTenderDocumentFolder $node)
        {
            if( $node->depth == 0 && is_null($node->parent_id) )
            {
                $node->root_id = $node->id;
                $node->lft     = 1;
                $node->rgt     = 2;
                $node->save();
            }
        });

        // The saved event is defined with an empty callback because somewhere, another saved event is triggered instead.
        static::saved(function(self $node)
        {
        });

        static::deleting(function(self $node)
        {
            $node->augmentedDestroyDescendants();

            foreach($node->files as $file)
            {
                $file->delete();
            }

            if( $structuredDocument = StructuredDocument::getDocument($node) )
            {
                ModelOperations::deleteWithTrigger($structuredDocument);
            }
        });
    }

    public function files()
    {
        return $this->hasMany('PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFile', 'folder_id')->orderBy('id', 'desc');
    }

    public function getUploadedFiles()
    {
        return $this->hasMany('PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFile', 'folder_id')->orderBy('created_at', 'DESC');
    }

    public function workCategories()
    {
        return $this->belongsToMany('PCK\WorkCategories\WorkCategory')->withTimestamps();
    }

    public function getSerialNumberAttribute()
    {
        $ids = self::getAllRootFolders()->lists('id');

        return array_search($this->id, $ids) + 1;
    }

    public static function getAllRootFolders()
    {
        return self::whereRaw('id = root_id')->orderBy('id')->get();
    }

    public static function getRootFolder(int $id)
    {
        return self::whereRaw('id = root_id')->where('id', '=', $id)->first();
    }

    public function ancestorsAndSelf()
    {
        return $this->newNestedSetQuery()
            ->where($this->getLeftColumnName(), '<=', $this->getLeft())
            ->where($this->getRightColumnName(), '>=', $this->getRight())
            ->where('root_id', '=', $this->root_id);
    }

    public function descendantsAndSelf()
    {
        return $this->newNestedSetQuery()
            ->where($this->getLeftColumnName(), '>=', $this->getLeft())
            ->where($this->getLeftColumnName(), '<', $this->getRight())
            ->where('root_id', '=', $this->root_id);
    }

    protected function moveTo($target, $position)
    {
        return TemplateTenderDocumentFolderMove::to($this, $target, $position);
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
        list( $lftCol, $rgtCol, $lft, $rgt ) = $this->getRightAndLeftParams();

        $diff = $rgt - $lft + 1;

        // root folder + folders on the right of "folder to be deleted"
        $this->newNestedSetQuery()
            ->where($lftCol, '>', $rgt)
            ->where('root_id', '=', $this->root_id)
            ->decrement($lftCol, $diff);

        $this->newNestedSetQuery()
            ->where($rgtCol, '>', $rgt)
            ->where('root_id', '=', $this->root_id)
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

    public function getAvailableWorkCategories()
    {
        $unassigned = new Collection();

        foreach(WorkCategory::all() as $workCategory)
        {
            if( $workCategory->templateTenderDocumentFolders()->count() == 0 ) $unassigned->add($workCategory);
        }

        return $this->workCategories->merge($unassigned);
    }

}