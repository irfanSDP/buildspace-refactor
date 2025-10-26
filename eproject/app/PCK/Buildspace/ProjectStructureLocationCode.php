<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ProjectStructureLocationCode extends Model {
	
	use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_project_structure_location_codes';
    
}