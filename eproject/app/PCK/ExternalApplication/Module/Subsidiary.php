<?php
namespace PCK\ExternalApplication\Module;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use PCK\ExternalApplication\Module\Base;
use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\Identifier;

use PCK\Subsidiaries\Subsidiary as EprojectSubsidiary;

class Subsidiary extends Base
{
    const INTERNAL_ATTRIBUTE_ID = 1;
    const INTERNAL_ATTRIBUTE_NAME = 2;
    const INTERNAL_ATTRIBUTE_IDENTIFIER = 3;
    const INTERNAL_ATTRIBUTE_PARENT_ID = 4;

    protected static $className = EprojectSubsidiary::class;

    protected static $internalAttributes = [
        self::INTERNAL_ATTRIBUTE_ID => [
            'name' => 'id',
            'type' => self::ATTRIBUTE_TYPE_STRING,
            'required' => true,
            'is_identifier' => true
        ],
        self::INTERNAL_ATTRIBUTE_NAME => [
            'name' => 'name',
            'type' => self::ATTRIBUTE_TYPE_STRING,
            'required' => true
        ],
        self::INTERNAL_ATTRIBUTE_IDENTIFIER => [
            'name' => 'identifier',
            'type' => self::ATTRIBUTE_TYPE_STRING,
            'required' => true
        ],
        self::INTERNAL_ATTRIBUTE_PARENT_ID => [
            'name' => 'parent_id',
            'type' => self::ATTRIBUTE_TYPE_JSON
        ]
    ];

