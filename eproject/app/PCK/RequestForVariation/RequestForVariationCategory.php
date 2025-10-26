<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class RequestForVariationCategory extends Model
{
    protected $table = 'request_for_variation_categories';

    protected $fillable = array(
        'name'
    );

    public function actionLogs()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariation')
                    ->orderBy('id', 'ASC');
    }

    public function isKpiLimitEnabled()
    {
        return !is_null($this->kpi_limit);
    }


    public static function descriptionIsUnique($description)
    {
        return ( self::where('name', '=', $description)->count() == 0 );
    }

    public static function editAllowed($rfvCategoryId)
    {
        return (RequestForVariation::where('request_for_variation_category_id', $rfvCategoryId)->count() == 0);
    }
}


