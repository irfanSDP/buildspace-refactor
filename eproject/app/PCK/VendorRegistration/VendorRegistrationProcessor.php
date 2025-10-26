<?php namespace PCK\VendorRegistration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class VendorRegistrationProcessor extends Model
{
    use SoftDeletingTrait;

    protected $table = 'vendor_registration_processors';

    protected $fillable = ['vendor_registration_id', 'user_id'];

    public function remark()
    {
        return $this->hasOne('PCK\VendorRegistration\VendorRegistrationProcessorRemark');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function updateRemarks($remarks)
    {
        VendorRegistrationProcessorRemark::where('vendor_registration_processor_id', '=', $this->id)->delete();

        VendorRegistrationProcessorRemark::create([
            'vendor_registration_processor_id' => $this->id,
            'remarks'                          => $remarks,
        ]);
    }
}