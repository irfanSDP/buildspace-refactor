<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;
use PCK\Verifier\Verifier;

class RequestForVariationUserPermission extends Model {

    protected $table = 'request_for_variation_user_permissions';

    const ROLE_SUBMIT_RFV = 1;
    const ROLE_SUBMIT_RFV_TEXT = 'Submit Request For Variation';

    const ROLE_FILL_UP_OMISSION_ADDITION = 2;
    const ROLE_FILL_UP_OMISSION_ADDITION_TEXT = 'Fill up Omission / Addition';

    const ROLE_SUBMIT_FOR_APPROVAL = 3;
    const ROLE_SUBMIT_FOR_APPROVAL_TEXT = 'Submit for Approval';

    protected $fillable = [
        'request_for_variation_user_permission_group_id',
        'module_id',
        'user_id',
        'added_by'
    ];

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function group()
    {
        return $this->belongsTo('PCK\RequestForVariation\RequestForVariationUserPermissionGroup', 'request_for_variation_user_permission_group_id');
    }

    public static function getRoleNameByModuleId($moduleId = null)
    {
        $mapping = [
            self::ROLE_SUBMIT_RFV                => self::ROLE_SUBMIT_RFV_TEXT,
            self::ROLE_FILL_UP_OMISSION_ADDITION => self::ROLE_FILL_UP_OMISSION_ADDITION_TEXT,
            self::ROLE_SUBMIT_FOR_APPROVAL       => self::ROLE_SUBMIT_FOR_APPROVAL_TEXT,
        ];

        return is_null($moduleId) ? $mapping : $mapping[$moduleId];
    }

    public function canDelete()
    {
        if($this->module_id == self::ROLE_SUBMIT_FOR_APPROVAL)
        {
            $user = \Confide::user();

            foreach($this->group->requestForVariations()->orderBy('id', 'DESC')->get() as $rfv)
            {
                if(Verifier::isAVerifierInline($user, $rfv))
                {
                    return false;
                }
            }
        }

        return true;
    }

    public function getModuleName()
    {
        return trans('requestForVariation.requestForVariation') . ' ' . trans('requestForVariation.userPermissions');
    }
}


