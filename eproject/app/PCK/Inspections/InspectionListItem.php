<?php namespace PCK\Inspections;

use Baum\Node;
use PCK\Users\User;

class InspectionListItem extends Node
{
    protected $table    = 'inspection_list_items';
    protected $scoped   = ['inspection_list_category_id'];
    protected $fillable = [
        'inspection_list_category_id',
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'description',
        'type',
    ];

    const TYPE_HEAD = 1;
    const TYPE_ITEM = 2;

    public function inspectionListCategory()
    {
        return $this->belongsTo('PCK\Inspections\InspectionListCategory');
    }

    public function isTypeHead()
    {
        return ($this->type == self::TYPE_HEAD);
    }

    public function isTypeItem()
    {
        return ($this->type == self::TYPE_ITEM);
    }

    public static function getInspectionListItems(InspectionListCategory $inspectionListCategory, $parentId = null)
    {
        $query = self::where('inspection_list_category_id', $inspectionListCategory->id);

        if($parentId)
        {
            $query->where('parent_id', $parentId);
        }
        else
        {
            $query->whereNull('parent_id');
        }

        $query->orderBy('priority', 'ASC');

        return $query->get();
    }

    public static function getInspectionListItemTypes($typeId = null)
    {
        $types = [
            self::TYPE_HEAD => trans('inspection.head'),
            self::TYPE_ITEM => trans('inspection.item'),
        ];

        return $typeId ? $types[$typeId] : $types;
    }

    public static function getRootInspetionListItems(InspectionListCategory $inspectionListCategory)
    {
        return self::where('inspection_list_category_id', $inspectionListCategory->id)->whereNull('parent_id')->orderBy('priority', 'ASC')->get();
    }

    public static function getNextFreePriority($inspectionListCategoryId, $parentId = null)
    {
        $query = self::where('inspection_list_category_id', $inspectionListCategoryId);

        if($parentId)
        {
            $query->where('parent_id', $parentId);
        }

        $query->orderBy('priority', 'DESC');

        if($query->count() == 0) return 0;

        return $query->max('priority') + 1;
    }

    public static function updatePriority($removedRecord)
    {
        $query = self::where('inspection_list_category_id', $removedRecord->inspection_list_category_id);

        if(is_null($removedRecord->parent_id))
        {
            $query->whereNull('parent_id');
        }
        else
        {
            $query->where('parent_id', $removedRecord->parent_id);
        }

        $query->where('priority', '>', $removedRecord->priority);
        
        $records = $query->orderBy('priority', 'ASC')->get();

        foreach($records as $record)
        {
            $record->priority = ($record->priority - 1);
            $record->save();
        }
    }

    public static function purge($inspectionListCategoryId)
    {
        self::where('inspection_list_category_id', $inspectionListCategoryId)->delete();
    }

    public static function purgeChildren($id)
    {
        $record = self::find($id);

        foreach($record->getImmediateDescendants() as $descendant)
        {
            $descendant->delete();
        }
    }
}

