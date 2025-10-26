<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class ProjectRevision extends Model {

	protected $connection = 'buildspace';

	protected $table = 'bs_project_revisions';

}