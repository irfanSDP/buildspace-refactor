<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ConsultantManagementProductType extends Model
{
    protected $table = 'consultant_management_product_types';

    protected $fillable = ['consultant_management_subsidiary_id', 'product_type_id', 'number_of_unit', 'lot_dimension_length', 'lot_dimension_width', 'proposed_built_up_area', 'proposed_average_selling_price'];

    public function consultantManagementSubsidiary()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementSubsidiary', 'consultant_management_subsidiary_id');
    }

    public function productType()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ProductType', 'product_type_id');
    }
}
