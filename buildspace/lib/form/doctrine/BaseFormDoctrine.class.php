<?php

/**
 * Project form base class.
 *
 * @package    buildspace
 * @subpackage form
 * @author     FahmiAnuar
 * @version    SVN: $Id: sfDoctrineFormBaseTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class BaseFormDoctrine extends sfFormDoctrine
{
    public function setup()
    {
    }

    public function getErrors()
    {
        $errors = array();
        // individual widget errors
        foreach ($this as $formField)
        {
            if($formField->hasError())
            {
                $errorObj = $formField->getError();
                if($errorObj instanceof sfValidatorErrorSchema)
                {
                    foreach ($errorObj->getErrors() as $error)
                    {
                        // if a field has more than 1 error, it'll be over-written
                        $errors[$formField->getName()] = $error->getMessage();
                    }
                }
                else
                {
                    $errors[$formField->getName()] = $errorObj->getMessage();
                }
            }
        }
        // global errors
        foreach ($this->getGlobalErrors() as $index => $validatorError)
        {
            $errors['global_errors'][$index] = $validatorError->getMessage();
        }
        return $errors;
    }

    public static function _formValuesAreBlank(array $fieldNames, array $values)
    {
        foreach($fieldNames as $fieldName)
        {
            if(isset($values[$fieldName]) && !self::formValueIsBlank($values[$fieldName])) return false;
        }
        return true;
    }

    public static function formValueIsBlank($value)
    {
        if(is_array($value))
        {
            foreach($value as $subValue)
            {
                if(!self::formValueIsBlank($subValue)) return false;
            }
            return true;
        }
        return $value ? false : true;
    }
}
