<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\RequestForVariation\RequestForVariationUserPermission;

class RequestForVariationUserPermissionGroup extends Model {

    protected $table = 'request_for_variation_user_permission_groups';

    protected $fillable = [ 'project_id' ];

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }

    public function userPermissions()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariationUserPermission', 'request_for_variation_user_permission_group_id')->orderBy('id', 'asc');
    }

    public function requestForVariations()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariation', 'request_for_variation_user_permission_group_id')->orderBy('id', 'asc');
    }

    public function canDelete()
    {
        return !$this->requestForVariations->count();
    }
}