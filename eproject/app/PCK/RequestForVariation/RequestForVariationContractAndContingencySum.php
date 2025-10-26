<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;

class RequestForVariationContractAndContingencySum extends Model
{
    protected $table = 'request_for_variation_contract_and_contingency_sum';

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }
}
