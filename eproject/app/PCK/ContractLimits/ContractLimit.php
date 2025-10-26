<?php namespace PCK\ContractLimits;

use Illuminate\Database\Eloquent\Model;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;

class ContractLimit extends Model {

    protected $fillable = [
        'limit',
    ];

    public function canBeDeleted()
    {
        return TechnicalEvaluationSetReference::where('contract_limit_id', '=', $this->id)->count() == 0;
    }
}