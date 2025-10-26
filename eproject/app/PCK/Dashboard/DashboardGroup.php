<?php namespace PCK\Dashboard;

use Illuminate\Database\Eloquent\Model;

use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\Subsidiaries\Subsidiary;
use PCK\Projects\StatusType;
use PCK\WorkCategories\WorkCategory;
use PCK\Countries\Country;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;

use PCK\Buildspace\Project as bsProject;
use PCK\Buildspace\ProjectMainInformation as bsProjectMainInformation;
use PCK\Buildspace\VariationOrder as bsVariationOrder;
use PCK\Buildspace\PostContract AS bsPostContract;
use PCK\Buildspace\NewPostContractFormInformation as bsNewPostContractFormInformation;
use PCK\Buildspace\ClaimCertificate AS bsClaimCertificate;

class DashboardGroup extends Model
{
    const TYPE_DEVELOPER       = 1;
    const TYPE_MAIN_CONTRACTOR = 2;
    const TYPE_E_BIDDING        = 3;

    const TYPE_DEVELOPER_TEXT = 'Developer Dashboard';
    const TYPE_MAIN_CONTRACTOR_TEXT = 'Main Contractor Dashboard';
    const TYPE_E_BIDDING_TEXT = 'E-Bidding Dashboard';

    protected $table      = 'dashboard_groups';
    protected $primaryKey = 'type';

    public function getName()
    {
        if(!is_null($this->title) and strlen($this->title))
        {
            return $this->title;
        }
        
        switch($this->type)
        {
            case self::TYPE_DEVELOPER:
                return self::TYPE_DEVELOPER_TEXT;
            case self::TYPE_MAIN_CONTRACTOR:
                return self::TYPE_MAIN_CONTRACTOR_TEXT;
            case self::TYPE_E_BIDDING:
                return self::TYPE_E_BIDDING_TEXT;
            default:
                throw new \Exception('Invalid dashboard group type');
        }
    }

    public function users()
    {
        return $this->belongsToMany('PCK\Users\User', 'dashboard_groups_users', 'dashboard_group_type', 'user_id');
    }

    public function excludedProjects()
    {
        return $this->belongsToMany('PCK\Projects\Project', 'dashboard_groups_excluded_projects', 'dashboard_group_type', 'project_id');
    }

    public function getPostContractProjects(Country $country=null, Array $fromDate=null, Array $toDate=null)
    {
        $query = \DB::table('projects')->join('countries', 'projects.country_id', '=', 'countries.id')
        ->leftJoin('dashboard_groups_excluded_projects AS x', function($join)
        {
            $join->on('x.project_id', '=', 'projects.id');
            $join->on('x.dashboard_group_type','=', \DB::raw($this->type));
        })
        ->select('projects.id')
        ->where('projects.status_id', StatusType::STATUS_TYPE_POST_CONTRACT)
        ->whereNull('x.project_id')
        ->whereNull('projects.deleted_at');

        if($country)
        {
            $query->where('countries.id', $country->id);
        }

        $eprojectIds = $query->distinct()->lists('projects.id');
        
        $projects = [];

        if(!empty($eprojectIds))
        {
            if(!empty($fromDate) && !empty($toDate))
            {
                $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
                $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));
                
                $eprojectIds = \DB::connection('buildspace')
                ->table('bs_project_structures AS p')
                ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
                ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
                ->whereIn('i.eproject_origin_id', $eprojectIds)
                ->where("f.awarded_date", ">=", $startDate)
                ->where("f.awarded_date", "<=", $endDate)
                ->whereNull('p.deleted_at')
                ->select('i.eproject_origin_id')
                ->groupBy('i.eproject_origin_id')
                ->lists('i.eproject_origin_id');
            }

