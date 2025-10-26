<?php namespace PCK\BusinessEntityType;

use Illuminate\Database\Eloquent\Model;

class BusinessEntityType extends Model {

    protected $table = 'business_entity_types';

    protected $fillable = ['name'];

    const OTHER = 'other';
}