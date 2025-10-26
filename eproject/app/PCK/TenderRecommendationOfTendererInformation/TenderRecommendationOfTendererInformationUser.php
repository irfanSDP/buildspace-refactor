<?php namespace PCK\TenderRecommendationOfTendererInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use Illuminate\Support\Facades\DB;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class TenderRecommendationOfTendererInformationUser extends Model implements FormLevelStatus {
    
    use TimestampFormatterTrait;

    protected $table = 'tender_rot_information_user';

    public static function getDaysPending($recOfTenderer, $user) {
        $isCurrentUserFirstVerifier = ($recOfTenderer->currentBatchVerifiers->first()->id === $user->id);
        if($isCurrentUserFirstVerifier) {
            $then = Carbon::parse($recOfTenderer->updated_at);
        } else {
            $then = Carbon::parse($recOfTenderer->latestVerifierLog->first()->updated_at);
        }
        $now = Carbon::now();
        return $then->diffInDays($now); 
    }
}