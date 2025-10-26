<?php namespace PCK\Tag;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {

    protected $table = 'tags';

    protected $fillable = ['category', 'name'];

    CONST CATEGORY_VENDOR_PROFILE = 1;
    CONST CATEGORY_VENDOR_MANAGEMENT_USERS = 2;
}