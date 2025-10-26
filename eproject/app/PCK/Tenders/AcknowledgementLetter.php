<?php namespace PCK\Tenders;

use Illuminate\Database\Eloquent\Model;

class AcknowledgementLetter extends Model {

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender', 'tender_id');
    }

    public static function saveDraft($tenderId, $inputs)
    {
        if( ! $tenderAcknowledgementLetter = self::where('tender_id', '=', $tenderId)->first() )
        {
            $tenderAcknowledgementLetter            = new self;
            $tenderAcknowledgementLetter->tender_id = $tenderId;
        }

        $tenderAcknowledgementLetter->letter_content = $inputs['message'];

        if( $inputs['enable'] == 'true' )
        {
            $tenderAcknowledgementLetter->enable_letter = true;
        }
        else
        {
            $tenderAcknowledgementLetter->enable_letter = false;
        }

        return $tenderAcknowledgementLetter->save();
    }
}