<?php namespace PCK\VendorWorkCategory;

use Illuminate\Database\Eloquent\Model;

class VendorWorkCategory extends Model {

    protected $table = 'vendor_work_categories';

    protected $fillable = ['vendor_category_id', 'name', 'code'];

    public function workCategories()
    {
        return $this->belongsToMany('PCK\WorkCategories\WorkCategory', 'vendor_work_category_work_category');
    }

    public function vendorCategories()
    {
        return $this->belongsToMany('PCK\VendorCategory\VendorCategory', 'vendor_category_vendor_work_category', 'vendor_work_category_id', 'vendor_category_id')->withTimestamps();
    }
}