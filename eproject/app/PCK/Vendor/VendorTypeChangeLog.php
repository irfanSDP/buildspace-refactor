<?php namespace PCK\Vendor;

use Illuminate\Database\Eloquent\Model;

class VendorTypeChangeLog extends Model {

    protected $table = 'vendor_type_change_logs';

    protected $fillable = [
        'vendor_id',
        'old_type',
        'new_type',
        'vendor_evaluation_cycle_score_id',
        'watch_list_entry_date',
        'watch_list_release_date',
        'created_by',
        'updated_by',
    ];
}