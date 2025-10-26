<?php namespace PCK\MobileSync;

use Carbon\Carbon;

use PCK\Users\User;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\SiteManagement\SiteManagementDefect;
use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PCK\Defects\DefectCategory;
use PCK\Defects\Defect;
use PCK\DailyLabourReports\ProjectLabourRate;
use PCK\Companies\Company;
use PCK\Base\Upload;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;

use PCK\Buildspace\ProjectStructureLocationCode;
use PCK\Buildspace\ProjectMainInformation as BsProjectMainInformation;
use PCK\Buildspace\PreDefinedLocationCode;


/*

fetch('http://dev.eproject.buildspace.my/rest/acknowledgedRecords/123456',
    {
        method: 'POST',
        body: JSON.stringify({syncIds:['projects-856', 'projects-721']}),
    }
);

fetch('http://dev.eproject.buildspace.my/rest/syncRecords/123456',
    {
        method: 'POST',
        body: JSON.stringify([
            {
                SyncID: '951c3040d37f11e89f88950cbe24b078',
                RecordType: 'siteManagementDefects',
                RecordID: '951c0930d37f11e89f88950cbe24b078',
                SyncType: 'I',
                Data: {
                    id: '951c0930d37f11e89f88950cbe24b078',
                    project_id: 879,
                    project_structure_location_code_id: 4185,
                    pre_defined_location_code_id: 169,
                    contractor_id: 1088,
                    defect_category_id: 3,
                    defect_id: 6,
                    remarks: 'Test xxx 123\nQweqw eqweq',
                    status_id: 1,
                    created_at: '2018-10-19 17:15:55',
                    updated_at: '2018-10-19 17:15:55'
                }
            },{
                SyncID: '23338120d38111e88329dd560f2a5666',
                RecordType: 'siteManagementDefects',
                RecordID: '23335a10d38111e88329dd560f2a5666',
                SyncType: 'I',
                Data: {
                    id: '23335a10d38111e88329dd560f2a5666',
                    project_id: 879,
                    project_structure_location_code_id: 4027,
                    pre_defined_location_code_id: 216,
                    contractor_id: 250,
                    defect_category_id: 11,
                    defect_id: null,
                    remarks: 'Jhjkadshjkfhajsf askfjaksfa\nFast as faskfasfklas',
                    status_id: 1,
                    created_at: '2018-10-19 17:27:02',
                    updated_at: '2018-10-19 17:27:02'
                }
            }
        ])
    }
);

*/
class MobileSync {

    const SyncTypeInsert = 'I';
    const SyncTypeUpdate = 'U';
    const SyncTypeDelete = 'D';

    protected $user;
    protected $deviceId;

    public function __construct(User $user, $deviceId)
    {
        $this->user = $user;
        $this->deviceId = $deviceId;
    }

