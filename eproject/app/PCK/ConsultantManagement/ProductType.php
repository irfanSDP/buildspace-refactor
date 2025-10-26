<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'product_types';

    protected $fillable = ['title'];

    public function developmentTypes()
    {
        return $this->belongsToMany('PCK\ConsultantManagement\DevelopmentType', 'development_types_product_types', 'product_type_id', 'development_type_id')->withTimestamps();
    }

    public function consultantManagementProductTypes()
    {
        return $this->hasMany('PCK\ConsultantManagement\ConsultantManagementProductType');
    }

    public function deletable()
    {
        return (!$this->consultantManagementProductTypes->count());
    }
}
