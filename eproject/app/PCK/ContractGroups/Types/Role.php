<?php namespace PCK\ContractGroups\Types;

class Role {

    const INSTRUCTION_ISSUER = 1;
    const CONTRACTOR         = 2;
    const CLAIM_VERIFIER     = 4;
    const CONSULTANT_1       = 8;
    const CONSULTANT_2       = 16;
    const PROJECT_OWNER      = 64;
    const GROUP_CONTRACT     = 128;
    const PROJECT_MANAGER    = 256;
    const CONSULTANT_3       = 512;
    const CONSULTANT_4       = 1024;
    const CONSULTANT_5       = 2048;
    const CONSULTANT_6       = 4096;
    const CONSULTANT_7       = 8192;
    const CONSULTANT_8       = 16384;
    const CONSULTANT_9       = 32768;
    const CONSULTANT_10      = 65536;
    const CONSULTANT_11      = 131072;
    const CONSULTANT_12      = 262144;
    const CONSULTANT_13      = 524288;
    const CONSULTANT_14      = 1048576;
    const CONSULTANT_15      = 2097152;
    const CONSULTANT_16      = 4194304;
    const CONSULTANT_17      = 8388608;

    public static function getAllRoles()
    {
        return [
            self::INSTRUCTION_ISSUER,
            self::CLAIM_VERIFIER,
            self::CONSULTANT_1,
            self::CONSULTANT_2,
            self::CONSULTANT_3,
            self::CONSULTANT_4,
            self::CONSULTANT_5,
            self::CONSULTANT_6,
            self::CONSULTANT_7,
            self::CONSULTANT_8,
            self::CONSULTANT_9,
            self::CONSULTANT_10,
            self::CONSULTANT_11,
            self::CONSULTANT_12,
            self::CONSULTANT_13,
            self::CONSULTANT_14,
            self::CONSULTANT_15,
            self::CONSULTANT_16,
            self::CONSULTANT_17,
            self::PROJECT_OWNER,
            self::GROUP_CONTRACT,
            self::PROJECT_MANAGER,
            self::CONTRACTOR,
        ];
    }

    public static function getRolesExcept(...$excludedRoles)
    {
        $roles = self::getAllRoles();

        return array_diff($roles, $excludedRoles);
    }

}