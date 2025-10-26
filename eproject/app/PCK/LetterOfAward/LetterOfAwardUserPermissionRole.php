<?php namespace PCK\LetterOfAward;

class LetterOfAwardUserPermissionRole {
    const EDITOR = 1;
    const REVIEWER = 2;

    public static function getRoleNameByModuleId($moduleId = null) {
        $mapping = [
            self::EDITOR     => trans('letterOfAward.editor'),
            self::REVIEWER   => trans('letterOfAward.reviewer'),
        ];

        return is_null($moduleId) ? $mapping : $mapping[$moduleId];
    }
}

