<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class WorkCategory extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_work_categories';

    public function vendorWorkCategories()
    {
        return $this->belongsToMany('PCK\VendorWorkCategory\VendorWorkCategory', 'vendor_work_category_work_category');
    }

    public static function getByName($name)
    {
        return self::where('name', 'ILIKE', $name)->first();
    }

    public static function initialise(\PCK\WorkCategories\WorkCategory $workCategory)
    {
        if( ! ( $bsResource = self::getByName($workCategory->name) ) )
        {
            $bsResource       = new self();
            $bsResource->name = $workCategory->name;

            $bsResource->save();
        }

        return $bsResource;
    }
}