<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class SubRegion extends Model {

	protected $connection = 'buildspace';

	protected $table = 'bs_subregions';

	public $timestamps = false;

}