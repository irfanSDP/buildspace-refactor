<?php namespace PCK\TendererTechnicalEvaluationInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use Illuminate\Support\Facades\DB;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class TenderTechnicalEvaluationInformationUser extends Model implements FormLevelStatus {
    
    use TimestampFormatterTrait;

    public static function getDaysPending($tender_id, $user_id) {
        $updatedDate = DB::table('tender_user_technical_evaluation_verifier')
                        ->select('updated_at')
                        ->where('tender_id', $tender_id)
                        ->where('user_id', $user_id)
                        ->where('status', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
                        ->orderBy('id', 'ASC')
                        ->first();
        $now = Carbon::now();
        $then = Carbon::parse($updatedDate->updated_at);
        return $then->diffInDays($now);
    }
}