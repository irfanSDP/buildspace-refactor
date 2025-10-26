<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;

class LetterOfAwardUserPermission extends Model {
    
    protected $table = 'letter_of_award_user_permissions';
    
    protected $fillable = [
        'project_id',
        'user_id',
        'module_identifier',
        'added_by'
    ];

    const EDITOR = 1;
    const REVIEWER = 2;

    public static function getRoleNameByModuleId($moduleId = null)
    {
        $mapping = [
            self::EDITOR     => trans('letterOfAward.editor'),
            self::REVIEWER   => trans('letterOfAward.reviewer'),
        ];

        return is_null($moduleId) ? $mapping : $mapping[$moduleId];
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function getModuleName()
    {
        return trans('letterOfAward.letterOfAward') . ' ' . trans('letterOfAward.userPermissions');
    }
}

