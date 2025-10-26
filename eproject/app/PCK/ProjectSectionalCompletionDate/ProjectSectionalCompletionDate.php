<?php namespace PCK\ProjectSectionalCompletionDate;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Projects\Project;

class ProjectSectionalCompletionDate extends Model
{
    protected $table = 'project_sectional_completion_dates';

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}