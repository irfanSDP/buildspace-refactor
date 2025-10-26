<?php

class BaseForm extends sfFormSymfony
{
    public function getErrors()
    {
        $errors = array();
        // individual widget errors
        foreach ($this as $form_field)
        {
            if($form_field->hasError())
            {
                $error_obj = $form_field->getError();
                if($error_obj instanceof sfValidatorErrorSchema)
                {
                    foreach ($error_obj->getErrors() as $error)
                    {
                        // if a field has more than 1 error, it'll be over-written
                        $errors[$form_field->getName()] = $error->getMessage();
                    }
                }
                else
                {
                    $errors[$form_field->getName()] = $error_obj->getMessage();
                }
            }
        }
        // global errors
        foreach ($this->getGlobalErrors() as $index => $validator_error)
        {
            $errors['global_errors'][$index] = $validator_error->getMessage();
        }
        return $errors;
    }
}
