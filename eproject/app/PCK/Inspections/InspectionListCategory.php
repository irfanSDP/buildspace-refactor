<?php namespace PCK\Inspections;

use Baum\Node;
use PCK\Users\User;
use PCK\Inspections\InspectionList;
use PCK\Projects\Project;

class InspectionListCategory extends Node
{
    protected $table    = 'inspection_list_categories';
    protected $scoped   = ['inspection_list_id'];
    protected $fillable = [
        'inspection_list_id',
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'name',
        'type',
    ];

    const TYPE_INSPECTION_CATEGORY = 1;
    const TYPE_INSPECTION_LIST     = 2;

    public function inspectionList()
    {
        return $this->belongsTo('PCK\Inspections\InspectionList');
    }

    public function inspectionListItems()
    {
        return $this->hasMany('PCK\Inspections\InspectionListItem', 'inspection_list_category_id')->orderBy('lft')->orderBy('priority');
    }

    public function additionalFields()
    {
        return $this->hasMany('PCK\Inspections\InspectionListCategoryAdditionalField', 'inspection_list_category_id')->orderBy('priority', 'ASC');
    }

    public function isTypeCategory()
    {
         return $this->type == self::TYPE_INSPECTION_CATEGORY;
    }

    public function isTypeListItem()
    {
        return $this->type == self::TYPE_INSPECTION_LIST;
    }

    public static function getInspectionCategories(InspectionList $inspectionList, $parentId = null)
    {
        $query = self::where('inspection_list_id', $inspectionList->id);

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

    public static function getInspectionListCategoryTypes($typeId = null)
    {
        $types = [
            self::TYPE_INSPECTION_CATEGORY => trans('inspection.category'),
            self::TYPE_INSPECTION_LIST     => trans('inspection.list'),
        ];

        return $typeId ? $types[$typeId] : $types;
    }

    public static function getNextFreePriority($inspectionListId, $parentId = null)
    {
        $query = self::where('inspection_list_id', $inspectionListId);

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
        $query = self::where('inspection_list_id', $removedRecord->inspection_list_id);

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

    public static function purgeChildren($inspectionListCategoryId)
    {
        $record = self::find($inspectionListCategoryId);

        foreach($record->getImmediateDescendants() as $descendant)
        {
            $descendant->delete();
        }
    }

    public function checkIsEditable()
    {
        if(is_null($this->inspectionList->project)) return true;

        $tiedRecords = RequestForInspection::where('project_id', $this->inspectionList->project->id)
                        ->where('inspection_list_category_id', $this->id)
                        ->get();

        return ($tiedRecords->count() == 0);
    }

    public function hasUneditableChildren()
    {
        $found = false;

        foreach($this->getDescendants() as $descendant)
        {
            if(!$descendant->checkIsEditable())
            {
                $found = true;
                break;
            }
        }

        return $found;
    }
}

