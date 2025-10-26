<?php namespace PCK\Countries;

use Illuminate\Database\Eloquent\Model;

class CurrencySetting extends Model
{
    const ROUNDING_TYPE_DISABLED = 1;
    const ROUNDING_TYPE_UPWARD = 2;
    const ROUNDING_TYPE_DOWNWARD = 4;
    const ROUNDING_TYPE_NEAREST_WHOLE_NUMBER = 8;
    const ROUNDING_TYPE_NEAREST_TENTH = 16;

    public function country()
    {
        return $this->belongsTo('PCK\Countries\Country');
    }

    public function getRoundedAmount($amount)
    {
        switch($this->rounding_type)
        {
            case self::ROUNDING_TYPE_UPWARD:
                $amount  = ceil($amount);
                break;
            case self::ROUNDING_TYPE_DOWNWARD:
                $amount  =  floor($amount);
                break;
            case self::ROUNDING_TYPE_NEAREST_WHOLE_NUMBER:
                $amount  =  round($amount);
                break;
            case self::ROUNDING_TYPE_NEAREST_TENTH:
                $amount  =  round($amount * 10) / 10;
                break;
            default:
                $amount  =  number_format($amount, 2, '.', '');
        }

        return $amount;
    }

    public static function getRoundingTypeText($roundingType = null)
    {
        $roundingTypeTextArray = [
            self::ROUNDING_TYPE_DISABLED             => trans('currencies.roundToTheExactAmount'),
            self::ROUNDING_TYPE_UPWARD               => trans('currencies.roundUp'),
            self::ROUNDING_TYPE_DOWNWARD             => trans('currencies.roundDown'),
            self::ROUNDING_TYPE_NEAREST_WHOLE_NUMBER => trans('currencies.roundToTheNearestWholeNumber'),
            self::ROUNDING_TYPE_NEAREST_TENTH        => trans('currencies.roundToTheNearestTenth'),
        ];

        return $roundingType ? $roundingTypeTextArray[$roundingType] : $roundingTypeTextArray;
    }
}

