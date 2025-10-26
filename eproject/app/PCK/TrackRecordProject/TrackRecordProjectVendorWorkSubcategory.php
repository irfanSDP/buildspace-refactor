<?php namespace PCK\TrackRecordProject;

use Illuminate\Database\Eloquent\Model;

class TrackRecordProjectVendorWorkSubcategory extends Model
{
    protected $table = 'track_record_project_vendor_work_subcategories';

    public function trackRecordProject()
    {
        return $this->belongsTo('PCK\TrackRecordProject\TrackRecordProject', 'track_record_project_id');
    }

    public function vendorWorkSubcategory()
    {
        return $this->belongsTo('PCK\VendorWorkSubcategory\VendorWorkSubcategory', 'vendor_work_subcategory_id');
    }

    public static function syncVendorWorkSubcategories(TrackRecordProject $trackRecordProject, array $vendorWorkSubcategoryIds)
    {
        $existingVendorworkSubcategoryIds = self::where('track_record_project_id', $trackRecordProject->id)->lists('vendor_work_subcategory_id');

        $newlySelectedIdsToSave = array_diff($vendorWorkSubcategoryIds, $existingVendorworkSubcategoryIds);
        $deselectedIdsToRemove  = array_diff($existingVendorworkSubcategoryIds, $vendorWorkSubcategoryIds);

        foreach($newlySelectedIdsToSave as $id)
        {
            $record                             = new self();
            $record->track_record_project_id    = $trackRecordProject->id;
            $record->vendor_work_subcategory_id = $id;
            $record->save();
        }

        foreach($deselectedIdsToRemove as $id)
        {
            $record = self::where('track_record_project_id', $trackRecordProject->id)->where('vendor_work_subcategory_id', $id)->first();

            if($record)
            {
                $record->delete();
            }
        }
    }
}