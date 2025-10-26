<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class SubProjectLatestApprovedClaimRevision extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_sub_project_latest_approved_claim_revisions';

    public static function deleteRecords(PostContractClaimRevision $claimRevision)
    {
        self::where('main_project_claim_revision_id', '=', $claimRevision->id)->delete();
    }
}