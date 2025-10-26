<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ProjectSchedule extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_project_schedules';

    const TYPE_MAIN_PROJECT = 1;
    const TYPE_SUB_PACKAGE = 2;

    const PRINT_TYPE_PLAN = 2;
    const PRINT_TYPE_ACTUAL = 4;

    const DEFAULT_GANTT_NUMBER_OF_DAYS = 100;

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }
}

