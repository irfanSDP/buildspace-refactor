<?php namespace PCK\Helpers;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidationException;
use Laracasts\Validation\FormValidator;

class FormValidatorHelper {

    /**
     * @param       $input
     * @param array ...$formValidators
     *
     * @throws FormValidationException
     * @throws \Exception
     */
    public static function validate($input, ...$formValidators)
    {
        $errors = new MessageBag();

        foreach($formValidators as $formValidator)
        {
            if( ! ( $formValidator instanceof FormValidator ) ) throw new \Exception('formValidator must be of type Form Validator.');
            try
            {
                $formValidator->validate($input);
            }
            catch(FormValidationException $exception)
            {
                $errors = $errors->merge($exception->getErrors());
            }
        }

        if( ! $errors->isEmpty() ) throw new FormValidationException(trans('forms.submissionFailed'), $errors);
    }

}