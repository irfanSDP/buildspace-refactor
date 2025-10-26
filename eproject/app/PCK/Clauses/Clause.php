<?php namespace PCK\Clauses;

use Illuminate\Database\Eloquent\Model;

class Clause extends Model {

    const TYPE_MAIN                       = 1;
    const TYPE_MAIN_TEXT                  = 'main';
    const TYPE_LOSS_AND_EXPENSES          = 2;
    const TYPE_LOSS_AND_EXPENSES_TEXT     = 'lossAndExpenses';
    const TYPE_ADDITIONAL_EXPENSES        = 3;
    const TYPE_ADDITIONAL_EXPENSES_TEXT   = 'additionalExpenses';
    const TYPE_EXTENSION_OF_TIME          = 4;
    const TYPE_EXTENSION_OF_TIME_TEXT     = 'extensionOfTime';
    const TYPE_EARLY_WARNING              = 5;
    const TYPE_EARLY_WARNING_TEXT         = 'earlyWarning';
    const TYPE_ARCHITECT_INSTRUCTION      = 6;
    const TYPE_ARCHITECT_INSTRUCTION_TEXT = 'architectInstruction';

    protected $fillable = [ 'contract_id', 'type', 'name' ];

    public function contract()
    {
        return $this->belongsTo('PCK\Contracts\Contract');
    }

    public function items()
    {
        return $this->hasMany('PCK\ClauseItems\ClauseItem')->orderBy('priority', 'asc');
    }

}