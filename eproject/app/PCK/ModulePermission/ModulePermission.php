<?php namespace PCK\ModulePermission;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ModulePermission extends Model {

    protected $table = 'module_permissions';

    protected $fillable = [
        'user_id',
        'module_identifier',
    ];

    const MODULE_ID_TENDER_DOCUMENTS_TEMPLATE     = 1;
    const MODULE_ID_FORM_OF_TENDER_TEMPLATE       = 2;
    const MODULE_ID_TECHNICAL_EVALUATION_TEMPLATE = 3;
    const MODULE_ID_CONTRACTOR_LISTING            = 4;
    const MODULE_ID_DEFECTS                       = 5;
    const MODULE_ID_WEATHERS                      = 6;
    const MODULE_ID_FINANCE                       = 7;
    const MODULE_ID_PROJECTS_OVERVIEW             = 8;
    const MODULE_ID_MASTER_COST_DATA              = 9;
    const MODULE_ID_COST_DATA                     = 10;
    const MODULE_ID_LETTER_OF_AWARD               = 11;
    const MODULE_ID_RFV_CATEGORY                  = 12;
    const MODULE_ID_INSPECTION_TEMPLATE           = 13;
    const MODULE_ID_INSPECTION                    = 14;
    const MODULE_ID_TOP_MANAGEMENT_VERIFIERS      = 15;
    const MODULE_ID_CONSULTANT_PAYMENT            = 16;
    const MODULE_ID_PROJECT_REPORT_TEMPLATE       = 17;
    const MODULE_ID_PROJECT_REPORT_DASHBOARD      = 18;
    const MODULE_ID_REJECTED_MATERIAL             = 19;
    const MODULE_ID_LABOUR                        = 20;
    const MODULE_ID_MACHINERY                     = 21;
    const MODULE_ID_SITE_DIARY_MAINTENANCE        = 22;
    const MODULE_ID_PROJECT_REPORT_CHART_TEMPLATE = 23;
    const MODULE_ID_PROJECT_REPORT_CHARTS         = 24;
    const MODULE_ID_PAYMENT_GATEWAY               = 25;
    const MODULE_ID_ORDERS                        = 26;

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function subsidiaries()
    {
        return $this->belongsToMany('PCK\Subsidiaries\Subsidiary', 'module_permission_subsidiaries', 'module_permission_id', 'subsidiary_id')->withTimestamps();
    }

    // Include module IDs that have subsidiary list
    protected static function hasSubsidiaryList()
    {
        return array(
            self::MODULE_ID_FINANCE,
            self::MODULE_ID_PROJECT_REPORT_CHARTS,
            self::MODULE_ID_PROJECT_REPORT_DASHBOARD,
        );
    }

    public function getSubsidiaryIds()
    {
        return $this->subsidiaries()->select('subsidiaries.id')->lists('id');
    }

    protected static function getRecord(User $user, $moduleId)
    {
        return static::where('user_id', '=', $user->id)
            ->where('module_identifier', '=', $moduleId)
            ->first();
    }

    public static function getUserList($moduleId)
    {
        $users = new Collection();

        foreach(static::where('module_identifier', '=', $moduleId)->get() as $record)
        {
            $users->add($record->user);
        }

        return $users;
    }

    public static function getEditorList($moduleId)
    {
        $users = new Collection();

        foreach(static::where('module_identifier', '=', $moduleId)->where('is_editor', '=', true)->get() as $record)
        {
            $users->add($record->user);
        }

        return $users;
    }

    public static function isEditor(User $user, $moduleId)
    {
        if( $user->isSuperAdmin() ) return true;

        return self::getRecord($user, $moduleId)->is_editor ?? false;
    }

    public static function hasPermission(User $user, $moduleId)
    {
        if( $user->isSuperAdmin() ) return true;

        return static::getRecord($user, $moduleId) ? true : false;
    }

    public static function hasAnyPermission(User $user, array $moduleIds)
    {
        foreach ($moduleIds as $moduleId) {
            if (self::hasPermission($user, $moduleId)) {
                return true;
            }
        }

        return false;
    }

    public static function grant(User $user, $moduleId)
    {
        if( static::hasPermission($user, $moduleId) )
        {
            // Update record timestamp.
            static::getRecord($user, $moduleId)->touch();

            return true;
        }

        $record = new static(array( 'user_id' => $user->id, 'module_identifier' => $moduleId ));

        return $record->save();
    }

    public static function revoke(User $user, $moduleId)
    {
        if( ! static::hasPermission($user, $moduleId) ) return true;

        $record = static::getRecord($user, $moduleId);

        return $record->delete();
    }

    public static function setAsEditor(User $user, $moduleId, $setAsEditor = true)
    {
        if( ! $record = self::getRecord($user, $moduleId) ) return false;

        $record->is_editor = $setAsEditor;

        return $record->save();
    }

}