<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PostContractBillItemRate extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_post_contract_bill_item_rates';

    const PRELIM_RATE_COLUMN_NAME                   = 'rate';

    const PRELIM_INITIAL_CLAIM_COLUMN_NAME          = 'initial';
    const PRELIM_RECURRING_CLAIM_COLUMN_NAME        = 'recurring';
    const PRELIM_TIMEBASED_CLAIM_COLUMN_NAME        = 'timeBased';
    const PRELIM_WORKBASED_CLAIM_COLUMN_NAME        = 'workbased';
    const PRELIM_FINAL_CLAIM_COLUMN_NAME            = 'final';

    const PRELIM_CLAIM_PERCENTAGE_FIELD_EXT_NAME    = 'percentage';
    const PRELIM_CLAIM_AMOUNT_FIELD_EXT_NAME        = 'amount';

    const REMEASUREMENT_FILTER_BY_ALL_ITEMS         = 'allItems';
    const REMEASUREMENT_FILTER_BY_PROVISIONAL_ITEMS = 'provisionalItems';
}

