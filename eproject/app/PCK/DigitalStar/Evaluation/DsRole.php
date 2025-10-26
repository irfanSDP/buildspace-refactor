<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;

class DsRole extends Model {

    protected $table = 'ds_roles';

    protected $fillable = [
        'slug',
        'description',
    ];

    public static function getById($id)
    {
        return self::find($id);
    }

    public static function getBySlug($slug)
    {
        return self::where('slug', '=', $slug)->first();
    }

}