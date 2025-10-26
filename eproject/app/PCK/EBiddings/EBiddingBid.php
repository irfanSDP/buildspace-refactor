<?php namespace PCK\EBiddings;

use Illuminate\Database\Eloquent\Model;

class EBiddingBid extends Model
{
    protected $table = 'e_bidding_bids';

    protected $fillable = [
        'e_bidding_id',
        'company_id',
        'type',
        'direction',
        'decrement_percent',
        'decrement_value',
        'decrement_amount',
        'bid_amount',
        'bid_type',
        'duration_extended', // minutes
        'extended_seconds',  // seconds
    ];

    const BID_TYPE_PERCENTAGE = 'PERCENTAGE';
    const BID_TYPE_AMOUNT = 'AMOUNT';
    const BID_TYPE_CUSTOM = 'CUSTOM';

    const BID_DIRECTION_INCREASE = 'INCREASE';
    const BID_DIRECTION_DECREASE = 'DECREASE';
    const BID_DIRECTION_NONE = 'NONE';

    const BID_COOLDOWN = 5; // 5 seconds

    public function eBidding()
    {
        return $this->belongsTo('PCK\EBiddings\EBidding');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function getTypeLabel($type)
    {
        switch($type) {
            case self::BID_TYPE_PERCENTAGE:
                $label = trans('eBiddingConsole.percentage');
                break;

            case self::BID_TYPE_AMOUNT:
                $label = trans('eBiddingConsole.amount');
                break;

                case self::BID_TYPE_CUSTOM:
                    $label = trans('eBiddingConsole.customAmount');
                    break;

            default:
                throw new \Exception('Invalid type');
        }
        return $label;
    }
}