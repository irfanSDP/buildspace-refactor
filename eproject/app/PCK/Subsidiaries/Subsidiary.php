<?php namespace PCK\Subsidiaries;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Helpers\StringOperations;
use PCK\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use PCK\ExternalApplication\Identifier;

class Subsidiary extends Model {

    use SoftDeletingTrait;

    protected $fillable = [
        'name',
        'identifier',
    ];

    protected static function boot()
    {
        parent::boot();

        self::saving(function(self $model)
        {
            $model->identifier = mb_strtoupper(preg_replace('/[^\w-]/', '', trim($model->identifier)));
        });

        self::deleting(function(self $model){
            if(!$model->deletable())
            {
                return false;
            }
            
            $ids = self::getSelfAndDescendantIds([$model->id]);

            Identifier::whereIn('internal_identifier', array_values($ids[$model->id]))
            ->where('class_name', '=', self::class)
            ->delete();

            self::whereIn('id', array_values($ids[$model->id]))
            ->where('id', '<>', $model->id)
            ->delete();

            return true;
        });
    }

    public function projects()
    {
        return $this->hasMany('PCK\Projects\Project', 'subsidiary_id', 'id');
    }

    public function openTenderNews()
    {
        return $this->hasMany('PCK\Tenders\OpenTenderNew', 'subsidiary_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function children()
    {
        return $this->hasMany('PCK\Subsidiaries\Subsidiary', 'parent_id', 'id')->orderBy('name', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo('PCK\Subsidiaries\Subsidiary');
    }

    public function modulePermissions()
    {
        return $this->belongsToMany('PCK\ModulePermission\ModulePermission', 'module_permission_subsidiaries', 'subsidiary_id', 'module_permission_id')->withTimestamps();
    }

    public function subsidiaryProportionRecords()
    {
        return $this->hasMany('PCK\AccountCodeSettings\SubsidiaryApportionmentRecord');
    }

    public function getFullNameAttribute()
    {
        $subsidiaryStack = array( $this->name );

        $parent = $this->parent;

        while( $parent )
        {
            $subsidiaryStack[] = $parent->name;
            $parent            = $parent->parent;
        }

        $firstSubsidiary = array_pop($subsidiaryStack);

        $fullName = $firstSubsidiary;

        if( count($subsidiaryStack) > 0 )
        {
            $subsidiaryStack = array_reverse($subsidiaryStack);

            $fullName = "{$fullName} (" . implode(", ", $subsidiaryStack) . ")";
        }

        return $fullName;
    }

    public function getShortNameAttribute()
    {
        return StringOperations::shorten($this->name, 30);
    }

    public function getSubsidiaryChildrenIdRecursively()
    {
        $listOfSubsidiaries = [];
        $flattenedArray     = [];

        array_push($listOfSubsidiaries, [
            'id'       => $this->id,
            'children' => $this->loopSubsidiaryChildrenRecursively($this->children),
        ]);

        array_walk_recursive($listOfSubsidiaries, function($value, $key) use (&$flattenedArray)
        {
            array_push($flattenedArray, $value);
        });

        return $flattenedArray;
    }

    private function loopSubsidiaryChildrenRecursively($subsidiaries)
    {
        $listOfSubsidiaries = [];

        foreach($subsidiaries as $subsidiary)
        {
            array_push($listOfSubsidiaries, [
                'id'       => $subsidiary->id,
                'children' => $this->loopSubsidiaryChildrenRecursively($subsidiary->children),
            ]);
        }

        return $listOfSubsidiaries;
    }

    public static function getSubsidiariesTree()
    {
        $rootSubsidiaries = Subsidiary::whereNull('parent_id')->get();

        $tree = [];

        foreach($rootSubsidiaries as $rootSubsidiary)
        {
            $tree[ $rootSubsidiary->id ] = self::getChildTree($rootSubsidiary);
        }

        return $tree;
    }

    public static function getChildTree(Subsidiary $subsidiary)
    {
        $children = $subsidiary->children;

        $tree = [];

        foreach($children as $child)
        {
            $tree[ $child->id ] = self::getChildTree($child);
        }

        return $tree;
    }

    public function getParentsOfSubsidiary()
    {
        if(is_null($this->parent_id))
        {
            return [];
        }

        $parentSubsidiaries = [];
        $currentSubsidiary = $this;
        $continueSearch = true;

        while($continueSearch)
        {
            $isRoot = is_null($currentSubsidiary->parent_id);

            if($isRoot)
            {
                $continueSearch = false;
            }
            else
            {
                $currentSubsidiary = self::find($currentSubsidiary->parent_id);
                array_push($parentSubsidiaries, $currentSubsidiary);
            }
        }

        return array_reverse($parentSubsidiaries);
    }

    public function getTopParentSubsidiary($parentOption='top')
    {
        $parents = $this->getParentsOfSubsidiary();

        if (empty($parents))
        {
            return $this;   // if no parent (it is the root), return itself
        }

        switch ($parentOption) {
            case 'root':
                return $parents[0]; // return Root Parent

            case 'top':
                return end($parents); // return Direct Parent

            default:
                return $this; // return itself
        }
    }

    public function deletable()
    {
        if($this->isBeingUsedInBuildspaceProjects() or $this->isBeingUsedInBuildspaceProjectCodeSettings())
        {
            throw new ValidationException(trans('subsidiaries.deleteFailedBeingUsedInBSProjectCodeSetting'));
        }

        return true;
    }

    public function isBeingUsedInBuildspaceProjects()
    {
        $ids = self::getSelfAndDescendantIds([$this->id]);

        $count = Project::select('id')
            ->whereIn('subsidiary_id', $ids[$this->id])
            ->whereNull('deleted_at')
            ->count();

        return ($count);
    }

    public function isBeingUsedInBuildspaceProjectCodeSettings()
    {
        $ids = self::getSelfAndDescendantIds([$this->id]);

        $projectCodeSettingsRecord = \DB::connection('buildspace')
            ->table('bs_project_code_settings')
            ->select('id')
            ->whereIn('eproject_subsidiary_id', $ids[$this->id])
            ->whereNull('deleted_at')
            ->first();

        return !is_null($projectCodeSettingsRecord);
    }

    // this is much faster than ORM, especially for subsidiaries with deep tree
    public static function getTopParentsGroupedBySubsidiaryIds(array $subsidiaryIds)
    {
        if(empty($subsidiaryIds))
        {
            return [];
        }

        $query = "WITH RECURSIVE subsidiary_relations_cte AS (
                      SELECT 
                          id,
                          name,
                          identifier,
                          company_id,
                          parent_id,
                          array[id]::INTEGER[] AS path_array
                          FROM subsidiaries 
                          WHERE id IN (" . implode(', ', $subsidiaryIds) . ")
                      UNION ALL
                      SELECT
                          s.id,
                          s.name,
                          s.identifier,
                          s.company_id,
                          s.parent_id,
                          ARRAY_APPEND(sr.path_array, s.id::INTEGER) AS path_array
                          FROM subsidiaries s 
                          INNER JOIN subsidiary_relations_cte sr ON sr.parent_id = s.id
                  )
                  SELECT sr.path_array[1] AS subsidiary_id, sr.id AS id, sr.name AS name, sr.identifier AS identifier, sr.company_id AS company_id
                  FROM subsidiary_relations_cte sr
                  WHERE sr.parent_id IS NULL
                  ORDER BY sr.path_array[1] ASC;";

        $queryResults = \DB::select(\DB::raw($query));

        $data = [];

        foreach($queryResults as $result)
        {
            $data[$result->subsidiary_id] = [
                'id'         => $result->id,
                'name'       => $result->name,
                'identifier' => $result->identifier,
                'company_id' => $result->company_id,
            ];
        }

        return $data;
    }

    public static function getSelfAndAncestorIds(array $subsidiaryIds)
    {
        if(empty($subsidiaryIds))
        {
            return [];
        }

        $subsidiaryIdString = implode(',', $subsidiaryIds);

        $results = \DB::select(\DB::raw("WITH recursive subsidiaries_cte AS (
                SELECT id, parent_id,
                ARRAY[id]::INTEGER[] AS ancestors
                FROM subsidiaries
                WHERE id IN ({$subsidiaryIdString})
                UNION ALL
                SELECT s.id, s.parent_id,
                ARRAY_APPEND(c.ancestors, c.id) AS ancestors
                FROM subsidiaries s
                JOIN subsidiaries_cte c ON c.parent_id = s.id
            )
            SELECT id, ancestors[1] AS ancestor
            FROM subsidiaries_cte ORDER BY id;"));

        $data = [];

        foreach($results as $result)
        {
            if(!array_key_exists($result->ancestor, $data)) $data[$result->ancestor] = [];

            $data[$result->ancestor][] = $result->id;
        }

        return $data;
    }

    public static function getSelfAndDescendantIds(array $subsidiaryIds)
    {
        if(empty($subsidiaryIds))
        {
            return [];
        }

        $subsidiaryIdString = implode(',', $subsidiaryIds);

        $results = \DB::select(\DB::raw("WITH recursive subsidiaries_cte AS (
                SELECT id, parent_id,
                ARRAY[id]::INTEGER[] AS children
                FROM subsidiaries
                WHERE id IN ({$subsidiaryIdString})
                UNION ALL
                SELECT s.id, s.parent_id,
                ARRAY_APPEND(p.children, s.id) AS children
                FROM subsidiaries s
                JOIN subsidiaries_cte p ON p.id = s.parent_id
            )
            SELECT id, children[1] AS child_id
            FROM subsidiaries_cte ORDER BY id;"));

        $data = [];

        foreach($results as $result)
        {
            if(!array_key_exists($result->child_id, $data)) $data[$result->child_id] = [];

            $data[$result->child_id][] = $result->id;
        }

        return $data;
    }
}