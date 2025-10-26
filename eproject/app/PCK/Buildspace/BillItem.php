<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class BillItem extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_bill_items';

    const TYPE_HEADER = 1;
    const TYPE_WORK_ITEM = 2;
    const TYPE_NOID = 4;
    const TYPE_ITEM_HTML_EDITOR = 8;
    const TYPE_ITEM_PROVISIONAL = 16;
    const TYPE_ITEM_RATE_ONLY = 32;
    const TYPE_ITEM_NOT_LISTED = 64;
    const TYPE_ITEM_PC_RATE = 128;
    const TYPE_ITEM_LUMP_SUM = 256;
    const TYPE_ITEM_LUMP_SUM_PERCENT = 512;
    const TYPE_ITEM_LUMP_SUM_EXCLUDE = 1024;
    const TYPE_HEADER_N = 2048;

    const TYPE_HEADER_TEXT = 'HEAD';
    const TYPE_WORK_ITEM_TEXT = 'ITEM';
    const TYPE_NOID_TEXT = 'NOID';
    const TYPE_ITEM_HTML_EDITOR_TEXT = "ITEM-HE";
    const TYPE_ITEM_PROVISIONAL_TEXT = "ITEM-P";
    const TYPE_ITEM_RATE_ONLY_TEXT = 'ITEM-RO';
    const TYPE_ITEM_NOT_LISTED_TEXT = 'ITEM-NL';
    const TYPE_ITEM_PC_RATE_TEXT = 'ITEM-PC';
    const TYPE_ITEM_LUMP_SUM_TEXT = 'ITEM-LS';
    const TYPE_ITEM_LUMP_SUM_PERCENT_TEXT = 'ITEM-LS%';
    const TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT = 'ITEM-LSX';
    const TYPE_HEADER_N_TEXT = 'HEAD-N';
}

