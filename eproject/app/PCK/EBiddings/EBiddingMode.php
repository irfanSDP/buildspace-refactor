<?php namespace PCK\EBiddings;

use Illuminate\Database\Eloquent\Model;

class EBiddingMode extends Model
{
    protected $table = 'e_bidding_modes';

    protected $fillable = [
        'slug',
        'description',
    ];

    const BID_MODE_DECREMENT = 'decrement';
    const BID_MODE_INCREMENT = 'increment';
    const BID_MODE_ONCE = 'once';

    public static function getBidModes()
    {
        return [
            self::BID_MODE_DECREMENT,
            self::BID_MODE_INCREMENT,
            self::BID_MODE_ONCE,
        ];
    }

    public function getTypeLabel($type)
    {
        return trans('eBiddingMode.' . $type);
    }

    public static function getBidModeSelections()
    {
        $bidModes = self::getBidModes();

        $bidModeSelections = [];

        foreach ($bidModes as $bidMode) {
            $bidModeSelections[$bidMode] = self::getTypeLabel($bidMode);
        }
        return $bidModeSelections;
    }

}