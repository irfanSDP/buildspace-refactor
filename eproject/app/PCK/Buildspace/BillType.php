<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class BillType extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_bill_types';

    const TYPE_STANDARD = 1;
    const TYPE_PROVISIONAL = 2;
    const TYPE_PRELIMINARY = 4;
    const TYPE_PRIMECOST = 8;

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 2;
    const STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM = 4;
    const STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT = 8;
    const STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL = 16;
    const STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM = 32;
    const STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT = 64;
    const STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL = 128;
    const STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM = 256;
    const STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ELEMENT = 512;
    const STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL = 1024;
}

