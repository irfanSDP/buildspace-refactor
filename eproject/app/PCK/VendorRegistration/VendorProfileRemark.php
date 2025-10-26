<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;

class VendorProfileRemark extends Model
{
    protected $table = 'vendor_profile_remarks';

    public function vendorProfile()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorProfile', 'vendor_profile_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }
}