<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class UnlockClaimSubmissionLog extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_unlock_claim_submission_logs';

    public static function addEntry($revisionId)
    {
        $user   = \Confide::user();
        $bsUser = $user->getBsUser();

        $logEntry = new self;

        $logEntry->revision_id = $revisionId;
        $logEntry->created_by  = $bsUser->id;
        $logEntry->updated_by  = $bsUser->id;

        return $logEntry->save();
    }
}