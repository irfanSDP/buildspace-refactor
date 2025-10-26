<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DevelopmentType extends Model
{
    protected $table = 'development_types';

    protected $fillable = ['title'];

    public function productTypes()
    {
        return $this->belongsToMany('PCK\ConsultantManagement\ProductType', 'development_types_product_types', 'development_type_id', 'product_type_id')->withTimestamps();
    }

    public function consultantManagementSubsidiary()
    {
        return $this->hasMany('PCK\ConsultantManagement\ConsultantManagementSubsidiary', 'development_type_id');
    }

    public function deletable()
    {
        return (!$this->consultantManagementSubsidiary->count());
    }
}
