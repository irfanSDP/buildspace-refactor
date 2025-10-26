<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;

class VendorCategoryTemporaryRecord extends Model
{
    protected $table = 'vendor_category_temporary_records';

    public function vendorRegistration()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorRegistration');
    }

    public static function getTemporaryVendorCategoryIds(VendorRegistration $vendorRegistration)
    {
        return self::where('vendor_registration_id', $vendorRegistration->id)->lists('vendor_category_id');
    }

    public static function init(VendorRegistration $vendorRegistration)
    {
        foreach($vendorRegistration->company->vendorCategories as $vendorCategory)
        {
            $record                         = new self();
            $record->vendor_registration_id = $vendorRegistration->id;
            $record->vendor_category_id     = $vendorCategory->id;
            $record->save();
        }
    }

    public static function syncValues(VendorRegistration $vendorRegistration, array $vendorCategoryIds)
    {
        $existingValues = self::getTemporaryVendorCategoryIds($vendorRegistration);

        $removedVendorCategoryIds = array_diff($existingValues, $vendorCategoryIds);

        foreach($removedVendorCategoryIds as $id)
        {
            $record = self::where('vendor_registration_id', $vendorRegistration->id)->where('vendor_category_id', $id)->first();

            if($record)
            {
                $record->delete();
            }
        }

        $addedVendorCategoryIds = array_diff($vendorCategoryIds, $existingValues);

        foreach($addedVendorCategoryIds as $id)
        {
            $record = self::where('vendor_registration_id', $vendorRegistration->id)->where('vendor_category_id', $id)->first();

            if($record) continue;

            $record                         = new self();
            $record->vendor_registration_id = $vendorRegistration->id;
            $record->vendor_category_id     = $id;
            $record->save();
        }
    }

    public static function applyChanges(VendorRegistration $vendorRegistration)
    {
        $values = self::getTemporaryVendorCategoryIds($vendorRegistration);

        if(count($values) > 0)
        {
            $vendorRegistration->company->vendorCategories()->sync($values);
        }
    }

    public static function flushRecords(VendorRegistration $vendorRegistration)
    {
        foreach($vendorRegistration->vendorCategoryTemporaryRecords as $record)
        {
            $record->delete();
        }

        $vendorRegistration->load('vendorCategoryTemporaryRecords');
    }
}