<?php namespace PCK\TenderReminder;

use Confide;
use Illuminate\Database\Eloquent\Model;
use PCK\Tenders\Tender;

class TenderReminder extends Model {

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public static function saveDraft($tenderId, $message)
    {
        $tenderStage = Tender::find($tenderId)->getTenderStage();

        if( ! $tenderReminder = self::where('tender_id', '=', $tenderId)->where('tender_stage', '=', $tenderStage)->first() )
        {
            $tenderReminder               = new self;
            $tenderReminder->tender_id    = $tenderId;
            $tenderReminder->tender_stage = $tenderStage;
        }

        $tenderReminder->updated_by = Confide::user()->id;
        $tenderReminder->message    = $message;

        return $tenderReminder->save();
    }

}