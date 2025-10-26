<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Companies\Company;

class VendorProfile extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'vendor_profiles';

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function remarkList()
    {
        return $this->hasMany('PCK\VendorRegistration\VendorProfileRemark', 'vendor_profile_id');
    }

    public static function findRecord(Company $company)
    {
        return self::where('company_id', $company->id)->first();
    }

    public static function createIfNotExists(Company $company)
    {
        $record = self::findRecord($company);

        if(is_null($record))
        {
            $record = new self();
            $record->company_id = $company->id;
            $record->save();
        }

        return self::find($record->id);
    }
}