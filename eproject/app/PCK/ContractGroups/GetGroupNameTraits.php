<?php namespace PCK\ContractGroups;

use PCK\ContractGroups\Types\Role;

trait GetGroupNameTraits {

    public static function getSystemDefaultGroupName($group)
    {
        switch($group)
        {
            case Role::INSTRUCTION_ISSUER:
                return trans('groupTypes.instructionIssuer');
            case Role::CONTRACTOR:
                return trans('groupTypes.contractor');
            case Role::CLAIM_VERIFIER:
                return trans('groupTypes.claimVerifier');
            case Role::CONSULTANT_1:
                return trans('groupTypes.consultant1');
            case Role::CONSULTANT_2:
                return trans('groupTypes.consultant2');
            case Role::CONSULTANT_3:
                return trans('groupTypes.consultant3');
            case Role::CONSULTANT_4:
                return trans('groupTypes.consultant4');
            case Role::CONSULTANT_5:
                return trans('groupTypes.consultant5');
            case Role::CONSULTANT_6:
                return trans('groupTypes.consultant6');
            case Role::CONSULTANT_7:
                return trans('groupTypes.consultant7');
            case Role::CONSULTANT_8:
                return trans('groupTypes.consultant8');
            case Role::CONSULTANT_9:
                return trans('groupTypes.consultant9');
            case Role::CONSULTANT_10:
                return trans('groupTypes.consultant10');
            case Role::CONSULTANT_11:
                return trans('groupTypes.consultant11');
            case Role::CONSULTANT_12:
                return trans('groupTypes.consultant12');
            case Role::CONSULTANT_13:
                return trans('groupTypes.consultant13');
            case Role::CONSULTANT_14:
                return trans('groupTypes.consultant14');
            case Role::CONSULTANT_15:
                return trans('groupTypes.consultant15');
            case Role::CONSULTANT_16:
                return trans('groupTypes.consultant16');
            case Role::CONSULTANT_17:
                return trans('groupTypes.consultant17');
            case Role::PROJECT_OWNER:
                return trans('groupTypes.projectOwner');
            case Role::GROUP_CONTRACT:
                return trans('groupTypes.groupContract');
            case Role::PROJECT_MANAGER:
                return trans('groupTypes.projectManager');
            default:
                throw new \InvalidArgumentException("Invalid Group Type: {$group}");
        }
    }

}