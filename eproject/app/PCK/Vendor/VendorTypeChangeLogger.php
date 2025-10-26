<?php namespace PCK\Vendor;

class VendorTypeChangeLogger {

    protected $vendor;
    protected $oldType;
    protected $watchListEntryDate;
    protected $watchListReleaseDate;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
        $this->captureInitialState();
    }

    public function captureInitialState()
    {
        $this->oldType              = $this->vendor->type;
        $this->watchListEntryDate   = $this->vendor->watch_list_entry_date;
        $this->watchListReleaseDate = $this->vendor->watch_list_release_date;
    }

    public function log()
    {
        $userId = \Auth::id();

        return VendorTypeChangeLog::create([
            'vendor_id'                        => $this->vendor->id,
            'old_type'                         => $this->oldType,
            'new_type'                         => $this->vendor->type,
            'vendor_evaluation_cycle_score_id' => $this->vendor->vendor_evaluation_cycle_score_id,
            'watch_list_entry_date'            => $this->watchListEntryDate,
            'watch_list_release_date'          => $this->watchListReleaseDate,
            'created_by'                       => $userId,
            'updated_by'                       => $userId,
        ]);
    }
}