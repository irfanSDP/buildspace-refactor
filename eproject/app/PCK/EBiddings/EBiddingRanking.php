<?php namespace PCK\EBiddings;

use Illuminate\Database\Eloquent\Model;

class EBiddingRanking extends Model
{
    protected $table = 'e_bidding_rankings';

    protected $fillable = [
        'e_bidding_id',
        'company_id',
        'bid_amount',
    ];

    public function eBidding()
    {
        return $this->belongsTo('PCK\EBiddings\EBidding');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }
}