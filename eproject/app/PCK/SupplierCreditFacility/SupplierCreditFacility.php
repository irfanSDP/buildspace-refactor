<?php namespace PCK\SupplierCreditFacility;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Companies\Company;

class SupplierCreditFacility extends Model {

    use ModuleAttachmentTrait;

    protected $table = 'supplier_credit_facilities';

    protected $fillable = ['supplier_name', 'credit_facilities', 'vendor_registration_id'];

    public function vendorRegistration()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorRegistration');
    }

    public static function getSupplierCreditFacilities(Company $company)
    {
        $facilities = [];

        foreach($company->vendorRegistration->supplierCreditFacilities()->orderBy('id', 'ASC')->get() as $facility)
        {
            array_push($facilities, [
                'label'             => $facility->supplier_name,
                'values'            => [],
                'route_attachments' => route('vendors.vendorRegistration.supplierCreditFacilities.attachments.get', [$facility->id]),
                'attachments_count' => $facility->attachments->count(),
            ]);
        }

        return $facilities;
    }
}