<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use GuzzleHttp\Client;

use PCK\Buildspace\BillItem;
use PCK\Buildspace\ProjectMainInformation;

class Project extends Model {

    use SoftDeletingTrait;

    const TYPE_ROOT = 1;
    const TYPE_BILL = 4;

    protected $connection = 'buildspace';
    protected $table      = 'bs_project_structures';

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $project)
        {
            $project->where('id', '>=', 0)
                ->whereRaw('id = root_id')
                ->where('deleted_at', null)
                ->update([ 'priority' => \DB::raw('priority + 1') ]);
        });

        static::created(function(self $project)
        {
            $project->root_id = $project->id;
            $project->lft     = 1;
            $project->rgt     = 2;
            $project->level   = 0;

            $project->save();
        });
    }

    public function mainInformation()
    {
        return $this->hasOne('PCK\Buildspace\ProjectMainInformation', 'project_structure_id');
    }

    public function postContract()
    {
        return $this->hasOne('PCK\Buildspace\PostContract', 'project_structure_id');
    }

    public function newPostContractFormInformation()
    {
        return $this->hasOne('PCK\Buildspace\NewPostContractFormInformation', 'project_structure_id');
    }

    public function tenderSetting()
    {
        return $this->hasOne('PCK\Buildspace\TenderSetting', 'project_structure_id');
    }

    public function tenderAlternatives()
    {
        //only return non deleted records. Eproject only cares for non deleted data from buildspace
        return $this->hasMany('PCK\Buildspace\TenderAlternative', 'project_structure_id')
            ->whereNull('deleted_at')
            ->whereNull('project_revision_deleted_at')
            ->orderBy('id');
    }

    public function getAwardedTenderAlternative()
    {
        return $this->tenderAlternatives()->whereRaw('is_awarded IS TRUE')->first();
    }

    public function getCurrentClaimCertificate()
    {
        if( ! $postContract = $this->postContract ) return null;

        return $postContract->postContractClaimRevisions()->where('locked_status', '=', false)->first()->claimCertificate;
    }

    public function getApprovedClaimCertificates()
    {
        $claimCertificates = new Collection();

        if( ! $postContract = $this->postContract ) return $claimCertificates;

        foreach($postContract->postContractClaimRevisions()->with('claimCertificate')->where('locked_status', '=', true)->orderBy('version', 'desc')->get() as $claimRevision)
        {
            if( ! $claimRevision->claimCertificate ) continue;

            if( $claimRevision->claimCertificate->status == ClaimCertificate::STATUS_TYPE_APPROVED )
            {
                $claimCertificates->push($claimRevision->claimCertificate);
            }
        }

        return $claimCertificates;
    }

    public function getLatestApprovedClaimCertificate()
    {
        $claimCertificates = $this->getApprovedClaimCertificates();

        if( $claimCertificates->isEmpty() ) return null;

        return $claimCertificates->first();
    }

    public function letterOfAward()
    {
        return $this->hasOne('PCK\Buildspace\PostContractLetterOfAward', 'project_structure_id');
    }

    public function projectCodeSettings()
    {
        return $this->hasMany('PCK\Buildspace\ProjectCodeSetting', 'project_structure_id');
    }

    public function itemCodeSettings()
    {
        return $this->hasMany('PCK\Buildspace\ItemCodeSetting', 'project_structure_id');
    }

    public function debitCreditNoteClaims()
    {
        return $this->hasMany('PCK\Buildspace\DebitCreditNoteClaim', 'project_structure_id');
    }

    public static function getOverallTotalByProjects(Array $projectStructureIds, $withNotListedItems=false)
    {
        $overallTotalByProject = [];

        if(empty($projectStructureIds)) return $overallTotalByProject;

        $query = \DB::connection('buildspace')
            ->table('bs_bill_items AS i')
            ->join('bs_bill_elements AS e', 'e.id', '=', 'i.element_id')
            ->join('bs_project_structures AS b', 'e.project_structure_id', '=', 'b.id')
            ->join('bs_project_structures AS p', 'b.root_id', '=', 'p.id')
            ->whereIn('b.root_id', $projectStructureIds)
            ->where('b.type', self::TYPE_BILL)
            ->where('p.type', self::TYPE_ROOT)
            ->whereNotIn('i.type', [BillItem::TYPE_HEADER, BillItem::TYPE_NOID, BillItem::TYPE_HEADER_N]);
        
        if(!$withNotListedItems)
        {
            $query->where('i.type', '<>', BillItem::TYPE_ITEM_NOT_LISTED);
        }

        $records = $query->whereNull('i.project_revision_deleted_at')
                ->whereNull('i.deleted_at')
                ->whereNull('e.deleted_at')
                ->whereNull('b.deleted_at')
                ->whereNull('p.deleted_at')
                ->select('p.id', \DB::raw('COALESCE(SUM(i.grand_total_after_markup),0) AS total'))
                ->groupBy('p.id')
                ->get();
        
        foreach($records as $record)
        {
            $overallTotalByProject[$record->id] = (float)$record->total;
        }
        
        return $overallTotalByProject;
    }
}