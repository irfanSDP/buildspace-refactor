<?php namespace PCK\MyCompanyProfiles;

use Illuminate\Database\Eloquent\Model;

class MyCompanyProfile extends Model {
    protected $fillable = [
        'name'
    ];

    public static function getLogoPath()
    {
        $companyProfile = self::all()->first();

        return $companyProfile->company_logo_path;
    }
}