<?php namespace PCK\TrackRecordProject;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Companies\Company;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorRegistration\VendorRegistration;

class TrackRecordProject extends Model {

    use ModuleAttachmentTrait;

    const TYPE_CURRENT = 1;
    const TYPE_COMPLETED = 2;

    protected $table = 'track_record_projects';

    protected $fillable = [
        'title',
        'property_developer_id',
        'property_developer_text',
        'vendor_category_id',
        'vendor_work_category_id',
        'vendor_registration_id',
        'type',
        'project_amount',
        'project_amount_remarks',
        'country_id',
        'year_of_site_possession',
        'year_of_completion',
        'has_qlassic_or_conquas_score',
        'qlassic_score',
        'qlassic_year_of_achievement',
        'conquas_score',
        'conquas_year_of_achievement',
        'awards_received',
        'year_of_recognition_awards',
        'remarks',
        'shassic_score'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (self $model)
        {
            $count = self::where('vendor_registration_id', $model->vendor_registration_id)->where('vendor_work_category_id', $model->vendor_work_category_id)->count();

            // the last of its kind
            if($count == 0)
            {
                $vendorPrequalification = VendorPreQualification::where('vendor_registration_id', $model->vendor_registration_id)->where('vendor_work_category_id', $model->vendor_work_category_id)->first();

                if($vendorPrequalification)
                {
                    $vendorPrequalification->delete();
                }
            }
        });
    }

    public function vendorCategory()
    {
        return $this->belongsTo('PCK\VendorCategory\VendorCategory');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory');
    }

    public function country()
    {
        return $this->belongsTo('PCK\Countries\Country');
    }

    public function trackRecordProjectVendorWorkSubcategories()
    {
        return $this->hasMany('PCK\TrackRecordProject\TrackRecordProjectVendorWorkSubcategory', 'track_record_project_id');
    }

    public function propertyDeveloper()
    {
        return $this->belongsTo('PCK\PropertyDeveloper\PropertyDeveloper');
    }

    public function vendorRegistration()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorRegistration');
    }

    public static function getVendorRegistrationProjectTrackRecords(Company $company)
    {
        $trackRecords = [];

        foreach($company->vendorRegistration->trackRecordProjects as $trackRecord)
        {
            array_push($trackRecords, [
                'label'             => $trackRecord->title,
                'values'            => [$trackRecord->propertyDeveloper ? $trackRecord->propertyDeveloper->name : $trackRecord->property_developer_text ],
                'route_attachments' => route('vendors.vendorRegistration.projectTrackRecord.attachments.get', [$trackRecord->id]),
                'attachments_count' => $trackRecord->attachments->count(),
            ]);
        }

        return $trackRecords;
    }

    public static function flushRecords(VendorRegistration $vendorRegistration)
    {
        foreach($vendorRegistration->trackRecordProjects as $record)
        {
            $record->delete();
        }

        $vendorRegistration->load('trackRecordProjects');
    }
}