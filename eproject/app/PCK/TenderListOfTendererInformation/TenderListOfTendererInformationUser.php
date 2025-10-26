<?php namespace PCK\TenderListOfTendererInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use Illuminate\Support\Facades\DB;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class TenderListOfTendererInformationUser extends Model implements FormLevelStatus {
    
    use TimestampFormatterTrait;

    protected $table = 'tender_lot_information_user';

    public static function getDaysPending($listOfTenderer, $user) {
        $isCurrentUserFirstVerifier = ($listOfTenderer->currentBatchVerifiers->first()->id === $user->id);
        if($isCurrentUserFirstVerifier) {
            $then = Carbon::parse($listOfTenderer->updated_at);
        } else {
            $then = Carbon::parse($listOfTenderer->latestVerifierLog->first()->updated_at);
        }
        $now = Carbon::now();
        return $then->diffInDays($now); 
    }
}