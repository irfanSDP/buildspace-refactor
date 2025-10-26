<?php namespace PCK\Folder;

use Baum\Node;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class FileNode extends Node {

    use SoftDeletingTrait;

    protected $table = 'file_nodes';

    protected $orderColumn = 'priority';

    CONST TYPE_FOLDER = 1;
    CONST TYPE_FILE   = 2;

  // /**
  // * With Baum, all NestedSet-related fields are guarded from mass-assignment
  // * by default.
  // *
  // * @var array
  // */
  // protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

    protected $scoped = array('root_id');

    protected static function boot() {

        parent::boot();

        static::creating(function($node){
            if(is_null($node->name)) $node->name = '';
            if(is_null($node->description)) $node->description = '';
            if(is_null($node->priority)) $node->priority = $node->getNewPriority();
            if(is_null($node->root_id)) $node->root_id = -1;
            if(is_null($node->type)) $node->type = self::TYPE_FOLDER;
            if(is_null($node->version)) $node->version = 1;
            if(is_null($node->is_latest_version)) $node->is_latest_version = true;

            $user = \Confide::user();
            $node->created_by = $user->id;
            $node->updated_by = $user->id;
        });

        static::created(function($node){
            if( $node->root_id == -1) 
            {
                \DB::statement("UPDATE file_nodes SET root_id = {$node->id} WHERE id = {$node->id} AND deleted_at IS NULL");
            }
            if( is_null($node->origin_id) )
            {
                \DB::statement("UPDATE file_nodes SET origin_id = {$node->id} WHERE id = {$node->id} AND deleted_at IS NULL");
            }
        });

        static::saving(function($node){
            $user = \Confide::user();

            $node->updated_by = $user->id;
        });

        static::deleting(function($node){
            $node->destroyDescendants();
        });
    }

    public function delete()
    {
        $nodeIds = $this->descendantsAndSelf()->lists('id');

        FileNodePermission::whereIn('file_node_id', $nodeIds)->delete();

        parent::delete();
    }

    public function upload()
    {
        return $this->belongsTo('PCK\Base\Upload');
    }

    public function getNewPriority()
    {
        if($this->id)
        {
            if($this->id == $this->root_id)
            {
                $maxPriorityValue = self::whereNull('parent_id')
                    ->where('id', '!=', $this->id)
                    ->max('priority');
            }
            else
            {
                $maxPriorityValue = self::where('root_id', '=', $this->root_id)
                    ->where('parent_id', '=', $this->parent_id)
                    ->where('id', '!=', $this->id)
                    ->max('priority');
            }
        }
        else
        {
            if(is_null($this->parent_id))
            {
                $maxPriorityValue = self::whereNull('parent_id')
                    ->max('priority');
            }
            else
            {
                $maxPriorityValue = self::where('root_id', '=', $this->root_id)
                    ->where('parent_id', '=', $this->parent_id)
                    ->max('priority');
            }
        }

        return $maxPriorityValue+1;
    }

    public function canEdit($user)
    {
        return in_array($this->id, FileNodePermission::getEditableIds($user->id));
    }

    public function makeChildOf($node)
    {
        if($this->parent_id == $node->id) return;

        if($this->inSameScope($node))
        {
            parent::makeChildOf($node);

            $newPriority = $this->getNewPriority();

            \DB::statement("UPDATE file_nodes SET priority = {$newPriority} WHERE id = {$this->id}");
        }
        else
        {
            $this->makeChildOfOtherScope($node);
        }
    }

    public function makeChildOfOtherScope($node)
    {
        $oldParentId = $this->parent_id;
        $oldPriority = $this->priority;

        $targetRoot = FileNode::find($node->root_id);

        $newPriority = FileNode::where('parent_id', '=', $node->id)
            ->max('priority') + 1;

        // Detach node from current tree.
        if(!$this->isRoot()) $this->makeRoot();

        // Attach node tree into target root.
        $boundaryDiff = $targetRoot->rgt - $this->lft;
        $treeWidth    = $this->rgt;

        \DB::statement("
            UPDATE file_nodes
            SET
            root_id = {$targetRoot->id},
            depth = depth + 1,
            lft = lft + {$boundaryDiff},
            rgt = rgt + {$boundaryDiff}
            WHERE root_id = {$this->id}
            ");

        // Set parent_id of node.
        \DB::statement("UPDATE file_nodes SET parent_id = {$targetRoot->id} WHERE id = {$this->id}");

        // Update target root's rgt
        \DB::statement("
            UPDATE file_nodes
            SET rgt = rgt + {$treeWidth}
            WHERE id = {$targetRoot->id}
            ");

        $this->reload();

        // Reposition node.
        parent::makeChildOf($node);

        \DB::statement("UPDATE file_nodes SET priority = {$newPriority} WHERE id = {$this->id}");

        // Update boundaries of old tree.
        if(is_null($oldParentId))
        {
            \DB::statement($s = "UPDATE file_nodes SET priority = priority - 1 WHERE parent_id IS NULL AND priority > {$oldPriority} AND deleted_at IS NULL");
        }
        else
        {
            \DB::statement("UPDATE file_nodes SET priority = priority - 1 WHERE parent_id = {$oldParentId} AND priority > {$oldPriority} AND deleted_at IS NULL");
        }
    }

    public function moveToLeftOf($node)
    {
        parent::moveToLeftOf($node);

        $newPriority = $node->priority;

        \DB::statement("UPDATE file_nodes SET priority = {$newPriority} WHERE id = {$this->id}");

        \DB::statement("
            UPDATE file_nodes
            SET priority = priority + 1
            WHERE parent_id = {$node->parent_id}
            AND priority >= {$newPriority}
            AND id != {$this->id}
            AND deleted_at IS NULL");
    }

    public function moveToRightOf($node)
    {
        parent::moveToRightOf($node);

        $newPriority = $node->priority + 1;

        \DB::statement("UPDATE file_nodes SET priority = {$newPriority} WHERE id = {$this->id}");

        \DB::statement("
            UPDATE file_nodes
            SET priority = priority + 1
            WHERE parent_id = {$node->parent_id}
            AND priority >= {$newPriority}
            AND id != {$this->id}
            AND deleted_at IS NULL");
    }

    public function makeRoot()
    {
        parent::makeRoot();

        // Set root_id.
        \DB::statement("
            UPDATE file_nodes
            SET root_id = {$this->id}
            WHERE root_id = {$this->root_id}
            AND lft >= {$this->lft}
            AND rgt <= {$this->rgt}
            AND deleted_at IS NULL");

        $this->reload();

        $priority = $this->getNewPriority();

        \DB::statement("
            UPDATE file_nodes
            SET priority = {$this->getNewPriority()}
            WHERE id = {$this->id}");

        // Update the boundaries (baum does not reset them to start from 1).
        if($this->lft > 1)
        {
            $diff = $this->lft - 1;

            \DB::statement("
                UPDATE file_nodes
                SET lft = lft - {$diff},
                rgt = rgt - {$diff}
                WHERE root_id = {$this->root_id}
                AND deleted_at IS NULL");
        }

        $this->reload();
    }
}