    public function initialSync()
    {
        $conn=\DB::getPdo();
        $sth = $conn->prepare("SELECT c.oid::regclass::text
            FROM pg_catalog.pg_class c
            JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname NOT LIKE 'pg_%'
            AND c.relname LIKE 'mobile_sync' || '%'
            AND c.relkind = 'r'");

        $sth->execute();

        $tables = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

        $success = false;

        try
        {
            $conn->beginTransaction();

            foreach($tables as $table)
            {
                $sth = $conn->prepare("DELETE FROM ".$table."
                WHERE user_id = :userId AND device_id = :deviceId");
                $sth->execute([
                    'deviceId' => $this->deviceId,
                    'userId' => $this->user->id
                ]);
            }

            $conn->commit();

            $success = true;
        }
        catch(\Exception $e)
        {
            $conn->rollBack();
            throw $e;
        }

        return $success;
    }

    public function insertAcknowledgedRecords($modelName, Array $records)
    {
        $ids = [];
        $identifierColumnName = $this->getTranslatedColumnName($modelName);

        foreach($records as $syncId)
        {
            $ids[]= $this->getIdFromSyncId($syncId);
        }

        if(!empty($ids))
        {
            switch($modelName)
            {
                case 'defect_category_trades':
                    $inSql = implode("','", $ids);
                    $inSql = "'".$inSql."'";
                    break;
                default:
                    $inSql = implode(',', $ids);
            }

            $dbh=\DB::getPdo();
            $sth = $dbh->prepare("SELECT ".$identifierColumnName." FROM mobile_sync_".$modelName."
            WHERE ".$identifierColumnName." IN (".$inSql.") AND device_id = :deviceId AND user_id = :userId");
            $sth->execute([
                'deviceId' => $this->deviceId,
                'userId' => $this->user->id
            ]);

            $existingRecordIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            $ids = array_diff($ids, $existingRecordIds);

            $insertRecords = [];

            foreach($ids as $id)
            {
                $insertRecords[] = [
                    $identifierColumnName => $id,
                    'user_id'             => $this->user->id,
                    'device_id'           => $this->deviceId,
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s')
                ];
            }

            if(!empty($insertRecords))
            {
                \DB::disableQueryLog();
                \DB::beginTransaction();
                try
                {
                    \DB::table('mobile_sync_'.$modelName)->insert($insertRecords);

                    \DB::commit();
                }
                catch (\Exception $e)
                {
                    \DB::rollBack();

                    throw $e;
                }
            }
        }
    }

    public function updateAcknowledgedRecords($modelName, Array $syncIds, $isSynced=true)
    {
        $val = ($isSynced) ? 'TRUE' : 'FALSE';

        if(!empty($syncIds))
        {
            \DB::disableQueryLog();
            \DB::beginTransaction();
            try
            {
                switch($modelName)
                {
                    case 'project_labour_rate_trades':
                    case 'project_labour_rate_contractors':
                    case 'defect_category_trades':
                        $inSql = implode("','", $syncIds);
                        $inSql = "'".$inSql."'";
                        break;
                    default:
                        $inSql = implode(',', $syncIds);
                }

                \DB::update("UPDATE mobile_sync_".$modelName." SET synced = ".$val.", updated_at = '".date('Y-m-d H:i:s')."' WHERE ".$this->getTranslatedColumnName($modelName)." IN (".$inSql.") AND user_id = ".$this->user->id." AND device_id = '".$this->deviceId."' ");

                \DB::commit();
            }
            catch (\Exception $e)
            {
                \DB::rollBack();

                throw $e;
            }
        }
    }

    public function getProjects()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $projectIds = $this->getProjectIds();

        if(!empty($projectIds))
        {
            $dbh=\DB::getPdo();

            $sth = $dbh->prepare("SELECT DISTINCT id, parent_project_id FROM projects
                WHERE parent_project_id IN (".implode(',', $projectIds).")
                AND deleted_at IS NULL
                ORDER BY parent_project_id");
            $sth->execute();
            $subProjectIds = $sth->fetchAll(\PDO::FETCH_KEY_PAIR);

            $projectIds = array_unique(array_merge($projectIds, array_keys($subProjectIds)));
        }

        //filter projects that already synced
        $projects = !empty($projectIds) ? Project::select('projects.*')
            ->whereIn('id', $projectIds)->leftJoin('mobile_sync_projects', function($join) use ($user, $deviceId)
            {
                $join->on('projects.id', '=', 'mobile_sync_projects.project_id');
                $join->on('user_id','=', \DB::raw($user->id));
                $join->on('device_id','=', \DB::raw("'".$deviceId."'"));
            })
            ->whereRaw('mobile_sync_projects.synced IS NOT TRUE')
            ->whereRaw('projects.deleted_at IS NULL')
            ->orderBy('projects.created_at', 'DESC')
            ->get() : [];

        $data = [];

        foreach($projects as $project)
        {
            $syncId = 'projects-'.$project->id;

            $data[] = [
                'RecordType' => 'projects',
                'SyncID'     => $syncId,
                'RecordID'   => $project->id,
                'SyncType'   => self::SyncTypeInsert,
                'data'       => [
                    'id'                       => $project->id,
                    'parent_project_id'        => ($project->parent_project_id) ? $project->parent_project_id : $project->id,
                    'reference'                => $project->reference,
                    'title'                    => $project->title,
                    'status'                   => Project::getStatusById($project->status_id),
                    'country'                  => $project->country->country,
                    'state'                    => $project->state->name,
                    'contract_name'            => $project->contract->name,
                    'is_sub_package'           => $project->isSubProject(),
                    'parent_project_title'     => $project->parentProject->title ?? '',
                    'parent_project_reference' => $project->parentProject->reference ?? '',
                    'created_at'               => Carbon::parse($project->created_at, \Config::get('app.timezone'))->toAtomString()
                ]
            ];
        }
        
        return $data;
    }

    public function getDefectCategories()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $defectCategories = DefectCategory::select('defect_categories.id', 'defect_categories.name', 'defect_categories.created_at')
        ->leftJoin('mobile_sync_defect_categories', function($join) use ($user, $deviceId)
           {
               $join->on('defect_categories.id', '=', 'mobile_sync_defect_categories.defect_category_id');
               $join->on('user_id','=', \DB::raw($user->id));
               $join->on('device_id','=', \DB::raw("'".$deviceId."'"));
           })
           ->whereRaw('mobile_sync_defect_categories.synced IS NOT TRUE')
           ->orderBy('defect_categories.name', 'ASC')
           ->get();

       $data = [];

       foreach($defectCategories as $defectCategory)
       {
           $syncId = 'defectCategories-'.$defectCategory->id;

           $data[] = [
               'RecordType' => 'defectCategories',
               'SyncID'     => $syncId,
               'RecordID'   => $defectCategory->id,
               'SyncType'   => self::SyncTypeInsert,
               'data'       => [
                   'id'         => $defectCategory->id,
                   'name'       => $defectCategory->name,
                   'created_at' => Carbon::parse($defectCategory->created_at, \Config::get('app.timezone'))->toAtomString(),
               ]
           ];
       }

       return $data;
    }

    public function getDefects()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $defects = Defect::select('defects.id', 'defects.name', 'defects.defect_category_id', 'defects.created_at')
        ->leftJoin('mobile_sync_defects', function($join) use ($user, $deviceId)
           {
               $join->on('defects.id', '=', 'mobile_sync_defects.defect_id');
               $join->on('user_id','=', \DB::raw($user->id));
               $join->on('device_id','=', \DB::raw("'".$deviceId."'"));
           })
           ->whereRaw('mobile_sync_defects.synced IS NOT TRUE')
           ->orderBy('defects.name', 'ASC')
           ->get();

       $data = [];

       foreach($defects as $defect)
       {
           $syncId = 'defects-'.$defect->id;

           $data[] = [
               'RecordType' => 'defects',
               'SyncID'     => $syncId,
               'RecordID'   => $defect->id,
               'SyncType'   => self::SyncTypeInsert,
               'data'       => [
                   'id'                 => $defect->id,
                   'name'               => $defect->name,
                   'defect_category_id' => $defect->defect_category_id,
                   'created_at'         => Carbon::parse($defect->created_at, \Config::get('app.timezone'))->toAtomString(),
               ]
           ];
       }

       return $data;
    }

    public function getProjectStructureLocationCodes()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $projectIds = $this->getProjectIds();

        $data = [];

        if(!empty($projectIds))
        {
            $dbh=\DB::getPdo();
            $sth = $dbh->prepare( "SELECT c.project_structure_location_code_id FROM mobile_sync_project_structure_location_codes c
            WHERE c.device_id = :deviceId AND c.user_id = :userId AND synced IS TRUE");
            $sth->execute([
                'deviceId' => $deviceId,
                'userId'   => $user->id
            ]);

            $syncedLocationIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            $bsProjectMainInfoTableName = with(new BsProjectMainInformation)->getTable();
            $bsProjectStructureLocationCodeTableName = with(new ProjectStructureLocationCode)->getTable();

            $query = ProjectStructureLocationCode::select($bsProjectStructureLocationCodeTableName.".id", $bsProjectStructureLocationCodeTableName.".description",
            $bsProjectStructureLocationCodeTableName.".priority", $bsProjectStructureLocationCodeTableName.".root_id", $bsProjectStructureLocationCodeTableName.".lft",
            $bsProjectStructureLocationCodeTableName.".rgt", $bsProjectStructureLocationCodeTableName.".level", $bsProjectStructureLocationCodeTableName.".created_at",
            "info.eproject_origin_id")
                ->join($bsProjectMainInfoTableName.' AS info', 'info.project_structure_id', '=', $bsProjectStructureLocationCodeTableName.'.project_structure_id')
                ->whereIn('info.eproject_origin_id', $projectIds);

            if(!empty($syncedLocationIds))
            {
                $query->whereNotIn($bsProjectStructureLocationCodeTableName.'.id', $syncedLocationIds);
            }

            $locations = $query->whereRaw($bsProjectStructureLocationCodeTableName.'.deleted_at IS NULL')
                ->orderBy("info.project_structure_id", "DESC")
                ->orderBy($bsProjectStructureLocationCodeTableName.".root_id", 'ASC')
                ->orderBy($bsProjectStructureLocationCodeTableName.".priority", "ASC")
                ->orderBy($bsProjectStructureLocationCodeTableName.".lft", "ASC")
                ->orderBy($bsProjectStructureLocationCodeTableName.".level", "ASC")
                ->get()
                ->toArray();

            foreach($locations as $location)
            {
                $syncId = 'projectStructureLocationCodes-'.$location['id'];

                $data[] = [
                    'RecordType' => 'projectStructureLocationCodes',
                    'SyncID'     => $syncId,
                    'RecordID'   => $location['id'],
                    'SyncType'   => self::SyncTypeInsert,
                    'data'       => [
                        'id'          => $location['id'],
                        'description' => (string)$location['description'],
                        'priority'    => $location['priority'],
                        'root_id'     => $location['root_id'],
                        'lft'         => $location['lft'],
                        'rgt'         => $location['rgt'],
                        'level'       => $location['level'],
                        'project_id'  => $location['eproject_origin_id'],
                        'created_at'  => Carbon::parse($location['created_at'], \Config::get('app.timezone'))->toAtomString(),
                    ]
                ];
            }
        }

        return $data;
    }

    public function getTrades()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $projectIds = $this->getProjectIds();

        $data = [];

        if(!empty($projectIds))
        {
            $dbh=\DB::getPdo();

            $sth = $dbh->prepare("SELECT DISTINCT id, parent_project_id FROM projects
                WHERE parent_project_id IN (".implode(',', $projectIds).")
                AND deleted_at IS NULL
                ORDER BY parent_project_id");
            $sth->execute();
            $subProjectIds = $sth->fetchAll(\PDO::FETCH_KEY_PAIR);

            $projectIds = array_unique(array_merge($projectIds, array_keys($subProjectIds)));

            $dbh=\DB::getPdo();
            $sth = $dbh->prepare( "SELECT t.trade_id FROM mobile_sync_trades t
            WHERE t.device_id = :deviceId AND t.user_id = :userId AND synced IS TRUE");
            $sth->execute([
                'deviceId' => $deviceId,
                'userId'   => $user->id
            ]);

            $syncedIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            $excludeSyncedSql = '';

            if(!empty($syncedIds))
            {
                $excludeSyncedSql = "AND pre_defined_location_code_id NOT IN (".implode(',', $syncedIds).")";
            }

            unset($syncedIds);

            $projectLabourRateTableName = with(new ProjectLabourRate)->getTable();

            $sth = $dbh->prepare("SELECT DISTINCT pre_defined_location_code_id
                FROM ".$projectLabourRateTableName."
                WHERE project_id IN (".implode(',', $projectIds).") AND pre_defined_location_code_id IS NOT NULL ".$excludeSyncedSql
            );
            $sth->execute();

            $preDefinedLocationCodeIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            if(!empty($preDefinedLocationCodeIds))
            {
                $preDefinedLocationCodeTableName = with(new PreDefinedLocationCode)->getTable();
                $dbhBuildspace=\DB::connection('buildspace')->getPdo();
                $sth = $dbhBuildspace->prepare("SELECT DISTINCT id, name, created_at
                    FROM ".$preDefinedLocationCodeTableName."
                    WHERE id IN (".implode(',', $preDefinedLocationCodeIds).") AND deleted_at IS NULL"
                );
                $sth->execute();
                $records = $sth->fetchAll(\PDO::FETCH_ASSOC);

                foreach($records as $record)
                {
                    $syncId = 'trades-'.$record['id'];

                    $data[] = [
                        'RecordType' => 'trades',
                        'SyncID'     => $syncId,
                        'RecordID'   => $record['id'],
                        'SyncType'   => self::SyncTypeInsert,
                        'data'       => [
                            'id'         => $record['id'],
                            'name'       => $record['name'],
                            'created_at' => Carbon::parse($record['created_at'], \Config::get('app.timezone'))->toAtomString(),
                        ]
                    ];
                }
            }
        }

        return $data;
    }

    public function getContractors()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $projectIds = $this->getProjectIds();

        $data = [];

        if(!empty($projectIds))
        {
            $dbh=\DB::getPdo();

            $sth = $dbh->prepare("SELECT DISTINCT id, parent_project_id FROM projects
                WHERE parent_project_id IN (".implode(',', $projectIds).")
                AND deleted_at IS NULL
                ORDER BY parent_project_id");
            $sth->execute();
            $subProjectIds = $sth->fetchAll(\PDO::FETCH_KEY_PAIR);

            $projectIds = array_unique(array_merge($projectIds, array_keys($subProjectIds)));

            $dbh=\DB::getPdo();
            $sth = $dbh->prepare( "SELECT c.company_id FROM mobile_sync_companies c
            WHERE c.device_id = :deviceId AND c.user_id = :userId AND synced IS TRUE");
            $sth->execute([
                'deviceId' => $deviceId,
                'userId'   => $user->id
            ]);

            $syncedIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            $excludeSyncedSql = '';

            if(!empty($syncedIds))
            {
                $excludeSyncedSql= "AND c.id NOT IN (".implode(',', $syncedIds).") ";
            }

            unset($syncedIds);

            $projectLabourRateTableName = with(new ProjectLabourRate)->getTable();
            $companyTableName = with(new Company)->getTable();

            $sth = $dbh->prepare("SELECT DISTINCT c.id, c.name, c.created_at
                FROM ".$companyTableName." c
                JOIN ".$projectLabourRateTableName." l ON l.contractor_id = c.id
                JOIN projects p ON l.project_id = p.id
                WHERE project_id IN (".implode(',', $projectIds).") AND contractor_id IS NOT NULL
                AND pre_defined_location_code_id IS NOT NULL ".$excludeSyncedSql."
                AND p.deleted_at IS NULL
                ORDER BY c.id DESC");

            $sth->execute();

            $records = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach($records as $record)
            {
                $syncId = 'companies-'.$record['id'];

                $data[$record['id']] = [
                    'RecordType' => 'companies',
                    'SyncID'     => $syncId,
                    'RecordID'   => $record['id'],
                    'SyncType'   => self::SyncTypeInsert,
                    'data'       => [
                        'id'         => $record['id'],
                        'name'       => (string)$record['name'],
                        'created_at' => Carbon::parse($record['created_at'], \Config::get('app.timezone'))->toAtomString(),
                    ]
                ];
            }

            $data = array_values($data);
        }

        return $data;
    }

    public function getProjectLabourRates()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $projectIds = $this->getProjectIds();

        $data = [];

        if(!empty($projectIds))
        {
            $dbh=\DB::getPdo();

            $sth = $dbh->prepare("SELECT DISTINCT id, parent_project_id FROM projects
                WHERE parent_project_id IN (".implode(',', $projectIds).")
                AND deleted_at IS NULL
                ORDER BY parent_project_id");
            $sth->execute();
            $subProjectIds = $sth->fetchAll(\PDO::FETCH_KEY_PAIR);

            $projectIds = array_unique(array_merge($projectIds, array_keys($subProjectIds)));

            $dbh=\DB::getPdo();
            $sth = $dbh->prepare( "SELECT r.project_labour_rate_id FROM mobile_sync_project_labour_rates r
            WHERE r.device_id = :deviceId AND r.user_id = :userId AND synced IS TRUE");
            $sth->execute([
                'deviceId' => $deviceId,
                'userId'   => $user->id
            ]);

            $syncedIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            $excludeSyncedSql = '';

            if(!empty($syncedIds))
            {
                $excludeSyncedSql = "AND id NOT IN (".implode(',', $syncedIds).")";
            }

            unset($syncedIds);

            $projectLabourRateTableName = with(new ProjectLabourRate)->getTable();

            $sth = $dbh->prepare("SELECT DISTINCT id, mobile_sync_uuid, labour_type, normal_working_hours, normal_rate_per_hour, ot_rate_per_hour,
                project_id, pre_defined_location_code_id, contractor_id, created_at
                FROM ".$projectLabourRateTableName."
                WHERE project_id IN (".implode(',', $projectIds).")
                AND pre_defined_location_code_id IS NOT NULL
                AND contractor_id IS NOT NULL ".$excludeSyncedSql."
                ORDER BY created_at DESC"
            );

            $sth->execute();

            $records = $sth->fetchAll(\PDO::FETCH_ASSOC);

            if(!empty($records))
            {
                foreach($records as $record)
                {
                    $syncId = 'projectLabourRates-'.$record['id'];
                    $recordId = !empty($record['mobile_sync_uuid']) ? $record['mobile_sync_uuid'] : $record['id'];

                    $data[] = [
                        'RecordType' => 'projectLabourRates',
                        'SyncID'     => $syncId,
                        'RecordID'   => $recordId,
                        'SyncType'   => self::SyncTypeInsert,
                        'data'       => [
                            'id'                     => (string)$recordId,
                            'project_labour_rate_id' => $record['id'],
                            'labour_type'            => $record['labour_type'],
                            'normal_working_hours'   => floatval($record['normal_working_hours']),
                            'normal_rate_per_hour'   => floatval($record['normal_rate_per_hour']),
                            'ot_rate_per_hour'       => floatval($record['ot_rate_per_hour']),
                            'trade_id'               => $record['pre_defined_location_code_id'],
                            'contractor_id'          => $record['contractor_id'],
                            'project_id'             => $record['project_id'],
                            'created_at'             => Carbon::parse($record['created_at'], \Config::get('app.timezone'))->toAtomString(),
                        ]
                    ];
                }
            }
        }

        return $data;
    }

    public function getDefectCategoryTrades()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $projectIds = $this->getProjectIds();

        $data = [];

        if(!empty($projectIds))
        {
            $dbh=\DB::getPdo();

            $sth = $dbh->prepare("SELECT DISTINCT id, parent_project_id FROM projects
                WHERE parent_project_id IN (".implode(',', $projectIds).")
                AND deleted_at IS NULL
                ORDER BY parent_project_id");
            $sth->execute();
            $subProjectIds = $sth->fetchAll(\PDO::FETCH_KEY_PAIR);

            $projectIds = array_unique(array_merge($projectIds, array_keys($subProjectIds)));

            $dbh=\DB::getPdo();
            $sth = $dbh->prepare( "SELECT t.defect_category_trade_id FROM mobile_sync_defect_category_trades t
            WHERE t.device_id = :deviceId AND t.user_id = :userId AND synced IS TRUE");
            $sth->execute([
                'deviceId' => $deviceId,
                'userId'   => $user->id
            ]);

            $syncedIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

            $excludeSyncedSql = '';

            if(!empty($syncedIds))
            {
                $excludeSyncedSql= "AND p.id NOT IN (".implode(',', $syncedIds).")  ";
            }

            unset($syncedTradeIds);

            $projectLabourRateTableName = with(new ProjectLabourRate)->getTable();

            $sth = $dbh->prepare("SELECT DISTINCT p.id, p.defect_category_id, p.pre_defined_location_code_id AS trade_id, p.created_at
                FROM defect_category_pre_defined_location_code p
                JOIN ".$projectLabourRateTableName." l ON l.pre_defined_location_code_id = p.pre_defined_location_code_id
                WHERE l.project_id IN (".implode(',', $projectIds).")
                AND l.pre_defined_location_code_id IS NOT NULL ".$excludeSyncedSql
            );
            $sth->execute();

            $records = $sth->fetchAll(\PDO::FETCH_ASSOC);

            if(!empty($records))
            {
                foreach($records as $record)
                {
                    $syncId = 'defectCategoryTrades-'.$record['id'];

                    $data[] = [
                        'RecordType' => 'defectCategoryTrades',
                        'SyncID'     => $syncId,
                        'RecordID'   => $record['id'],
                        'SyncType'   => self::SyncTypeInsert,
                        'data'       => [
                            'id'         => $record['id'],
                            'trade_id'   => $record['trade_id'],
                            'defect_category_id' => $record['defect_category_id'],
                            'created_at' => Carbon::parse($record['created_at'], \Config::get('app.timezone'))->toAtomString(),
                        ]
                    ];
                }
            }
        }

        return $data;
    }

    public function getSiteManagementDefects()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $data = [];

        $dbh=\DB::getPdo();

        $sth = $dbh->prepare( "SELECT d.site_management_defect_id FROM mobile_sync_site_management_defects d
        WHERE d.device_id = :deviceId AND d.user_id = :userId AND synced IS TRUE");
        $sth->execute([
            'deviceId' => $deviceId,
            'userId'   => $user->id
        ]);

        $syncedIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

        $siteManagementDefects = [];

        $userTypes = [
            SiteManagementUserPermission::USER_TYPE_SITE,
            SiteManagementUserPermission::USER_TYPE_QA_QC_CLIENT,
            SiteManagementUserPermission::USER_TYPE_PM,
            SiteManagementUserPermission::USER_TYPE_QS
        ];

        foreach($userTypes as $userType)
        {
            $siteManagementDefects += SiteManagementDefect::getRecordsForMobileDataByUserPermission($user, $syncedIds, $userType);
        }

        foreach($siteManagementDefects as $siteManagementDefect)
        {
            //sync id uses db record id to update mobile_sync_* table
            $syncId = 'siteManagementDefects-'.$siteManagementDefect['id'];
            //record id will use uuid if it is set (data came from mobile) or else use db record id
            $recordId = !empty($siteManagementDefect['mobile_sync_uuid']) ? $siteManagementDefect['mobile_sync_uuid'] : $siteManagementDefect['id'];

            $mainProjectId = !empty($siteManagementDefect['parent_project_id']) ?  $siteManagementDefect['parent_project_id'] : $siteManagementDefect['project_id'];

            $tradeId          = $siteManagementDefect['pre_defined_location_code_id'];
            $contractorId     = $siteManagementDefect['contractor_id'];
            $defectCategoryId = $siteManagementDefect['defect_category_id'];

            $data[] = [
                'RecordType' => 'siteManagementDefects',
                'SyncID'     => $syncId,
                'RecordID'   => $recordId,
                'SyncType'   => self::SyncTypeInsert,
                'data'       => [
                    'id'                                 => (string)$recordId,
                    'site_management_defect_id'          => $siteManagementDefect['id'],
                    'project_structure_location_code_id' => $siteManagementDefect['project_structure_location_code_id'],
                    'trade_id'                           => $tradeId,
                    'contractor_id'                      => $contractorId,
                    'defect_category_id'                 => $defectCategoryId,
                    'defect_id'                          => $siteManagementDefect['defect_id'],
                    'status_id'                          => $siteManagementDefect['status_id'],
                    'count_reject'                       => $siteManagementDefect['count_reject'],
                    'remarks'                            => (string)$siteManagementDefect['remarks'],
                    'project_id'                         => $siteManagementDefect['project_id'],
                    'created_at'                         => Carbon::parse($siteManagementDefect['created_at'], \Config::get('app.timezone'))->toAtomString(),
                ]
            ];
        }

        return $data;
    }

    public function getSiteManagementDefectAttachments()
    {
        $user = $this->user;
        $deviceId = $this->deviceId;

        $data = [];

        $dbh=\DB::getPdo();

        $uploadTableName = with(new Upload)->getTable();
        $moduleUploadTableName = with(new ModuleUploadedFile)->getTable();
        $siteManagementDefectTableName = with(new SiteManagementDefect)->getTable();

        $userTypes = [
            SiteManagementUserPermission::USER_TYPE_SITE,
            SiteManagementUserPermission::USER_TYPE_QA_QC_CLIENT,
            SiteManagementUserPermission::USER_TYPE_PM,
            SiteManagementUserPermission::USER_TYPE_QS
        ];

        $siteManagementDefects = [];
        foreach($userTypes as $userType)
        {
            $siteManagementDefects += SiteManagementDefect::getRecordsForMobileDataByUserPermission($user, [], $userType);
        }

        $siteManagementDefectIds = array_keys($siteManagementDefects);

        $sth = $dbh->prepare( "SELECT u.upload_id
            FROM mobile_sync_uploads u
            JOIN ".$moduleUploadTableName." mu ON u.upload_id = mu.upload_id
            WHERE u.device_id = :deviceId AND u.user_id = :userId AND synced IS TRUE
            AND uploadable_type = 'PCK\SiteManagement\SiteManagementDefect' ");
        $sth->execute([
            'deviceId' => $deviceId,
            'userId'   => $user->id
        ]);

        $syncedIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

        $uploads = [];

        if(!empty($siteManagementDefectIds))
        {
            $sql = "SELECT u.id AS upload_id, u.mobile_sync_uuid, d.id AS defect_id, d.mobile_sync_uuid AS defect_mobile_sync_uuid,
            u.extension, u.size, u.created_at
            FROM ".$uploadTableName." u
            JOIN ".$moduleUploadTableName." mu ON mu.upload_id = u.id
            JOIN ".$siteManagementDefectTableName." d ON mu.uploadable_id = d.id
            WHERE mu.uploadable_type = :uploadableType
            AND d.id IN (".implode(',', $siteManagementDefectIds).") ";

            if(!empty($syncedIds))
            {
                $sql .= "AND u.id NOT IN (".implode(',', $syncedIds).") ";
            }

            $sql .= "ORDER BY d.project_id, u.created_at DESC";

            $sth = $dbh->prepare($sql);
            $sth->execute([
                'uploadableType' => 'PCK\SiteManagement\SiteManagementDefect'
            ]);

            $uploads = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }

        foreach($uploads as $upload)
        {
            $syncId = 'siteManagementDefectAttachments-'.$upload['upload_id'];
            //record id will use uuid if it is set (data came from mobile) or else use db record id
            $recordId = !empty($upload['mobile_sync_uuid']) ? $upload['mobile_sync_uuid'] : $upload['upload_id'];
            $siteManagementDefectId = !empty($upload['defect_mobile_sync_uuid']) ? $upload['defect_mobile_sync_uuid'] : $upload['defect_id'];

            $data[] = [
                'RecordType' => 'siteManagementDefectAttachments',
                'SyncID'     => $syncId,
                'RecordID'   => $recordId,
                'SyncType'   => self::SyncTypeInsert,
                'data'       => [
                    'id'                        => (string)$recordId,
                    'upload_id'                 => $upload['upload_id'],
                    'size'                      => $upload['size'],
                    'extension'                 => $upload['extension'],
                    'site_management_defect_id' => (string)$siteManagementDefectId,
                    'created_at'                => Carbon::parse($upload['created_at'], \Config::get('app.timezone'))->toAtomString(),
                ]
            ];
        }

        return $data;
    }

    public function syncRecords(Array $records)
    {
        $insertRecords = [];
        $updateRecords = [];

        foreach($records as $record)
        {
            switch($record['SyncType'])
            {
                case self::SyncTypeInsert:

                    if(array_key_exists('id', $record['Data']))
                    {
                        unset($record['Data']['id']);
                    }

                    if(!array_key_exists($record['RecordType'], $insertRecords))
                    {
                        $insertRecords[$record['RecordType']] = [];
                    }

                    if($record['RecordType']=='siteManagementDefects')
                    {
                        $record['Data']['submitted_by'] = $this->user->id;
                    }

                    $record['Data']['mobile_sync_uuid'] = is_numeric($record['RecordID']) ? null : $record['RecordID'];//non numeric recordID means created from mobile else create from eproject
                    $insertRecords[$record['RecordType']][] = $record['Data'];

                    break;
                case self::SyncTypeUpdate:
                    break;
                default:
            }
        }

        if(!empty($insertRecords))
        {
            $this->insertRecords($insertRecords);
        }

    }

    protected function insertRecords(Array $data)
    {
        foreach($data as $modelName => $records)
        {
            $tableName = $this->getTranslatedTableName($modelName);

            \DB::disableQueryLog();
            \DB::beginTransaction();
            try
            {
                \DB::table($tableName)->insert($records);
                \DB::commit();
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                throw $e;
            }
        }
    }

    public function syncAttachments(Array $attachments, Array $data)
    {
        $moduleType = null;
        $uuids = [];
        $uploadableIds = [];

        foreach($data as $info)
        {
            $pieces = explode('::', $info);
            //piece 0 - uploadedable uuid
            //piece 2 - attachment uuid
            $uuids[$pieces[2]] = $pieces[0];

            $moduleType = $pieces[1];
        }

        switch($moduleType)
        {
            case "PCK-SiteManagement-SiteManagementDefect":
                $moduleType = "PCK\SiteManagement\SiteManagementDefect";
                $uploadableIds = SiteManagementDefect::whereIn('mobile_sync_uuid', array_values($uuids))
                ->lists('id', 'mobile_sync_uuid');
                break;
            default:
                throw new \Exception('Invalid module type');
        }

        foreach($attachments as $attachment)
        {
            $upload = new Upload();
            $upload->process($attachment, true);
        }

        $uploadIds = Upload::whereIn('mobile_sync_uuid', array_keys($uuids))
        ->lists('id', 'mobile_sync_uuid');

        $recordsToInsert = [];
        foreach($uuids as $attachmentUUId => $uploadableUUId)
        {
            $uploadableId = array_key_exists($uploadableUUId, $uploadableIds) ? $uploadableIds[$uploadableUUId] : null;
            $uploadId = array_key_exists($attachmentUUId, $uploadIds) ? $uploadIds[$attachmentUUId] : null;

            if($uploadableId && $uploadId)
            {
                $recordsToInsert[] = [
                    'upload_id' => $uploadId,
                    'uploadable_id' => $uploadableId,
                    'uploadable_type' => $moduleType,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d')
                ];
            }
        }

        if(!empty($recordsToInsert))
        {
            ModuleUploadedFile::insert($recordsToInsert);
        }
    }
    /*
    * get project ids based on module permissions and user type
    */
    protected function getProjectIds()
    {
        $user = $this->user;
        $projectTableName = with(new Project)->getTable();

        $siteManagementUserPermTableName = with(new SiteManagementUserPermission)->getTable();
        $dbh=\DB::getPdo();
        $sth = $dbh->prepare("SELECT project_id FROM ".$siteManagementUserPermTableName." perm
        JOIN ".$projectTableName." p ON perm.project_id = p.id
        WHERE module_identifier = :moduleIdentifier AND user_id = :userId AND p.deleted_at IS NULL");
        $sth->execute([
            'moduleIdentifier' => SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT,
            'userId' => $user->id
        ]);

        $projectIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

        $projectIds = array_unique($projectIds);

        if(!$user->isSuperAdmin())
        {
            //get project ids where the user's company is a selected contractor
            $tenderTableName = with(new Tender)->getTable();

            $dbh=\DB::getPdo();
            $sth = $dbh->prepare( "SELECT t.project_id FROM ".$tenderTableName." t
            JOIN company_tender c ON t.id = c.tender_id
            JOIN ".$projectTableName." p ON t.project_id = p.id
            WHERE c.selected_contractor IS TRUE AND c.company_id = :companyId
            AND t.id = (SELECT MAX(t2.id) FROM ".$tenderTableName." t2 WHERE t.id = t2.id)
            AND p.deleted_at IS NULL
            ORDER BY t.id DESC");
            $sth->execute([
                'companyId' => $user->company->id
            ]);

            $projectIds = array_unique(array_merge($projectIds, $sth->fetchAll(\PDO::FETCH_COLUMN, 0)));
        }

        return $projectIds;
    }

    protected function getTranslatedColumnName($modelName)
    {
        switch($modelName)
        {
            case 'projects':
                return 'project_id';
            case 'defect_categories':
                return 'defect_category_id';
            case 'defects':
                return 'defect_id';
            case 'project_structure_location_codes':
                return 'project_structure_location_code_id';
            case 'project_labour_rates':
                return 'project_labour_rate_id';
            case 'trades':
                return 'trade_id';
            case 'companies':
                return 'company_id';
            case 'defect_category_trades':
                return 'defect_category_trade_id';
            case 'site_management_defects':
                return 'site_management_defect_id';
            case 'uploads':
                return 'upload_id';
            default:
                throw new \Exception('Invalid model name '.$modelName);
        }
    }

    protected function getTranslatedTableName($modelName)
    {
        switch($modelName)
        {
            case 'siteManagementDefects':
                return 'site_management_defects';
            case 'siteManagementDefectAttachments':
                return 'uploads';
            default:
                throw new \Exception('Invalid model name');
        }
    }

    protected function getIdFromSyncId($syncId)
    {
        $pieces = explode("-", $syncId);
        return $pieces[1];
    }
}
