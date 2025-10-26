<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Base\ModuleAttachmentTrait;

class InspectionItemResult extends Model{

	use ModuleAttachmentTrait;

    protected $fillable = [
        'inspection_result_id',
        'inspection_list_item_id',
        'remarks',
        'progress_status'
    ];
}