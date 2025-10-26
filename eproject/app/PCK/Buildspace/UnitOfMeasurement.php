<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class UnitOfMeasurement extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_unit_of_measurements';

    const UNIT_TYPE_METRIC   = 1;
    const UNIT_TYPE_IMPERIAL = 2;

    protected $fillable = [
        'name',
        'symbol',
        'display',
        'type',
        'created_by',
        'updated_by',
    ];

    public static function getOrCreate($symbol)
    {
        $unit = self::where('symbol', '=', $symbol)->first();

        if( is_null($unit) )
        {
            $user = \Confide::user();

            $bsUser = $user->getBsUser();

            $unit = self::create(array(
                'name'       => $symbol,
                'symbol'     => $symbol,
                'display'    => true,
                'type'       => self::UNIT_TYPE_METRIC,
                'created_by' => $bsUser->id,
                'updated_by' => $bsUser->id,
            ));
        }

        return $unit;
    }
}
