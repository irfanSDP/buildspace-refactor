<?php namespace PCK\FormBuilder\Elements;

class FormBuilderElementCommon
{
    const DROPDOWN_SINGLE_SELECT   = 1;
    const DROPDOWN_MULTIPLE_SELECT = 2;

    public static function getDropdownSelectTypes()
    {
        return [
            self::DROPDOWN_SINGLE_SELECT   => trans('formBuilder.singleSelection'),
            self::DROPDOWN_MULTIPLE_SELECT => trans('formBuilder.multipleSelection'),
        ];
    }
}

