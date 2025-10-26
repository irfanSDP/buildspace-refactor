<?php namespace PCK\EBiddings;

use Illuminate\Database\Eloquent\Model;

class EBiddingStat extends Model
{
    protected $table = 'e_bidding_stats';

    protected $fillable = [
        'e_bidding_id',
        'e_bidding_mode_id',
        'root_subsidiary_id',
        'subsidiary_id',
        'project_id',
        'duration',
        'duration_extended',
        'total_bids',
        'total_bidders',
        'lowest_tender_amount',
        'budget_amount',
        'leading_bid_amount',
        'tender_amount_diff',
        'budget_amount_diff',
        'currency_code',
        'started_at',
        'ended_at',
    ];

    public function eBidding()
    {
        return $this->belongsTo('PCK\EBiddings\EBidding');
    }

    public function eBiddingMode()
    {
        return $this->belongsTo('PCK\EBiddings\EBiddingMode');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function rootSubsidiary()
    {
        return $this->belongsTo('PCK\Companies\Subsidiary', 'root_subsidiary_id');
    }

    public function subsidiary()
    {
        return $this->belongsTo('PCK\Subsidiaries\Subsidiary');
    }
}