<?php namespace PCK\TenderCallingTenderInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use Illuminate\Support\Facades\DB;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class TenderCallingTenderOfTendererInformationUser extends Model implements FormLevelStatus {
    
    use TimestampFormatterTrait;

    protected $table = 'tender_calling_tender_information_user';

    public static function getDaysPending($callingTender, $user) {
        $isCurrentUserFirstVerifier = ($callingTender->currentBatchVerifiers->first()->id === $user->id);
        if($isCurrentUserFirstVerifier) {
            $then = Carbon::parse($callingTender->updated_at);
        } else {
            $then = Carbon::parse($callingTender->latestVerifierLog->first()->updated_at);
        }
        $now = Carbon::now();
        return $then->diffInDays($now); 
    }
}