<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

use PCK\Buildspace\Project;
use PCK\Projects\Project as EProject;
use PCK\Projects\StatusType;
use PCK\Countries\Country;

class PostContract extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_post_contracts';

    const RATE_TYPE_CONTRACTOR = 1;
    const RATE_TYPE_RATIONALIZED = 2;
    const RATE_TYPE_ORIGINAL = 4;

    const PUBLISHED_TYPE_NORMAL = 1;
    const PUBLISHED_TYPE_NEW = 2;
    
    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function postContractClaimRevisions()
    {
        return $this->hasMany('PCK\Buildspace\PostContractClaimRevision')->orderBy('version', 'desc');
    }

    public function currentProjectRevision()
    {
        return $this->hasOne('PCK\Buildspace\PostContractClaimRevision')->orderBy('version', 'desc');
    }

    public function getContractSum()
    {
        return \DB::connection('buildspace')
            ->table('bs_post_contract_bill_item_rates')
            ->where('post_contract_id', '=', $this->id)
            ->sum('grand_total');
    }

    public function getInProgressClaimRevision()
    {
        return $this->postContractClaimRevisions()->whereHas('claimCertificate', function($q)
        {
            $q->where('status', '=', \PCK\Buildspace\ClaimCertificate::STATUS_TYPE_IN_PROGRESS);
        })->first();
    }

    public static function getTotalContractSumByProjects(Array $projectIds)
    {
        $contractSumByProjects = [];

        if(!empty($projectIds))
        {
            $records = \DB::connection('buildspace')
            ->table('bs_post_contract_bill_item_rates AS r')
            ->join('bs_post_contracts AS pc', 'pc.id', '=', 'r.post_contract_id')
            ->join('bs_project_structures AS p', 'p.id', '=', 'pc.project_structure_id')
            ->whereIn('p.id', $projectIds)
            ->where('p.type', Project::TYPE_ROOT)
            ->whereNull('p.deleted_at')
            ->select('p.id', \DB::raw('COALESCE(SUM(r.grand_total),0) AS total'))
            ->groupBy('p.id')
            ->get();

            foreach($records as $record)
            {
                $contractSumByProjects[$record->id] = (float)$record->total;
            }
        }

        return $contractSumByProjects;
    }
}