<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Base\ModuleAttachmentTrait;

class PostContractClaimRevision extends Model {

    use SoftDeletingTrait, ModuleAttachmentTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_post_contract_claim_revisions';

    public function postContract()
    {
        return $this->belongsTo('PCK\Buildspace\PostContract');
    }

    public function claimCertificate()
    {
        return $this->hasOne('PCK\Buildspace\ClaimCertificate', 'post_contract_claim_revision_id');
    }

    public function subProjectLatestApprovedClaimRevisions()
    {
        return $this->hasMany('PCK\Buildspace\SubProjectLatestApprovedClaimRevision', 'main_project_claim_revision_id');
    }

    public function mainProjectLatestApprovedClaimRevision()
    {
        return $this->hasOne('PCK\Buildspace\SubProjectLatestApprovedClaimRevision', 'sub_project_claim_revision_id');
    }

    public function getImportedBQWorkDoneAmount()
    {
        $standardClaimWorkDone = \DB::connection($this->connection)
            ->table('bs_post_contract_imported_standard_claim')
            ->where('revision_id', '=', $this->id)
            ->sum('up_to_date_amount');

        $preliminaryClaimWorkDone = \DB::connection($this->connection)
            ->table('bs_post_contract_imported_preliminary_claim')
            ->where('revision_id', '=', $this->id)
            ->sum('up_to_date_amount');

        return $standardClaimWorkDone + $preliminaryClaimWorkDone;
    }

    public function getImportedVariationOrderWorkDoneAmount()
    {
        return \DB::connection($this->connection)
            ->table('bs_imported_variation_order_claim_items')
            ->where('revision_id', '=', $this->id)
            ->sum('up_to_date_amount');
    }

    public function getImportedMaterialOnSiteAmount()
    {
        return \DB::connection($this->connection)
            ->table('bs_imported_material_on_site_items as mosi')
            ->join('bs_imported_materials_on_site as mos', 'mos.id', '=', 'mosi.imported_material_on_site_id')
            ->where('mos.revision_id', '=', $this->id)
            ->sum('mosi.final_amount');
    }

    public function getClaimImportLog()
    {
        return \DB::connection($this->connection)
            ->table('bs_claim_import_logs')
            ->where('revision_id', '=', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUnlockClaimSubmissionLog()
    {
        return \DB::connection($this->connection)
            ->table('bs_unlock_claim_submission_logs')
            ->where('revision_id', '=', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}