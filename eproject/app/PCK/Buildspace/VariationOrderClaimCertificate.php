<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class VariationOrderClaimCertificate extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_variation_order_claim_certificates';

    protected $primaryKey = 'variation_order_id';

    protected $fillable = [ 'variation_order_id' ];

}