            $projects = Project::whereIn('id', $eprojectIds)->get();
        }
        
        return $projects;
    }

    public function getClosedTenderProjects(Country $country=null, Array $fromDate=[], Array $toDate=[])
    {
        $dbh=\DB::getPdo();

        $countrySql = ($country) ? " AND countries.id = ".$country->id." " : "";

        $dateSql = "";
        if(!empty($fromDate) && !empty($toDate))
        {
            $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
            $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

            $dateSql = " AND ar.updated_at::timestamp::date >= '".$startDate."' AND ar.updated_at::timestamp::date <= '".$endDate."'";
        }

        $stmt = $dbh->prepare("SELECT p.id
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT max(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = p.id
            JOIN tenders AS t ON tx.id = t.id
            JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
            JOIN companies AS c ON t.currently_selected_tenderer_id = c.id
            JOIN open_tender_award_recommendation_bill_details b ON b.tender_id = t.id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_CLOSED_TENDER."
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND t.open_tender_verification_status = ".Tender::SUBMISSION."
            AND t.currently_selected_tenderer_id IS NOT NULL
            AND ar.status = ".OpenTenderAwardRecommendationStatus::APPROVED."
            ".$dateSql."
            AND x.project_id IS NULL
            AND p.deleted_at IS NULL
            ".$countrySql."
            GROUP BY p.id
        ");

        $stmt->execute();

        $projectIds = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        $projects = [];
        if(!empty($projectIds))
        {
            $projects = Project::whereIn('id', $projectIds)->get();
        }

        return $projects;
    }

    public function getClosedTenderContractSumRecords(Country $country, Array $fromDate, Array $toDate)
    {
        $projects = $this->getClosedTenderProjects($country, $fromDate, $toDate);

        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [];

        unset($projects);

        $records = [];

        if(!empty($projectIds))
        {
            $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
            $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

            $dbh=\DB::getPdo();

            $stmt = $dbh->prepare("SELECT p.id, t.id AS tender_id, t.created_at AS tender_created_at, t.currently_selected_tenderer_id, c.name, ct.tender_amount AS contract_sum
                FROM projects AS p
                JOIN countries ON p.country_id = countries.id
                JOIN (SELECT max(id) AS id, project_id
                FROM tenders
                GROUP BY project_id) tx ON tx.project_id = p.id
                JOIN tenders AS t ON tx.id = t.id
                JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
                JOIN companies AS c ON t.currently_selected_tenderer_id = c.id
                JOIN company_tender ct ON ct.tender_id = t.id AND ct.company_id = c.id
                WHERE p.id IN (".implode(',', $projectIds).")
                AND p.status_id = ".StatusType::STATUS_TYPE_CLOSED_TENDER."
                AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
                AND t.open_tender_verification_status = ".Tender::SUBMISSION."
                AND t.currently_selected_tenderer_id IS NOT NULL
                AND ar.status = ".OpenTenderAwardRecommendationStatus::APPROVED."
                AND ar.updated_at::timestamp::date >= '".$startDate."' AND ar.updated_at::timestamp::date <= '".$endDate."'
                AND p.deleted_at IS NULL
            ");

            $stmt->execute();

            $records = $stmt->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC);
        }

        return $records;
    }

    public function getCountries()
    {
        $postContractProjects  = $this->getPostContractProjects();
        $closedTenderProjects  = $this->getClosedTenderProjects();

        if($closedTenderProjects)
            $projects = ($postContractProjects) ? $postContractProjects->merge($closedTenderProjects) : $closedTenderProjects;
        else
            $projects = $postContractProjects;
        
        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [];

        unset($projects);

        $countries = [];
        if(!empty($projectIds))
        {
            $countries = Country::join('projects', 'projects.country_id', '=', 'countries.id')
            ->select('countries.*')
            ->whereIn('projects.id', $projectIds)
            ->whereNull('projects.deleted_at')
            ->groupby('countries.id')
            ->distinct()
            ->orderby('countries.country', 'ASC')
            ->get();
        }
        
        return $countries;
    }

    public function getDeveloperDashboardADataByUserAndCountry(User $user, Country $country, Array $fromDate, Array $toDate)
    {
        if(!$user->dashboardGroup() or $user->dashboardGroup()->type != self::TYPE_DEVELOPER)
        {
            return [];
        }

        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);
        
        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];
        
        $overallBudgetRecords  = [];
        $contractSumRecords    = [];
        $variationOrderRecords = [];

        unset($projects);
        
        if(!empty($projectIds))
        {
            $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
            $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

            $bsProjectIds = \DB::connection('buildspace')
                ->table('bs_project_structures AS p')
                ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
                ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
                ->whereIn('i.eproject_origin_id', $projectIds)
                ->where("f.awarded_date", ">=", $startDate)
                ->where("f.awarded_date", "<=", $endDate)
                ->whereNull('p.deleted_at')
                ->select('p.id')
                ->groupBy('p.id')
                ->lists('p.id');

            //$overallBudgetRecords  = bsProject::getOverallTotalByProjects($bsProjectIds); //I just commented this first just in case we might need to call this method to get PTE
            $contractSumRecords    = bsPostContract::getTotalContractSumByProjects($bsProjectIds);
            $variationOrderRecords = bsVariationOrder::getTotalVariationOrderByProjects($bsProjectIds);

            $overallBudgetRecords = $this->getTotalBudgetByProjectIds($projectIds, $country, $fromDate, $toDate);
        }
        
        $totalBudget         = array_sum($overallBudgetRecords);
        $totalContractSum    = array_sum($contractSumRecords);
        $totalVariationOrder = array_sum($variationOrderRecords);

        $closedTenderContractSumRecords = $this->getClosedTenderContractSumRecords($country, $fromDate, $toDate);

        foreach($closedTenderContractSumRecords as $projectId => $record)
        {
            $totalContractSum += $record['contract_sum'];
        }

        unset($closedTenderContractSumRecords);

        $projectInfo = [
            'currency'             => $country->currency_code,
            'overall_budget'       => $totalBudget,
            'awarded_contract_sum' => $totalContractSum,
            'variation_order'      => $totalVariationOrder,
        ];

        $projectInfo['overrun_amount']     = $projectInfo['overall_budget'] - ($projectInfo['awarded_contract_sum'] + $projectInfo['variation_order']);
        $projectInfo['overrun_percentage'] = ($projectInfo['overall_budget']) ? $projectInfo['overrun_amount'] / $projectInfo['overall_budget'] * 100 : 0;

        return $projectInfo;
    }

    public function getDeveloperSubsidiaries(User $user, Country $country, Array $fromDate, Array $toDate, Array $filterParams=[], Array $pagination=[])
    {
        if(!$user->dashboardGroup() or $user->dashboardGroup()->type != self::TYPE_DEVELOPER)
        {
            return [];
        }

        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);

        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        if(empty($projectIds))
        {
            return [];
        }

        $dbh=\DB::getPdo();

        $stmt = $dbh->prepare("WITH RECURSIVE tree AS (
            SELECT id, ARRAY[]::integer[] AS path
            FROM subsidiaries
            WHERE parent_id IS NULL
            UNION ALL
            SELECT subsidiaries.id, subsidiaries.id || tree.path || subsidiaries.parent_id
            FROM subsidiaries, tree
            WHERE subsidiaries.parent_id = tree.id
        )
        SELECT DISTINCT
        CASE WHEN (btrim(tree.path::text, '{}') IS NULL OR btrim(tree.path::text, '{}') = '') THEN subsidiaries.id::text ELSE btrim(tree.path::text, '{}') END AS path
        FROM tree
        JOIN subsidiaries ON subsidiaries.id = tree.id
        JOIN projects on projects.subsidiary_id = subsidiaries.id
        JOIN states ON states.id = projects.state_id
        JOIN companies ON companies.id = subsidiaries.company_id
        WHERE projects.id IN (".implode(',', $projectIds).")
        AND states.country_id = ".$country->id."
        AND projects.status_id IN (".StatusType::STATUS_TYPE_CLOSED_TENDER.", ".StatusType::STATUS_TYPE_POST_CONTRACT.")
        AND projects.deleted_at IS NULL");

        $stmt->execute();

        $subsidiaryIdRows = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        $subsidiaryIds = [];

        foreach($subsidiaryIdRows as $ids)
        {
            if($ids)
            {
                $arr = explode(',', $ids);
                $subsidiaryIds = array_merge($subsidiaryIds, $arr);
            }
        }

        $subsidiaries = [];
        if(!empty($subsidiaryIds))
        {
            $subsidiaryIds = array_unique($subsidiaryIds);

            $subsidiaries = Subsidiary::join('companies AS c', 'c.id', '=', 'subsidiaries.company_id')
            ->whereIn('subsidiaries.id', $subsidiaryIds)
            ->select('subsidiaries.id', 'subsidiaries.name', 'subsidiaries.parent_id', 'c.id AS company_id', 'c.name AS company_name')
            ->groupBy('subsidiaries.id')
            ->groupBy('c.id')
            ->orderBy('subsidiaries.parent_id', 'DESC')
            ->get()
            ->toArray();

            $subsidiaries = $this->transformTree($subsidiaries);
        }

        return $subsidiaries;
    }

    private function transformTree($treeArray, $parentId = null)
    {
        $output = [];

        foreach ($treeArray as $node)
        {
            if ($node['parent_id'] == $parentId)
            {
                $children = $this->transformTree($treeArray, $node['id']);

                if ($children)
                {
                    $node['_children'] = $children;
                }

                $output[] = $node;

                unset($node);
            }
        }
        
        return $output;
    }

    public function getDeveloperDashboardBDataByUserAndCountry(User $user, Country $country, Array $subsidiaryIds, Array $fromDate, Array $toDate)
    {
        if(!$user->dashboardGroup() or $user->dashboardGroup()->type != self::TYPE_DEVELOPER)
        {
            return [];
        }

        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);

        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $query = Subsidiary::join('projects AS p', 'p.subsidiary_id', '=', 'subsidiaries.id')
            ->join('states AS st', 'st.id', '=', 'p.state_id')
            ->where('st.country_id', $country->id)
            ->whereIn('p.id', $projectIds)
            ->whereNull('p.deleted_at');

        if(!empty($subsidiaryIds))
        {
            $query->whereIn('subsidiaries.id', $subsidiaryIds);
        }

        $projects = $query->select('p.id', 'p.subsidiary_id', 'subsidiaries.name AS subsidiary_name')
            ->orderBy('subsidiaries.name', 'ASC')
            ->get()
            ->toArray();
        
        $projectBySubsidiaries = [];

        foreach($projects as $project)
        {
            if(!array_key_exists($project['subsidiary_id'], $projectBySubsidiaries))
            {
                $projectBySubsidiaries[$project['subsidiary_id']] = [
                    'rownum'               => 0,
                    'name'                 => $project['subsidiary_name'],
                    'overall_budget'       => 0,
                    'awarded_contract_sum' => 0,
                    'variation_order'      => 0,
                    'overrun_amount'       => 0,
                    'overrun_percentage'   => 0,
                    'projects'             => []
                ];
            }

            $projectBySubsidiaries[$project['subsidiary_id']]['projects'][] = $project['id'];
        }

        unset($projects);

        $closedTenderContractSumRecords = $this->getClosedTenderContractSumRecords($country, $fromDate, $toDate);

        $cnt = 1;
        foreach($projectBySubsidiaries as $subsidiaryId => $data)
        {
            $overallBudgetRecords  = [];
            $contractSumRecords    = [];
            $variationOrderRecords = [];

            if(!empty($data['projects']))
            {
                $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
                $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));
    
                $bsProjectIds = \DB::connection('buildspace')
                    ->table('bs_project_structures AS p')
                    ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
                    ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
                    ->whereIn('i.eproject_origin_id', array_values($data['projects']))
                    ->where("f.awarded_date", ">=", $startDate)
                    ->where("f.awarded_date", "<=", $endDate)
                    ->whereNull('p.deleted_at')
                    ->select('p.id')
                    ->groupBy('p.id')
                    ->lists('p.id');

                $overallBudgetRecords = $this->getTotalBudgetByProjectIds(array_values($data['projects']), $country, $fromDate, $toDate);

                //$overallBudgetRecords  = bsProject::getOverallTotalByProjects($bsProjectIds);
                $contractSumRecords    = bsPostContract::getTotalContractSumByProjects($bsProjectIds);
                $variationOrderRecords = bsVariationOrder::getTotalVariationOrderByProjects($bsProjectIds);

                foreach(array_values($data['projects']) as $projectId)
                {
                    if(array_key_exists($projectId, $closedTenderContractSumRecords))
                    {
                        $contractSumRecords[$projectId] = $closedTenderContractSumRecords[$projectId]['contract_sum'];

                        unset($closedTenderContractSumRecords[$projectId]);
                    }
                }
            }

            $totalBudget         = array_sum($overallBudgetRecords);
            $totalContractSum    = array_sum($contractSumRecords);
            $totalVariationOrder = array_sum($variationOrderRecords);

            $projectBySubsidiaries[$subsidiaryId]['rownum']               = $cnt;
            $projectBySubsidiaries[$subsidiaryId]['overall_budget']       = $totalBudget;
            $projectBySubsidiaries[$subsidiaryId]['awarded_contract_sum'] = $totalContractSum;
            $projectBySubsidiaries[$subsidiaryId]['variation_order']      = $totalVariationOrder;
            $projectBySubsidiaries[$subsidiaryId]['overrun_amount']       = $projectBySubsidiaries[$subsidiaryId]['overall_budget'] - ($projectBySubsidiaries[$subsidiaryId]['awarded_contract_sum'] + $projectBySubsidiaries[$subsidiaryId]['variation_order']);
            $projectBySubsidiaries[$subsidiaryId]['overrun_percentage']   = ($projectBySubsidiaries[$subsidiaryId]['overall_budget']) ? $projectBySubsidiaries[$subsidiaryId]['overrun_amount'] / $projectBySubsidiaries[$subsidiaryId]['overall_budget'] * 100 : 0;

            unset($projectBySubsidiaries[$subsidiaryId]['projects']);

            $cnt++;
        }

        return array_values($projectBySubsidiaries);//reindex array keys
    }

    public function getDeveloperDashboardCDataByUserAndCountry(User $user, Country $country, Array $subsidiaryIds=[], Array $fromDate, Array $toDate)
    {
        if(!$user->dashboardGroup() or $user->dashboardGroup()->type != self::TYPE_DEVELOPER)
        {
            return [];
        }

        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);

        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $query = WorkCategory::join('projects AS p', 'p.work_category_id', '=', 'work_categories.id')
            ->join('subsidiaries AS s', 's.id', '=', 'p.subsidiary_id')
            ->join('states AS st', 'st.id', '=', 'p.state_id')
            ->where('st.country_id', $country->id)
            ->whereIn('p.id', $projectIds)
            ->whereNull('p.deleted_at');

        if(!empty($subsidiaryIds))
        {
            $query->whereIn('s.id', $subsidiaryIds);
        }

        $projects = $query->select('p.id', 'work_categories.id AS work_category_id', 'p.subsidiary_id', 's.name AS subsidiary_name')
            ->orderBy('s.name', 'ASC')
            ->get()
            ->toArray();
        
        $projectBySubsidiaries = [];

        foreach($projects as $project)
        {
            if(!array_key_exists($project['subsidiary_id'], $projectBySubsidiaries))
            {
                $projectBySubsidiaries[$project['subsidiary_id']] = [
                    'name'                 => $project['subsidiary_name'],
                    'work_categories'      => []
                ];
            }

            if(!array_key_exists($project['work_category_id'], $projectBySubsidiaries[$project['subsidiary_id']]['work_categories']))
            {
                $projectBySubsidiaries[$project['subsidiary_id']]['work_categories'][$project['work_category_id']] = [
                    'overall_budget'       => 0,
                    'awarded_contract_sum' => 0,
                    'variation_order'      => 0,
                    'overrun_amount'       => 0,
                    'overrun_percentage'   => 0,
                    'projects'             => []
                ];
            }

            $projectBySubsidiaries[$project['subsidiary_id']]['work_categories'][$project['work_category_id']]['projects'][] = $project['id'];
        }

        unset($projects);

        $closedTenderContractSumRecords = $this->getClosedTenderContractSumRecords($country, $fromDate, $toDate);

        foreach($projectBySubsidiaries as $subsidiaryId => $projectBySubsidiary)
        {
            foreach($projectBySubsidiary['work_categories'] as $workCategoryId => $data)
            {
                $overallBudgetRecords  = [];
                $contractSumRecords    = [];
                $variationOrderRecords = [];

                if(!empty($data['projects']))
                {
                    $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
                    $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));
    
                    $bsProjectIds = \DB::connection('buildspace')
                        ->table('bs_project_structures AS p')
                        ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
                        ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
                        ->whereIn('i.eproject_origin_id', array_values($data['projects']))
                        ->where("f.awarded_date", ">=", $startDate)
                        ->where("f.awarded_date", "<=", $endDate)
                        ->whereNull('p.deleted_at')
                        ->select('p.id')
                        ->groupBy('p.id')
                        ->lists('p.id');

                    $overallBudgetRecords = $this->getTotalBudgetByProjectIds(array_values($data['projects']), $country, $fromDate, $toDate);

                    //$overallBudgetRecords  = bsProject::getOverallTotalByProjects($bsProjectIds);
                    $contractSumRecords    = bsPostContract::getTotalContractSumByProjects($bsProjectIds);
                    $variationOrderRecords = bsVariationOrder::getTotalVariationOrderByProjects($bsProjectIds);

                    foreach(array_values($data['projects']) as $projectId)
                    {
                        if(array_key_exists($projectId, $closedTenderContractSumRecords))
                        {
                            $contractSumRecords[$projectId] = $closedTenderContractSumRecords[$projectId]['contract_sum'];

                            unset($closedTenderContractSumRecords[$projectId]);
                        }
                    }
                }

                $totalBudget         = array_sum($overallBudgetRecords);
                $totalContractSum    = array_sum($contractSumRecords);
                $totalVariationOrder = array_sum($variationOrderRecords);

                $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overall_budget']       = $totalBudget;
                $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['awarded_contract_sum'] = $totalContractSum;
                $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['variation_order']      = $totalVariationOrder;
                $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overrun_amount']       = $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overall_budget'] - ($projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['awarded_contract_sum'] + $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['variation_order']);
                $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overrun_percentage']   = ($projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overall_budget']) ? $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overrun_amount'] / $projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overall_budget'] * 100 : 0;

                if(!$projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['overall_budget'] && !$projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['awarded_contract_sum'])
                {
                    unset($projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]);
                }
                
                unset($projectBySubsidiaries[$subsidiaryId]['work_categories'][$workCategoryId]['projects']);
            }
        }

        return array_values($projectBySubsidiaries);//reindex array keys
    }

    public function getMainContractorDashboardADataByUserAndCountry(User $user, Country $country, Array $fromDate, Array $toDate)
    {
        if(!$user->dashboardGroup() or $user->dashboardGroup()->type != self::TYPE_MAIN_CONTRACTOR)
        {
            return [];
        }

        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);

        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $mainContractIds = Project::join('states AS s', 's.id', '=', 'projects.state_id')
            ->where('s.country_id', $country->id)
            ->whereIn('projects.id', $projectIds)
            ->whereNull('projects.parent_project_id')
            ->whereNull('projects.deleted_at')
            ->select('projects.*')
            ->lists('projects.id');
        
        $subContractIds = Project::join('states AS s', 's.id', '=', 'projects.state_id')
            ->where('s.country_id', $country->id)
            ->whereIn('projects.id', $projectIds)
            ->whereNotNull('projects.parent_project_id')
            ->whereNull('projects.deleted_at')
            ->select('projects.*')
            ->lists('projects.id');
            
        $mainContractValues = $this->getContractSumAmountByProjectIds($mainContractIds, $country, $fromDate, $toDate);
        $subContractValues  = $this->getContractSumAmountByProjectIds($subContractIds, $country, $fromDate, $toDate);
        
        $top5Highest = [];

        if($mainContractValues)
        {
            arsort($mainContractValues);
            $top5Highest = array_slice($mainContractValues, 0, 5, true);

            $projects = Project::join('states AS s', 's.id', '=', 'projects.state_id')
                ->whereIn('projects.id', array_keys($top5Highest))
                ->whereNull('projects.deleted_at')
                ->select('projects.*')
                ->get();
            
            $subProjects = Project::whereIn('parent_project_id', array_keys($top5Highest))
                ->whereIn('status_id', [StatusType::STATUS_TYPE_CLOSED_TENDER, StatusType::STATUS_TYPE_POST_CONTRACT])
                ->whereNull('deleted_at')
                ->select('id', 'parent_project_id')
                ->get();
            
            $subProjectIds = [];
            foreach($subProjects as $subProject)
            {
                if(!array_key_exists($subProject->parent_project_id, $subProjectIds))
                {
                    $subProjectIds[$subProject->parent_project_id] = [];
                }

                $subProjectIds[$subProject->parent_project_id][] = $subProject->id;
            }

            foreach($projects as $project)
            {
                if(array_key_exists($project->id, $top5Highest))
                {
                    $totalSubCon = 0;

                    if(array_key_exists($project->id, $subProjectIds) && !empty($subProjectIds[$project->id]))
                    {
                        $subContractValues  = $this->getContractSumAmountByProjectIds($subProjectIds[$project->id], $country, $fromDate, $toDate);
                        $totalSubCon        = array_sum($subContractValues);
                    }

                    $mainContractTotal = $top5Highest[$project->id];

                    $top5Highest[$project->id] = [
                        'id'                  => $project->id,
                        'title'               => $project->title,
                        'main_contract_total' => $mainContractTotal,
                        'sub_contract_total'  => $totalSubCon,
                        'profit'              => ($mainContractTotal - $totalSubCon)
                    ];
                }
            }
        }
        
        return [
            'main_contract_total_sum' => array_sum($mainContractValues),
            'sub_contract_total_sum'  => array_sum($subContractValues),
            'highest_main_contracts'  => $top5Highest
        ];
    }

    public function getMainContracts(User $user, Country $country, Array $fromDate, Array $toDate, Array $filterParams=[], Array $pagination=[])
    {
        if(!$user->dashboardGroup() or $user->dashboardGroup()->type != self::TYPE_MAIN_CONTRACTOR)
        {
            return [];
        }

        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);

        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $query = Subsidiary::join('projects AS p', 'p.subsidiary_id', '=', 'subsidiaries.id')
            ->join('states AS st', 'st.id', '=', 'p.state_id')
            ->join('companies AS c', 'c.id', '=', 'subsidiaries.company_id')
            ->whereNull('p.parent_project_id')
            ->where('st.country_id', $country->id)
            ->whereIn('p.id', $projectIds);

        foreach($filterParams as $columnName => $val) 
        {
            switch(strtolower($columnName))
            {
                case 'project_title':
                    $query->where('p.title', 'ILIKE', '%'.trim($val).'%');
                    break;
                case 'reference':
                    $query->where('p.reference', 'ILIKE', '%'.trim($val).'%');
                    break;
                case 'subsidiary_name':
                    $query->where('subsidiaries.name', 'ILIKE', '%'.trim($val).'%');
                    break;
            }
        }

        $query->whereNull('p.deleted_at')
        ->select('p.id AS project_id', 'p.title AS project_title', 'p.reference', 'subsidiaries.id AS subsidiary_id', 'subsidiaries.name AS subsidiary_name', 'c.id AS company_id', 'c.name AS company_name');
        
        $page  = 1;
        $limit = 0;
        $totalPages = 0;

        if($pagination)
        {
            $total      = $query->count(\DB::raw('DISTINCT p.id'));
            $limit      = (array_key_exists('page_size', $pagination)) ? $pagination['page_size'] : 0;
            $totalPages = ($limit) ? ceil($total / $limit) : 0;
            
            if(array_key_exists('current_page', $pagination))
            {
                $page = max($pagination['current_page'], 1);
                $page = min($pagination['current_page'], $totalPages);
            }

            $skip  = $limit * ($page - 1);
            $query = $query->take($limit)->skip($skip);
        }
        
        $mainContracts = $query->orderBy('p.title', 'ASC')
            ->orderBy('subsidiaries.name', 'ASC')
            ->get()
            ->toArray();
        
        $cnt = 1;
        $projectIds = [];
        foreach($mainContracts as $k => $mainContract)
        {
            $mainContracts[$k]['rownum']             = $cnt + (($page-1) * $limit);
            $mainContracts[$k]['contract_value']     = 0;
            $mainContracts[$k]['sub_contract_total'] = 0;
            $mainContracts[$k]['profit_amount']      = 0;
            $mainContracts[$k]['profit_percentage']  = 0;
            $mainContracts[$k]['subsidiaries']       = [];

            $projectIds[] = $mainContract['project_id'];

            $cnt++;
        }

        if(!empty($projectIds))
        {
            $query = Subsidiary::join('projects AS p', 'p.subsidiary_id', '=', 'subsidiaries.id')
            ->join('states AS st', 'st.id', '=', 'p.state_id')
            ->join('companies AS c', 'c.id', '=', 'subsidiaries.company_id')
            ->leftJoin('dashboard_groups_excluded_projects AS x', function($join)
            {
                $join->on('x.project_id', '=', 'p.id');
                $join->on('x.dashboard_group_type','=', \DB::raw(self::TYPE_MAIN_CONTRACTOR));
            })
            ->whereNull('x.project_id')
            ->whereIn('p.parent_project_id', $projectIds);

            $query->whereIn('p.status_id', [StatusType::STATUS_TYPE_CLOSED_TENDER, StatusType::STATUS_TYPE_POST_CONTRACT]);

            $subsidiariesRecords = $query->whereNull('p.deleted_at')
            ->select('p.parent_project_id', 'p.id AS project_id', 'p.title AS project_title', 'p.reference', 'subsidiaries.id AS subsidiary_id', 'subsidiaries.name AS subsidiary_name', 'c.id AS company_id', 'c.name AS company_name')
            ->orderBy('p.title', 'ASC')
            ->orderBy('subsidiaries.name', 'ASC')
            ->get()
            ->toArray();

            foreach($mainContracts as $idx => $mainContract)
            {
                foreach($subsidiariesRecords as $k => $subsidiary)
                {
                    if($subsidiary['parent_project_id'] == $mainContract['project_id'])
                    {
                        $mainContracts[$idx]['subsidiaries'][] = [
                            'project_id'      => $subsidiary['project_id'],
                            'project_title'   => $subsidiary['project_title'],
                            'subsidiary_id'   => $subsidiary['subsidiary_id'],
                            'subsidiary_name' => $subsidiary['subsidiary_name'],
                            'company_id'      => $subsidiary['company_id'],
                            'company_name'    => $subsidiary['company_name'],
                            'contract_value'  => 0
                        ];

                        unset($subsidiariesRecords[$k]);
                    }
                }
            }

            $mainContractValues = $this->getContractSumAmountByProjectIds($projectIds, $country, $fromDate, $toDate);

            foreach($mainContracts as $idx => $mainContract)
            {
                if(array_key_exists($mainContract['project_id'], $mainContractValues))
                {
                    $mainContracts[$idx]['contract_value'] = $mainContractValues[$mainContract['project_id']];
                }

                $subConTotalSum = 0;

                if(!empty($mainContract['subsidiaries']))
                {
                    $ids = array_column($mainContract['subsidiaries'], 'project_id');

                    $subContractValues = $this->getContractSumAmountByProjectIds($ids, $country, $fromDate, $toDate);

                    foreach($mainContract['subsidiaries'] as $key => $subContract)
                    {
                        if(array_key_exists($subContract['project_id'], $subContractValues))
                        {
                            $mainContracts[$idx]['subsidiaries'][$key]['contract_value'] = $subContractValues[$subContract['project_id']];

                            $subConTotalSum += $subContractValues[$subContract['project_id']];
                        }
                    }
                }

                $profitAmount = $mainContracts[$idx]['contract_value'] - $subConTotalSum;

                $mainContracts[$idx]['sub_contract_total'] = $subConTotalSum;
                $mainContracts[$idx]['profit_amount']      = $profitAmount;
                $mainContracts[$idx]['profit_percentage']  = ($mainContracts[$idx]['contract_value']) ? ($profitAmount / $mainContracts[$idx]['contract_value']) * 100 : 0;
            }
        }

        return [
            'last_page' => $totalPages,
            'data'      => $mainContracts
        ];
    }

    private function getContractSumAmountByProjectIds(Array $projectIds, Country $country, Array $fromDate, Array $toDate, $withVariationOrder=true)
    {
        if(empty($projectIds))
            return [];

        $mappedProjectIds = [];

        $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
        $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

        $bsProjectRecords = \DB::connection('buildspace')
            ->table('bs_project_structures AS p')
            ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
            ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
            ->whereIn('i.eproject_origin_id', array_values($projectIds))
            ->where("f.awarded_date", ">=", $startDate)
            ->where("f.awarded_date", "<=", $endDate)
            ->whereNull('p.deleted_at')
            ->select('i.id', 'i.project_structure_id', 'i.eproject_origin_id')
            ->groupBy('i.id')
            ->get();
        
        foreach($bsProjectRecords as $bsProjectRecord)
        {
            $mappedProjectIds[$bsProjectRecord->project_structure_id] = $bsProjectRecord->eproject_origin_id;
        }

        unset($bsProjectRecords);

        $mainContractValues = bsPostContract::getTotalContractSumByProjects(array_keys($mappedProjectIds));

        if($withVariationOrder)
        {
            $variationOrders = bsVariationOrder::getTotalVariationOrderByProjects(array_keys($mappedProjectIds));

            foreach($variationOrders as $pid => $value)
            {
                if(!array_key_exists($pid, $mainContractValues))
                {
                    $mainContractValues[$pid] = 0;
                }

                $mainContractValues[$pid] += $value;
            }
        }
        
        $tmpMainContractValues = [];
        
        foreach($mainContractValues as $pid => $val)
        {
            $tmpMainContractValues[$mappedProjectIds[$pid]] = $val;
        }

        $closedTenderContractSumRecords = $this->getClosedTenderContractSumRecords($country, $fromDate, $toDate);
        foreach($closedTenderContractSumRecords as $projectId => $record)
        {
            $tmpMainContractValues[$projectId] = $record['contract_sum'];

            unset($closedTenderContractSumRecords[$projectId]);
        }

        return $tmpMainContractValues;
    }

    public function getOverallBudgetVersusContractSumAndVOByWorkCategories(User $user, Country $country, Array $fromDate, Array $toDate)
    {
        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);
        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $closedTenderContractSumRecords = $this->getClosedTenderContractSumRecords($country, $fromDate, $toDate);

        $mappedProjectIds = [];

        $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
        $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

        $bsProjectRecords = \DB::connection('buildspace')
            ->table('bs_project_structures AS p')
            ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
            ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
            ->whereIn('i.eproject_origin_id', array_values($projectIds))
            ->where("f.awarded_date", ">=", $startDate)
            ->where("f.awarded_date", "<=", $endDate)
            ->whereNull('p.deleted_at')
            ->select('i.id', 'i.project_structure_id', 'i.eproject_origin_id')
            ->groupBy('i.id')
            ->get();
        
        foreach($bsProjectRecords as $bsProjectRecord)
        {
            $mappedProjectIds[$bsProjectRecord->project_structure_id] = $bsProjectRecord->eproject_origin_id;
        }

        unset($bsProjectRecords);

        $overallBudgetRecords = $this->getTotalBudgetByProjectIds($projectIds, $country, $fromDate, $toDate);

        //$overallBudgetRecords  = bsProject::getOverallTotalByProjects(array_keys($mappedProjectIds));
        $contractSumRecords    = bsPostContract::getTotalContractSumByProjects(array_keys($mappedProjectIds));
        $variationOrderRecords = bsVariationOrder::getTotalVariationOrderByProjects(array_keys($mappedProjectIds));

        $workCategories = WorkCategory::join('projects AS p', 'p.work_category_id', '=', 'work_categories.id')
            ->whereIn('p.id', $projectIds)
            ->whereNull('p.deleted_at')
            ->select('work_categories.id', 'work_categories.name', 'p.id AS project_id')
            ->distinct()
            ->orderby('work_categories.name', 'ASC')
            ->get();
        
        $records = [];
        foreach($workCategories as $idx => $workCategory)
        {
            if(!array_key_exists($workCategory->id, $records))
            {
                $records[$workCategory->id] = [
                    'name'                 => $workCategory->name,
                    'overall_budget'       => 0,
                    'awarded_contract_sum' => 0,
                    'variation_order'      => 0
                ];
            }

            if(array_key_exists($workCategory->project_id, $overallBudgetRecords))
            {
                $records[$workCategory->id]['overall_budget'] += $overallBudgetRecords[$workCategory->project_id];
                unset($overallBudgetRecords[$workCategory->project_id]);
            }
            
            foreach($contractSumRecords as $bsProjectId => $value)
            {
                if(array_key_exists($bsProjectId, $mappedProjectIds) && $mappedProjectIds[$bsProjectId] == $workCategory->project_id)
                {
                    $records[$workCategory->id]['awarded_contract_sum'] += $value;

                    unset($contractSumRecords[$bsProjectId]);
                }
            }

            if(array_key_exists($workCategory->project_id, $closedTenderContractSumRecords))
            {
                $records[$workCategory->id]['awarded_contract_sum'] += $closedTenderContractSumRecords[$workCategory->project_id]['contract_sum'];

                unset($closedTenderContractSumRecords[$workCategory->project_id]);
            }

            foreach($variationOrderRecords as $bsProjectId => $value)
            {
                if(array_key_exists($bsProjectId, $mappedProjectIds) && $mappedProjectIds[$bsProjectId] == $workCategory->project_id)
                {
                    $records[$workCategory->id]['variation_order'] += $value;

                    unset($variationOrderRecords[$bsProjectId]);
                }
            }

            unset($workCategories[$idx]);
        }

        return array_values($records);
    }

    public function getProcurementMethodSummary(User $user, Country $country, Array $fromDate, Array $toDate)
    {
        $dbh=\DB::getPdo();

        $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
        $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

        $postContractProjectIds = \DB::connection('buildspace')
            ->table('bs_project_structures AS p')
            ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
            ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
            ->where("f.awarded_date", ">=", $startDate)
            ->where("f.awarded_date", "<=", $endDate)
            ->whereNotNull('i.eproject_origin_id')
            ->whereNull('p.deleted_at')
            ->select('i.eproject_origin_id')
            ->lists('i.eproject_origin_id');
        
        $records = [];

        if(!empty($postContractProjectIds))
        {
            $stmt = $dbh->prepare("SELECT s.id, s.name, pm.id AS procurement_method_id, pm.name AS procurement_method_name, COUNT(pm.id) AS total
            FROM subsidiaries s 
            JOIN projects AS p ON p.subsidiary_id = s.id
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT min(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = p.id
            JOIN tenders AS t ON tx.id = t.id
            JOIN tender_lot_information AS rot ON rot.tender_id = t.id
            JOIN procurement_methods pm ON rot.procurement_method_id = pm.id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_POST_CONTRACT."
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND t.open_tender_verification_status = ".Tender::SUBMISSION."
            AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
            AND p.id IN (".implode(',', $postContractProjectIds).")
            AND x.project_id IS NULL
            AND countries.id = ".$country->id."
            AND p.deleted_at IS NULL
            GROUP BY s.id, pm.id
            ORDER BY s.name");

            $stmt->execute();

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach($rows as $row)
            {
                if(!array_key_exists($row['id'], $records))
                {
                    $records[$row['id']] = [
                        'name' => $row['name'],
                        'procurement_methods' => []
                    ];
                }

                if(!array_key_exists($row['procurement_method_id'], $records[$row['id']]['procurement_methods']))
                {
                    $records[$row['id']]['procurement_methods'][$row['procurement_method_id']] = [
                        'name' => $row['name'],
                        'procurement_method_name' => $row['procurement_method_name'],
                        'total' => 0
                    ];
                }

                $records[$row['id']]['procurement_methods'][$row['procurement_method_id']]['total'] += $row['total'];
            }
        }

        $stmt = $dbh->prepare("SELECT s.id, s.name, pm.id AS procurement_method_id, pm.name AS procurement_method_name, COUNT(pm.id) AS total
        FROM subsidiaries s 
        JOIN projects AS p ON p.subsidiary_id = s.id
        JOIN countries ON p.country_id = countries.id
        JOIN (SELECT min(id) AS id, project_id
        FROM tenders
        GROUP BY project_id) otx ON otx.project_id = p.id
        JOIN tenders AS ori_t ON otx.id = ori_t.id
        JOIN tender_lot_information AS rot ON rot.tender_id = ori_t.id
        JOIN (SELECT max(id) AS id, project_id
        FROM tenders
        GROUP BY project_id) tx ON tx.project_id = ori_t.project_id
        JOIN tenders AS t ON tx.id = t.id
        JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
        JOIN procurement_methods pm ON rot.procurement_method_id = pm.id
        LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
        WHERE p.status_id = ".StatusType::STATUS_TYPE_CLOSED_TENDER."
        AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
        AND t.open_tender_verification_status = ".Tender::SUBMISSION."
        AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
        AND ar.status = ".OpenTenderAwardRecommendationStatus::APPROVED."
        AND ar.updated_at::timestamp::date >= '".$startDate."' AND ar.updated_at::timestamp::date <= '".$endDate."'
        AND x.project_id IS NULL
        AND countries.id = ".$country->id."
        AND p.deleted_at IS NULL
        GROUP BY s.id, pm.id
        ORDER BY s.name");

        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($rows as $row)
        {
            if(!array_key_exists($row['id'], $records))
            {
                $records[$row['id']] = [
                    'name' => $row['name'],
                    'procurement_methods' => []
                ];
            }

            if(!array_key_exists($row['procurement_method_id'], $records[$row['id']]['procurement_methods']))
            {
                $records[$row['id']]['procurement_methods'][$row['procurement_method_id']] = [
                    'name' => $row['name'],
                    'procurement_method_name' => $row['procurement_method_name'],
                    'total' => 0
                ];
            }

            $records[$row['id']]['procurement_methods'][$row['procurement_method_id']]['total'] += $row['total'];
        }
        
        return $records;
    }

    public function getProjectStatusSummary(User $user, Country $country, Array $fromDate, Array $toDate)
    {
        $projects = $user->getDashboardProjects($country, $fromDate, $toDate);
        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $dbh=\DB::getPdo();

        $projectStatuses = [
            Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER,
            Project::STATUS_TYPE_LIST_OF_TENDERER,
            Project::STATUS_TYPE_CALLING_TENDER,
            Project::STATUS_TYPE_CLOSED_TENDER,
            Project::STATUS_TYPE_POST_CONTRACT
        ];

        if($projectIds)
        {
            $stmt = $dbh->prepare("SELECT s.id, s.name, p.status_id, COUNT(p.status_id) AS total
            FROM subsidiaries s 
            JOIN projects AS p ON p.subsidiary_id = s.id
            WHERE p.status_id IN (".implode(', ', $projectStatuses).")
            AND p.id IN (".implode(", ", $projectIds).")
            AND p.deleted_at IS NULL
            GROUP BY s.id, p.status_id
            ORDER BY s.name");

            $stmt->execute();

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $records = [];

            foreach($rows as $row)
            {
                if(!array_key_exists($row['id'], $records))
                {
                    $records[$row['id']] = [
                        'id'     => $row['id'],
                        'name'   => $row['name'],
                        'status' => []
                    ];
                }

                $records[$row['id']]['status'][] = [
                    'id'    => $row['status_id'],
                    'total' => $row['total']
                ];
            }
            
            return array_values($records);
        }
        
        return [];
    }

    public function getWaiverStatusSummary(Country $country, $waiverType, Array $fromDate, Array $toDate)
    {
        $projects = $this->getPostContractProjects($country, $fromDate, $toDate);
        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

        unset($projects);

        $bsProjectRecords = [];

        $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
        $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

        $query = \DB::connection('buildspace')
        ->table('bs_project_structures AS p')
        ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
        ->join('bs_new_post_contract_form_information AS pc',  'pc.project_structure_id', '=', 'i.project_structure_id')
        ->whereIn('i.eproject_origin_id', array_values($projectIds))
        ->where("pc.awarded_date", ">=", $startDate)
        ->where("pc.awarded_date", "<=", $endDate)
        ->whereNull('p.deleted_at');
        
        if($waiverType == 'auction')
        {
            $query->whereNotNull('pc.e_auction_waiver_option_type')
            ->select('i.eproject_origin_id', 'i.project_structure_id', 'e_auction_waiver_option_type AS waiver_option_type');
        }
        else
        {
            $query->whereNotNull('pc.e_tender_waiver_option_type')
            ->select('i.eproject_origin_id', 'i.project_structure_id', 'e_tender_waiver_option_type AS waiver_option_type');
        }

        array_map(function($item) use (&$bsProjectRecords) {
            $bsProjectRecords[$item->eproject_origin_id] = (Array)$item; // object to array
        }, $query->get());

        $projectWithWaiverIds = (!empty($bsProjectRecords)) ? array_keys($bsProjectRecords) : [-1];

        $projectsBySubsidiaries = Subsidiary::join('projects AS p', 'p.subsidiary_id', '=', 'subsidiaries.id')
            ->whereIn('p.id', $projectWithWaiverIds)
            ->whereNull('p.deleted_at')
            ->select('subsidiaries.id', 'subsidiaries.name', 'p.id AS project_id')
            ->distinct()
            ->orderby('subsidiaries.name', 'ASC')
            ->get()
            ->toArray();

        $records = [];

        foreach($projectsBySubsidiaries as $idx => $data)
        {
            if(!array_key_exists($data['id'], $records))
            {
                $records[$data['id']] = [
                    'name' => $data['name'],
                    'waiver_option_types' => []
                ];
            }

            if(array_key_exists($data['project_id'], $bsProjectRecords) && !array_key_exists($bsProjectRecords[$data['project_id']]['waiver_option_type'], $records[$data['id']]['waiver_option_types']))
            {
                $records[$data['id']]['waiver_option_types'][$bsProjectRecords[$data['project_id']]['waiver_option_type']] = [
                    'name' => bsNewPostContractFormInformation::getWaiverTypeText($bsProjectRecords[$data['project_id']]['waiver_option_type']),
                    'total' => 0
                ];
            }

            $records[$data['id']]['waiver_option_types'][$bsProjectRecords[$data['project_id']]['waiver_option_type']]['total']++;

            unset($bsProjectRecords[$data['project_id']], $projectsBySubsidiaries[$idx]);
        }

        return $records;
    }

    public function getWaiverOtherStatusDetails(Subsidiary $subsidiary, $waiverType, Country $country, Array $fromDate, Array $toDate)
    {
        $dbh=\DB::getPdo();

        $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
        $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));

        $stmt = $dbh->prepare("SELECT p.id, p.title AS project_title, wc.name AS work_category_name
        FROM subsidiaries s 
        JOIN projects AS p ON p.subsidiary_id = s.id
        JOIN countries AS c ON p.country_id = c.id
        JOIN work_categories AS wc ON p.work_category_id = wc.id
        LEFT JOIN dashboard_groups_excluded_projects AS x ON (x.project_id = p.id AND x.dashboard_group_type = ".\DB::raw($this->type).")
        WHERE p.subsidiary_id = ".$subsidiary->id."
        AND p.status_id = ".StatusType::STATUS_TYPE_POST_CONTRACT."
        AND c.id = ".$country->id."
        AND x.project_id IS NULL
        AND p.deleted_at IS NULL
        ORDER BY p.title");

        $stmt->execute();

        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $projectIds = (!empty($projects)) ? array_column($projects, 'id') : [-1];

        $waiverOptionType = ($waiverType=='tender') ? bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS : bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS;

        $bsProjectRecords = \DB::connection('buildspace')
            ->table('bs_project_structures AS p')
            ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
            ->join('bs_new_post_contract_form_information AS pc',  'pc.project_structure_id', '=', 'i.project_structure_id')
            ->join('bs_waiver_user_defined_options AS w', 'w.project_structure_id', '=', 'i.project_structure_id')
            ->whereIn('i.eproject_origin_id', array_values($projectIds))
            ->where("pc.awarded_date", ">=", $startDate)
            ->where("pc.awarded_date", "<=", $endDate)
            ->whereNull('p.deleted_at')
            ->where('w.waiver_option_type', $waiverOptionType)
            ->select('i.id', 'i.project_structure_id', 'i.eproject_origin_id', 'w.description')
            ->orderBy('p.created_at', 'DESC')
            ->orderBy('w.description', 'ASC')
            ->get();
        
        $records = [];

        foreach($bsProjectRecords as $bsProjectRecord)
        {
            $key = array_search($bsProjectRecord->eproject_origin_id, array_column($projects, 'id'));

            $record = $projects[$key];
            $record['waiver_option_description'] = $bsProjectRecord->description;

            $records[] = $record;
        }
        
        return $records;
    }

    public function getOverallCertifiedPayment(Country $country, Array $fromDate, Array $toDate)
    {
        $projects = $this->getPostContractProjects($country, $fromDate, $toDate);
        $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];
        
        $records = [];
        
        if(!empty($projectIds))
        {
            $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
            $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));
            
            $bsPdo = \DB::connection('buildspace')->getPdo();

            $stmt = $bsPdo->prepare("SELECT EXTRACT(year FROM l.updated_at) AS years,
            TO_CHAR(l.updated_at,'MM') AS mon, SUM(c.amount_certified) AS total
            FROM bs_claim_certificates AS c
            JOIN bs_claim_certificate_approval_logs AS l ON l.claim_certificate_id = c.id
            JOIN bs_post_contract_claim_revisions AS cr ON c.post_contract_claim_revision_id = cr.id
            JOIN bs_post_contracts AS pc ON cr.post_contract_id = pc.id
            JOIN bs_project_main_information AS i ON pc.project_structure_id = i.project_structure_id
            JOIN bs_project_structures AS p ON i.project_structure_id = p.id
            JOIN bs_new_post_contract_form_information AS f ON f.project_structure_id = p.id
            WHERE i.eproject_origin_id IN (".implode(',', $projectIds).")
            AND c.status = ".bsClaimCertificate::STATUS_TYPE_APPROVED."
            AND l.status = ".bsClaimCertificate::STATUS_TYPE_APPROVED."
            AND c.amount_certified <> 0
            AND f.awarded_date::timestamp::date >= '".$startDate."' AND f.awarded_date::timestamp::date <= '".$endDate."'
            AND p.deleted_at IS NULL
            AND cr.deleted_at IS NULL
            GROUP BY 1,2
            ORDER BY 1,2");

            $stmt->execute();

            $records = $stmt->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_ASSOC);
        }
        
        $cumulativeCost = 0;
        foreach($records as $year => $record)
        {
            foreach($record as $key => $data)
            {
                $cumulativeCost += $data['total'];
                $records[$year][$key]['cumulative_cost'] = $cumulativeCost;
            }
        }

        return $records;
    }

    public function getOverallCertifiedPaymentBySubsidiaries(Country $country, Array $subsidiaryIds, Array $fromDate, Array $toDate)
    {
        $dbh=\DB::getPdo();

        $stmt = $dbh->prepare("SELECT p.subsidiary_id, p.id 
        FROM projects AS p
        LEFT JOIN dashboard_groups_excluded_projects AS x ON (x.project_id = p.id AND x.dashboard_group_type = ".\DB::raw($this->type).")
        WHERE p.status_id = ".StatusType::STATUS_TYPE_POST_CONTRACT."
        AND p.subsidiary_id IN (".implode(',', $subsidiaryIds).")
        AND x.project_id IS NULL
        AND p.deleted_at IS NULL
        ORDER BY p.subsidiary_id, p.id");

        $stmt->execute();

        $projectsBySubsidiaries = $stmt->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_COLUMN);
        
        $projectIds = (!empty($projectsBySubsidiaries)) ? array_values(call_user_func_array('array_merge', $projectsBySubsidiaries)) : [];

        $bsRecords = [];

        if(!empty($projectIds))
        {
            $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
            $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));
            
            $bsPdo = \DB::connection('buildspace')->getPdo();

            $stmt = $bsPdo->prepare("SELECT i.eproject_origin_id, EXTRACT(year FROM l.updated_at) AS years,
            TO_CHAR(l.updated_at,'MM') AS mon, SUM(c.amount_certified) AS total
            FROM bs_claim_certificates AS c
            JOIN bs_claim_certificate_approval_logs AS l ON l.claim_certificate_id = c.id
            JOIN bs_post_contract_claim_revisions AS cr ON c.post_contract_claim_revision_id = cr.id
            JOIN bs_post_contracts AS pc ON cr.post_contract_id = pc.id
            JOIN bs_project_main_information AS i ON pc.project_structure_id = i.project_structure_id
            JOIN bs_project_structures As p ON i.project_structure_id = p.id
            JOIN bs_new_post_contract_form_information AS f ON f.project_structure_id = p.id
            WHERE i.eproject_origin_id IN (".implode(',', $projectIds).")
            AND c.status = ".bsClaimCertificate::STATUS_TYPE_APPROVED."
            AND l.status = ".bsClaimCertificate::STATUS_TYPE_APPROVED."
            AND f.awarded_date::timestamp::date >= '".$startDate."' AND f.awarded_date::timestamp::date <= '".$endDate."'
            AND c.amount_certified <> 0
            AND p.deleted_at IS NULL
            AND cr.deleted_at IS NULL
            GROUP BY i.eproject_origin_id, 2, 3
            ORDER BY i.eproject_origin_id, 2, 3");

            $stmt->execute();

            $bsRecords = $stmt->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_ASSOC);
        }

        $records = [];

        foreach($projectsBySubsidiaries as $subsidiaryId => $projectIds)
        {
            foreach($bsRecords as $eprojectId => $record)
            {
                if(in_array($eprojectId, $projectIds))
                {
                    if(!array_key_exists($subsidiaryId, $records))
                    {
                        $records[$subsidiaryId] = [];
                    }

                    foreach($record as $data)
                    {
                        if(!array_key_exists($data['years'], $records[$subsidiaryId]))
                        {
                            $records[$subsidiaryId][$data['years']] = [];
                        }

                        if(!array_key_exists($data['mon'], $records[$subsidiaryId][$data['years']]))
                        {
                            $records[$subsidiaryId][$data['years']][$data['mon']] = [
                                'total' => 0,
                                'cumulative_cost' => 0
                            ];

                            $records[$subsidiaryId][$data['years']][$data['mon']]['total'] += $data['total'];
                        }

                        ksort($records[$subsidiaryId][$data['years']]);//sorting month for cummulative calculation later
                    }
                }
            }

            if(array_key_exists($subsidiaryId, $records))
                ksort($records[$subsidiaryId]);//sort year
        }

        unset($bsRecords, $projectsBySubsidiaries);
        
        foreach($records as $subsidiaryId => $record)
        {
            $cumulativeCost = 0;

            foreach($record as $year => $data)
            {
                foreach($data as $month => $val)
                {
                    $cumulativeCost += $val['total'];
                    $records[$subsidiaryId][$year][$month]['cumulative_cost'] = $cumulativeCost;
                }
            }
        }

        return $records;
    }

    private function getTotalBudgetByProjectIds(Array $projectIds, Country $country, Array $fromDate, Array $toDate)
    {
        $dbh=\DB::getPdo();

        if(empty($projectIds))
        {
            $projectIds = [-1];
        }
        
        $startDate = date('Y-m-d', strtotime($fromDate['year']."-".sprintf("%02d", $fromDate['month'])."-1"));
        $endDate = date('Y-m-t', strtotime($toDate['year']."-".sprintf("%02d", $toDate['month'])."-1"));
        
        $postContractProjectIds = \DB::connection('buildspace')
            ->table('bs_project_structures AS p')
            ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
            ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
            ->whereIn('i.eproject_origin_id', $projectIds)
            ->where("f.awarded_date", ">=", $startDate)
            ->where("f.awarded_date", "<=", $endDate)
            ->where("i.status", bsProjectMainInformation::STATUS_POSTCONTRACT)
            ->whereNull('p.deleted_at')
            ->whereNotNull('i.eproject_origin_id')
            ->select('i.eproject_origin_id')
            ->lists('i.eproject_origin_id');
        
        $records = [];

        if(!empty($postContractProjectIds))
        {
            //query post contract projects with approved AR. If the approved AR budget amount is 0 then use ROT budget amount
            $stmt = $dbh->prepare("SELECT p.id,
            CASE
                WHEN COALESCE(ar_sum.budget, 0) = 0 THEN
                rot.budget
                ELSE
                ar_sum.budget
            END AS budget
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT min(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) otx ON otx.project_id = p.id
            JOIN tenders AS ori_t ON otx.id = ori_t.id
            JOIN tender_rot_information AS rot ON rot.tender_id = ori_t.id
            JOIN (SELECT max(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = ori_t.project_id
            JOIN tenders AS t ON tx.id = t.id
            JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
            LEFT JOIN open_tender_award_recommendation_tender_summary ar_sum ON ar_sum.tender_id = ar.tender_id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_POST_CONTRACT."
            AND p.id IN (".implode(',', $postContractProjectIds).")
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND t.open_tender_verification_status = ".Tender::SUBMISSION."
            AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
            AND ar.status = ".OpenTenderAwardRecommendationStatus::APPROVED."
            AND x.project_id IS NULL
            AND countries.id = ".$country->id."
            AND p.deleted_at IS NULL
            ORDER BY p.id");

            $stmt->execute();

            $records = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            //query post contract projects with unapproved AR. Use ROT budget amount
            $stmt = $dbh->prepare("SELECT p.id, COALESCE(rot.budget, 0) AS budget
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT min(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) otx ON otx.project_id = p.id
            JOIN tenders AS ori_t ON otx.id = ori_t.id
            JOIN tender_rot_information AS rot ON rot.tender_id = ori_t.id
            JOIN (SELECT max(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = ori_t.project_id
            JOIN tenders AS t ON tx.id = t.id
            JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
            LEFT JOIN open_tender_award_recommendation_tender_summary ar_sum ON ar_sum.tender_id = ar.tender_id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_POST_CONTRACT."
            AND p.id IN (".implode(',', $postContractProjectIds).")
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND t.open_tender_verification_status = ".Tender::SUBMISSION."
            AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
            AND ar.status <> ".OpenTenderAwardRecommendationStatus::APPROVED."
            AND x.project_id IS NULL
            AND countries.id = ".$country->id."
            AND p.deleted_at IS NULL
            ORDER BY p.id");

            $stmt->execute();

            $records += $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            //for projects without any AR. Mostly skipped to post contract projects
            $stmt = $dbh->prepare("SELECT p.id, COALESCE(rot.budget, 0) AS budget
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT min(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) otx ON otx.project_id = p.id
            JOIN tenders AS ori_t ON otx.id = ori_t.id
            JOIN tender_rot_information AS rot ON rot.tender_id = ori_t.id
            JOIN (SELECT max(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = ori_t.project_id
            JOIN tenders AS t ON tx.id = t.id
            LEFT JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_POST_CONTRACT."
            AND p.id IN (".implode(',', $postContractProjectIds).")
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
            AND x.project_id IS NULL
            AND ar.tender_id IS NULL
            AND rot.budget <> 0
            AND countries.id = ".$country->id."
            AND p.deleted_at IS NULL
            ORDER BY p.id");

            $stmt->execute();

            $records += $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            $projectIds = array_diff($projectIds, $postContractProjectIds);
        }
        
        if(!empty($projectIds))//closed tender projects
        {
            $stmt = $dbh->prepare("SELECT p.id,
            CASE
                WHEN COALESCE(ar_sum.budget, 0) = 0 THEN
                rot.budget
                ELSE
                ar_sum.budget
            END AS budget
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT min(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) otx ON otx.project_id = p.id
            JOIN tenders AS ori_t ON otx.id = ori_t.id
            JOIN tender_rot_information AS rot ON rot.tender_id = ori_t.id
            JOIN (SELECT max(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = ori_t.project_id
            JOIN tenders AS t ON tx.id = t.id
            JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
            LEFT JOIN open_tender_award_recommendation_tender_summary ar_sum ON ar_sum.tender_id = ar.tender_id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_CLOSED_TENDER."
            AND p.id IN (".implode(',', $projectIds).")
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND t.open_tender_verification_status = ".Tender::SUBMISSION."
            AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
            AND ar.status = ".OpenTenderAwardRecommendationStatus::APPROVED."
            AND ar.updated_at::timestamp::date >= '".$startDate."' AND ar.updated_at::timestamp::date <= '".$endDate."'
            AND x.project_id IS NULL
            AND countries.id = ".$country->id."
            AND p.deleted_at IS NULL
            ORDER BY p.id");
            
            $stmt->execute();

            $records += $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            //for projects without any AR. Mostly skipped to post contract projects
            $stmt = $dbh->prepare("SELECT p.id, COALESCE(rot.budget, 0) AS budget
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT min(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = p.id
            JOIN tenders AS t ON tx.id = t.id
            JOIN tender_rot_information AS rot ON rot.tender_id = t.id
            LEFT JOIN open_tender_award_recommendation ar ON ar.tender_id = rot.tender_id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_CLOSED_TENDER."
            AND p.id IN (".implode(',', $projectIds).")
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND rot.status = ".TenderRecommendationOfTendererInformation::SUBMISSION."
            AND rot.created_at::timestamp::date >= '".$startDate."' AND rot.created_at::timestamp::date <= '".$endDate."'
            AND x.project_id IS NULL
            AND ar.tender_id IS NULL
            AND rot.budget <> 0
            AND countries.id = ".$country->id."
            AND p.deleted_at IS NULL
            ORDER BY p.id");

            $stmt->execute();

            $records += $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        }

        return $records;
    }

    public function getMinYear(Country $country)
    {
        $query = \DB::table('projects')->join('countries', 'projects.country_id', '=', 'countries.id')
        ->leftJoin('dashboard_groups_excluded_projects AS x', function($join)
        {
            $join->on('x.project_id', '=', 'projects.id');
            $join->on('x.dashboard_group_type','=', \DB::raw($this->type));
        })
        ->select('projects.id')
        ->where('projects.status_id', StatusType::STATUS_TYPE_POST_CONTRACT)
        ->whereNull('x.project_id')
        ->whereNull('projects.deleted_at');

        $query->where('countries.id', $country->id);

        $eprojectIds = $query->distinct()->lists('projects.id');

        $years = [];
        
        if(!empty($eprojectIds))
        {
            $data = \DB::connection('buildspace')
                ->table('bs_project_structures AS p')
                ->join('bs_project_main_information AS i', 'i.project_structure_id', '=', 'p.id')
                ->join('bs_new_post_contract_form_information AS f', 'f.project_structure_id', '=', 'i.project_structure_id')
                ->whereIn('i.eproject_origin_id', $eprojectIds)
                ->whereNull('p.deleted_at')
                ->select('f.awarded_date')
                ->orderBy('f.awarded_date', 'ASC')
                ->groupBy('f.awarded_date')
                ->first('f.awarded_date');
            
            $years[] = ($data) ? date('Y', strtotime($data->awarded_date)) : date('Y');
        }
        
        $dbh=\DB::getPdo();

        $stmt = $dbh->prepare("SELECT ar.updated_at
            FROM projects AS p
            JOIN countries ON p.country_id = countries.id
            JOIN (SELECT max(id) AS id, project_id
            FROM tenders
            GROUP BY project_id) tx ON tx.project_id = p.id
            JOIN tenders AS t ON tx.id = t.id
            JOIN open_tender_award_recommendation ar ON ar.tender_id = t.id
            JOIN companies AS c ON t.currently_selected_tenderer_id = c.id
            JOIN open_tender_award_recommendation_bill_details b ON b.tender_id = t.id
            LEFT JOIN dashboard_groups_excluded_projects AS x ON x.project_id = p.id AND x.dashboard_group_type = ".$this->type."
            WHERE p.status_id = ".StatusType::STATUS_TYPE_CLOSED_TENDER."
            AND t.open_tender_status = ".Tender::OPEN_TENDER_STATUS_OPENED."
            AND t.open_tender_verification_status = ".Tender::SUBMISSION."
            AND t.currently_selected_tenderer_id IS NOT NULL
            AND ar.status = ".OpenTenderAwardRecommendationStatus::APPROVED."
            AND x.project_id IS NULL
            AND p.deleted_at IS NULL
            AND countries.id = ".$country->id."
            GROUP BY ar.updated_at
            ORDER BY ar.updated_at ASC
        ");

        $stmt->execute();

        $year = $stmt->fetch(\PDO::FETCH_COLUMN, 0);

        if($year)
        {
            $years[] = date('Y', strtotime($year));
        }

        return ($years) ? min($years) : date('Y'); 
    }
}