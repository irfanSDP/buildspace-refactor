<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Buildspace\AccountGroup;

class ItemCodeSettingObject extends Model
{
    protected $connection = 'buildspace';
    protected $table      = 'bs_item_code_setting_objects';
}