    public function create(array $data)
    {
        $toInsert = [];

        $this->toArrayStructure($data, $toInsert);

        $this->validate($toInsert);

        $insertedRecords = [];

        if(!empty($toInsert))
        {
            $identifierTbl = with(new Identifier)->getTable();
            $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();
            
            $existingExtIdentifiers = \DB::table($subsidiaryTbl)->select($subsidiaryTbl.'.id', $identifierTbl.'.external_identifier')
            ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id)
            ->where($identifierTbl.'.class_name', '=', self::$className)
            ->get();

            $existingIdentifiers = [];

            foreach($existingExtIdentifiers as $existingExtIdentifier)
            {
                $existingExtIdentifiers[$existingExtIdentifier->id] = $existingExtIdentifier->external_identifier;
            }

            $user = \Confide::user();

            $byParentIds = [];

            foreach($toInsert as $data)
            {
                if(in_array($data[self::INTERNAL_ATTRIBUTE_ID], $existingExtIdentifiers))
                {
                    continue;
                }

                $subsidiary = new EprojectSubsidiary;
                $subsidiary->name = $data[self::INTERNAL_ATTRIBUTE_NAME];
                $subsidiary->identifier = $data[self::INTERNAL_ATTRIBUTE_IDENTIFIER];
                $subsidiary->company_id = $user->company_id;

                $subsidiary->save();

                $identifierMap = new Identifier;
                $identifierMap->client_module_id = $this->clientModule->id;
                $identifierMap->class_name = EprojectSubsidiary::class;
                $identifierMap->internal_identifier = $subsidiary->id;
                $identifierMap->external_identifier = $data[self::INTERNAL_ATTRIBUTE_ID];

                $identifierMap->save();

                if(array_key_exists(self::INTERNAL_ATTRIBUTE_PARENT_ID, $data) && !empty($data[self::INTERNAL_ATTRIBUTE_PARENT_ID]))
                {
                    $byParentIds[$data[self::INTERNAL_ATTRIBUTE_PARENT_ID]] = $subsidiary->id;
                }

                $insertedRecords[] = $data[self::INTERNAL_ATTRIBUTE_ID];
            }

            if(!empty($byParentIds))
            {
                $parentIdentifiers = \DB::table($subsidiaryTbl)->select($subsidiaryTbl.'.id', $identifierTbl.'.external_identifier')
                ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
                ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id)
                ->where($identifierTbl.'.class_name', '=', self::$className)
                ->whereIn($identifierTbl.'.external_identifier', array_keys($byParentIds))
                ->get();

                foreach($parentIdentifiers as $parentIdentifier)
                {
                    if(array_key_exists($parentIdentifier->external_identifier, $byParentIds))
                    {
                        $subsidiary = EprojectSubsidiary::find($byParentIds[$parentIdentifier->external_identifier]);
                        $subsidiary->parent_id = $parentIdentifier->id;

                        $subsidiary->save();
                    }
                }
            }
        }
        
        $data = [];

        if(!empty($insertedRecords))
        {
            $records = EprojectSubsidiary::select($subsidiaryTbl.".id AS id", $subsidiaryTbl.".name", $subsidiaryTbl.".identifier", $subsidiaryTbl.".parent_id",
            $identifierTbl.".external_identifier", "parentIdent.external_identifier AS parent_external_identifier", $subsidiaryTbl.".created_at")
            ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
                $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
                $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
            })
            ->where($identifierTbl.'.class_name', '=', self::$className)
            ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id)
            ->whereIn($identifierTbl.'.external_identifier', $insertedRecords)
            ->get();

            foreach($records as $record)
            {
                $data[] = [
                    'id'                 => $record->id,
                    'name'               => $record->name,
                    'identifier'         => $record->identifier,
                    'parent_id'          => $record->parent_id,
                    'external_id'        => $record->external_identifier,
                    'parent_external_id' => $record->parent_external_identifier,
                    'created_at'         => Carbon::parse($record->created_at)->format('Y-m-d H:i:s')
                ];
            }
        }

        return $data;
    }

    private function toArrayStructure(array $data, array &$structure)
    {
        $subsidiary = [];

        foreach($this->attributeMaps as $internalAttribute => $externalAttribute)
        {
            if(array_key_exists($externalAttribute, $data))
            {
                if($internalAttribute == self::INTERNAL_ATTRIBUTE_PARENT_ID)
                {
                    if(!empty($data[$externalAttribute]))
                    {
                        $parentSubsidiary = $this->toArrayStructure($data[$externalAttribute], $structure);
                        $subsidiary[$internalAttribute] = $parentSubsidiary[self::INTERNAL_ATTRIBUTE_ID];
                    }
                }
                else
                {
                    $val = $data[$externalAttribute];

                    if($internalAttribute === self::INTERNAL_ATTRIBUTE_IDENTIFIER)
                    {
                        $val = mb_strtoupper(preg_replace('/[^\w-]/', '', trim($val)));
                    }

                    $subsidiary[$internalAttribute] = $val;
                }
            }
        }

        $structure[] = $subsidiary;

        return $subsidiary;
    }

    private function validate(array $records)
    {
        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        $existingNames = \DB::table($subsidiaryTbl)->selectRaw($subsidiaryTbl.".id, LOWER(".$subsidiaryTbl.".name) AS name")
            ->lists('name');
        
        $existingSubsidiaryIdentifiers = \DB::table($subsidiaryTbl)->selectRaw($subsidiaryTbl.".id, LOWER(".$subsidiaryTbl.".identifier) AS identifier")
            ->lists('identifier');
        
        $existingExtIdentifiers = \DB::table($subsidiaryTbl)->select($subsidiaryTbl.'.id', $subsidiaryTbl.'.name', $identifierTbl.'.internal_identifier', $identifierTbl.'.external_identifier')
            ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id)
            ->where($identifierTbl.'.class_name', '=', self::$className)
            ->lists('external_identifier');
        
        foreach(self::$internalAttributes as $key => $attribute)
        {
            foreach($records as $record)
            {
                if(array_key_exists('required', $attribute) && $attribute['required'])
                {
                    if(!array_key_exists($key, $record) || !strlen($record[$key]))
                    {
                        throw new \Exception('Value for attribute {'.$attribute['name'].'} is required');
                    }

                    if($key == self::INTERNAL_ATTRIBUTE_NAME && in_array(mb_strtolower($record[$key]), $existingNames) && !in_array($record[self::INTERNAL_ATTRIBUTE_ID], $existingExtIdentifiers))
                    {
                        throw new \Exception('Value for attribute {'.$attribute['name'].'} must be unique. '.$record[$key].' already existed in the system.');
                    }

                    if($key == self::INTERNAL_ATTRIBUTE_IDENTIFIER && in_array(mb_strtolower(preg_replace('/[^\w-]/', '', trim($record[$key]))), $existingSubsidiaryIdentifiers) && !in_array($record[self::INTERNAL_ATTRIBUTE_ID], $existingExtIdentifiers))
                    {
                        throw new \Exception('Value for attribute {'.$attribute['name'].'} must be unique. '.$record[$key].' already existed in the system.');
                    }
                }
            }
        }

        return true;
    }

    public function list(Request $request)
    {
        if($this->clientModule->downstream_permission == ClientModule::DOWNSTREAM_PERMISSION_DISABLED)
        {
            throw new UnauthorizedHttpException('Bearer', 'No authorization to get the entity list');
        }

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 100;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        $model = EprojectSubsidiary::select($subsidiaryTbl.".id AS id", $subsidiaryTbl.".name", $subsidiaryTbl.".identifier", $subsidiaryTbl.".parent_id",
        $identifierTbl.".external_identifier AS external_id", "parentIdent.external_identifier AS parent_external_id", $subsidiaryTbl.".created_at");

        if($this->clientModule->downstream_permission == ClientModule::DOWNSTREAM_PERMISSION_CLIENT)
        {
            $model->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
                $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
                $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
            })
            ->where($identifierTbl.'.class_name', '=', self::$className)
            ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id);
        }
        else
        {
            $model->leftJoin($identifierTbl, function($join) use($subsidiaryTbl, $identifierTbl){
                $join->on($identifierTbl.'.internal_identifier', '=', $subsidiaryTbl.'.id');
                $join->on($identifierTbl.'.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on($identifierTbl.'.client_module_id','=', \DB::raw($this->clientModule->id));
            })
            ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
                $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
                $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
            });
        }
        
        $model->orderBy(\DB::raw('COALESCE('.$subsidiaryTbl.'.parent_id, '.$subsidiaryTbl.'.id), '.$subsidiaryTbl.'.parent_id IS NOT NULL, '.$subsidiaryTbl.'.id'));
        
        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $subsidiaries = $records->toArray();

        $data = $this->buildTree($subsidiaries);

        $totalPages = ceil( $rowCount / $limit );

        $returnData['last_page'] = $totalPages;
        $returnData['data'] = $data;

        return $returnData;
    }

    private function buildTree(array $subsidiaries, $parentId = 0)
    {
        $branch = [];

        foreach ($subsidiaries as $subsidiary)
        {
            if ($subsidiary['parent_id'] == $parentId)
            {
                $children = $this->buildTree($subsidiaries, $subsidiary['id']);
                if ($children)
                {
                    $subsidiary['_children'] = $children;
                }

                $branch[] = $subsidiary;
            }
        }

        if(!empty($branch))
        {
            $sort = [];
            foreach($branch as $k=>$v)
            {
                $sort['name'][$k] = $v['name'];
            }
            array_multisort($sort['name'], SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE, $branch);
        }

        return $branch;
    }

    public function retrieve($id)
    {
        if($this->clientModule->downstream_permission == ClientModule::DOWNSTREAM_PERMISSION_DISABLED)
        {
            throw new UnauthorizedHttpException('Bearer', 'No authorization to retrieve the entity');
        }

        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        $model = EprojectSubsidiary::select($subsidiaryTbl.".id AS id", $subsidiaryTbl.".name", $subsidiaryTbl.".identifier", $subsidiaryTbl.".parent_id",
        $identifierTbl.".external_identifier AS external_id", "parentIdent.external_identifier AS parent_external_id", $subsidiaryTbl.".created_at");

        if($this->clientModule->downstream_permission == ClientModule::DOWNSTREAM_PERMISSION_CLIENT)
        {
            $model->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
                $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
                $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
            })
            ->where($identifierTbl.'.class_name', '=', self::$className)
            ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id);
        }
        else
        {
            $model->leftJoin($identifierTbl, function($join) use($subsidiaryTbl, $identifierTbl){
                $join->on($identifierTbl.'.internal_identifier', '=', $subsidiaryTbl.'.id');
                $join->on($identifierTbl.'.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on($identifierTbl.'.client_module_id','=', \DB::raw($this->clientModule->id));
            })
            ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
                $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
                $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
                $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
            });
        }

        $subsidiary = $model->where($identifierTbl.'.external_identifier', $id)->first();

        if(!$subsidiary)
        {
            throw new ModelNotFoundException('Entity does not exist for id: '.$id);
        }
        
        $subsidiaryIds = EprojectSubsidiary::getSelfAndDescendantIds([$subsidiary->id]);

        $subsidiaries = EprojectSubsidiary::select($subsidiaryTbl.".id AS id", $subsidiaryTbl.".name", $subsidiaryTbl.".identifier", $subsidiaryTbl.".parent_id",
        $identifierTbl.".external_identifier AS external_id", "parentIdent.external_identifier AS parent_external_id", $subsidiaryTbl.".created_at")
        ->leftJoin($identifierTbl, function($join) use($subsidiaryTbl, $identifierTbl){
            $join->on($identifierTbl.'.internal_identifier', '=', $subsidiaryTbl.'.id');
            $join->on($identifierTbl.'.class_name', '=', \DB::raw("'".self::$className."'"));
            $join->on($identifierTbl.'.client_module_id','=', \DB::raw($this->clientModule->id));
        })
        ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
            $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
            $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
            $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
        })
        ->whereIn($subsidiaryTbl.'.id', $subsidiaryIds[$subsidiary->id])
        ->get()
        ->toArray();

        $tree = $this->buildTree($subsidiaries);

        if(!empty($tree))
        {
            return $tree;//with children
        }

        return [[
            'id'                 => $subsidiary->id,
            'name'               => $subsidiary->name,
            'identifier'         => $subsidiary->identifier,
            'parent_id'          => $subsidiary->parent_id,
            'external_id'        => $subsidiary->external_id,
            'parent_external_id' => $subsidiary->parent_external_id,
            'created_at'         => Carbon::parse($subsidiary->created_at)->format('Y-m-d H:i:s')
        ]];
    }

    public function delete($id)
    {
        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        $subsidiary = EprojectSubsidiary::join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
        ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id)
        ->where($identifierTbl.'.class_name', '=', self::$className)
        ->where($identifierTbl.'.external_identifier', $id)
        ->first();

        if(!$subsidiary)
        {
            throw new ModelNotFoundException('Entity does not exist for id: '.$id);
        }

        //all validations and deleting other relations are handled in the boot callback
        $subsidiary->delete();
    }

    public function createdRecords(Request $request)
    {
        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 100;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        $model = EprojectSubsidiary::select($subsidiaryTbl.".id AS id", $subsidiaryTbl.".name", $subsidiaryTbl.".identifier", $subsidiaryTbl.".parent_id",
        $identifierTbl.".external_identifier", "parentIdent.external_identifier AS parent_external_identifier", $subsidiaryTbl.".created_at")
        ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
        ->leftJoin($identifierTbl." AS parentIdent", function($join) use($subsidiaryTbl){
            $join->on('parentIdent.internal_identifier', '=', $subsidiaryTbl.'.parent_id');
            $join->on('parentIdent.class_name', '=', \DB::raw("'".self::$className."'"));
            $join->on('parentIdent.client_module_id','=', \DB::raw($this->clientModule->id));
        })
        ->where($identifierTbl.'.class_name', '=', self::$className)
        ->where($identifierTbl.'.client_module_id', '=', $this->clientModule->id);

        $model->orderBy($subsidiaryTbl.'.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'counter'            => $counter,
                'id'                 => $record->id,
                'name'               => $record->name,
                'identifier'         => $record->identifier,
                'parent_id'          => $record->parent_id,
                'internal_id'        => $record->id,
                'external_id'        => $record->external_identifier,
                'parent_external_id' => $record->parent_external_identifier,
                'created_at'         => Carbon::parse($record->created_at)->format('Y-m-d H:i:s')
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return [$totalPages, $data];
    }
}
