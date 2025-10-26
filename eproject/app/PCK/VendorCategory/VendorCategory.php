<?php namespace PCK\VendorCategory;

use Illuminate\Database\Eloquent\Model;

class VendorCategory extends Model {

    protected $table = 'vendor_categories';

    protected $fillable = ['contract_group_category_id', 'name', 'code', 'target'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $object)
        {
            if( is_null($object->name) ) $object->name = $object->code;
            if( is_null($object->code) ) $object->code = $object->name;
        });
    }

    public function contractGroupCategory()
    {
        return $this->belongsTo('PCK\ContractGroupCategory\ContractGroupCategory', 'contract_group_category_id');
    }

    public function vendorWorkCategories()
    {
        return $this->belongsToMany('PCK\VendorWorkCategory\VendorWorkCategory', 'vendor_category_vendor_work_category', 'vendor_category_id', 'vendor_work_category_id')->withTimestamps();
    }
}