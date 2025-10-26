<?php namespace PCK\ContractGroups;

use Illuminate\Database\Eloquent\Model;

class ContractGroup extends Model {

    use GetGroupNameTraits;

    protected $fillable = [ 'name' ];

    public function contractGroupProjectUsers()
    {
        return $this->hasMany('PCK\ContractGroupProjectUsers\ContractGroupProjectUser');
    }

    public function conversations()
    {
        return $this->belongsToMany('PCK\Conversations\Conversation')->withTimestamps();
    }

    public function documentManagementFolders()
    {
        return $this->belongsToMany('PCK\DocumentManagementFolders\DocumentManagementFolder')->withTimestamps();
    }

    public function projectContractGroupTenderDocumentPermission()
    {
        return $this->hasMany('PCK\ProjectContractGroupTenderDocumentPermissions\ProjectContractGroupTenderDocumentPermission');
    }

    public function contractGroupCategories()
    {
        return $this->belongsToMany('PCK\ContractGroupCategory\ContractGroupCategory')->withTimestamps();
    }

    public static function getIdByGroup($group)
    {
        return self::findByGroup($group)->id;
    }

    public static function findByGroup($group)
    {
        return self::where('group', '=', $group)->first();
    }

    public static function findByGroups(array $groups = [])
    {
        return self::whereIn('group', $groups)->orderBy('group', 'ASC')->get();
    }
}