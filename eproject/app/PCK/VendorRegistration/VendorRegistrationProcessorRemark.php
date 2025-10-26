<?php namespace PCK\VendorRegistration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class VendorRegistrationProcessorRemark extends Model
{
    use SoftDeletingTrait;

    protected $table = 'vendor_registration_processor_remarks';

    protected $fillable = ['vendor_registration_processor_id', 'remarks'];
}