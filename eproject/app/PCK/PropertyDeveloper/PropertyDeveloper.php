<?php namespace PCK\PropertyDeveloper;

use Illuminate\Database\Eloquent\Model;

class PropertyDeveloper extends Model {

    protected $table = 'property_developers';

    protected $fillable = ['name'];
}