<?php namespace PCK\VendorWorkSubcategory;

use Illuminate\Database\Eloquent\Model;

class VendorWorkSubcategory extends Model {

    protected $table = 'vendor_work_subcategories';

    protected $fillable = ['vendor_work_category_id', 'name', 'code'];

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory', 'vendor_work_category_id');
    }
}