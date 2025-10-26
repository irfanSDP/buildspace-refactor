<?php namespace PCK\EBiddings;

use Illuminate\Database\Eloquent\Model;

class EBiddingZone extends Model
{
    protected $table = 'e_bidding_zones';

    public function eBidding()
    {
        return $this->belongsTo('PCK\EBiddings\EBidding');
    }

    public function getLowerLimitAttribute()
    {
        $record = self::where('e_bidding_id', $this->e_bidding_id)->where('upper_limit', '<', $this->upper_limit)->orderBy('upper_limit', 'desc')->first();

        return is_null($record) ? 0 : ($record->upper_limit + 1);
    }
}
