<?php namespace PCK\ProcurementMethod;

use Illuminate\Database\Eloquent\Model;

class ProcurementMethod extends Model {

    protected $table = 'procurement_methods';

    protected $fillable = [ 'name' ];
}