<?php namespace PCK\EBiddingCommittees;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Projects\Project;

class EBiddingCommittee extends Model {

    protected $table = 'e_bidding_committees';

    public function EBidding()
    {
        return $this->belongsTo('PCK\EBiddings\EBidding');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User','user_id');
    }

}