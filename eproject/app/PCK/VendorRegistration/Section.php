<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;
use PCK\Statuses\FormStatus;

class Section extends Model implements FormStatus
{
    protected $table = 'vendor_registration_sections';

    protected $fillable = ['vendor_registration_id', 'section', 'status_id'];

    const SECTION_COMPANY_DETAILS = 1;
    const SECTION_COMPANY_PERSONNEL = 2;
    const SECTION_PROJECT_TRACK_RECORD = 3;
    const SECTION_SUPPLIER_CREDIT_FACILITIES = 4;
    const SECTION_PAYMENT = 5;

    const AMENDMENT_STATUS_NOT_REQUIRED = 1;
    const AMENDMENT_STATUS_REQUIRED = 2;
    const AMENDMENT_STATUS_MADE = 3;

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            if( is_null($model->status_id) ) $model->status_id = self::STATUS_DRAFT;
            if( is_null($model->amendment_status) ) $model->amendment_status = self::AMENDMENT_STATUS_NOT_REQUIRED;
        });
    }

    public function amendmentsNotRequired()
    {
        return $this->amendment_status == self::AMENDMENT_STATUS_NOT_REQUIRED;
    }

    public function amendmentsRequired()
    {
        return $this->amendment_status == self::AMENDMENT_STATUS_REQUIRED;
    }

    public function amendmentsMade()
    {
        return $this->amendment_status == self::AMENDMENT_STATUS_MADE;
    }

    public function isRejected()
    {
        return $this->status_id == self::STATUS_REJECTED;
    }

    public static function getSections()
    {
        return [
            self::SECTION_COMPANY_DETAILS,
            self::SECTION_COMPANY_PERSONNEL,
            self::SECTION_PROJECT_TRACK_RECORD,
            self::SECTION_SUPPLIER_CREDIT_FACILITIES,
            self::SECTION_PAYMENT,
        ];
    }

    public static function initiate(VendorRegistration $vendorRegistration)
    {
        foreach(self::getSections() as $section)
        {
            self::firstOrCreate(array(
                'vendor_registration_id' => $vendorRegistration->id,
                'section'                => $section,
            ));
        }
    }
}