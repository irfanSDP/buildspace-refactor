<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidationException;
use Laracasts\Validation\FormValidator;

abstract class CustomFormValidator extends FormValidator {

    protected $errors;
    protected $validationMessage;
    protected $rules = [];
    protected $messages = [];
    protected $throwException = true;
    public $success = false;

    protected function setRules($formData)
    {
        // Update rules based on form input.
    }

    protected function setMessages()
    {
        // Update messages.
    }

    public function setThrowException($setTo = true)
    {
        $this->throwException = $setTo;
    }

    protected function getNewMessageBag()
    {
        return new MessageBag;
    }

    public function validate($formData)
    {
        $this->setRules($formData);
        $this->setMessages();

        $this->errors = new MessageBag;

        $this->errors->merge($this->preParentValidation($formData));

        try
        {
            parent::validate($formData);
        }
        catch(FormValidationException $exception)
        {
            $this->errors->merge($this->getValidationErrors());
        }

        if( $this->throwException ) $this->throwErrorsIfPresent();

        if( $this->errors->isEmpty() )
        {
            $this->errors = $this->postParentValidation($formData);

            if( $this->throwException ) $this->throwErrorsIfPresent();
        }

        if( $this->errors->isEmpty() ) $this->success = true;
    }

    public function getErrors()
    {
        if( ! $this->errors ) $this->errors = $this->getNewMessageBag();

        return $this->errors;
    }

    public function getErrorMessages()
    {
        $errors = [];

        foreach($this->getErrors()->toArray() as $key => $errorMsg)
        {
            $errors[] = [
                'key' => $key,
                'msg' => $errorMsg[0]
            ];
        }

        return $errors;
    }

    protected function throwErrorsIfPresent()
    {
        if( ! $this->errors->isEmpty() )
        {
            if( ! empty( $this->validationMessage ) ) $this->validationMessage = trans('validation.validationFailed');

            throw new FormValidationException($this->validationMessage, $this->errors);
        }
    }

    protected function preParentValidation($formData)
    {
        return new MessageBag;
    }

    protected function postParentValidation($formData)
    {
        return new MessageBag;
    }

}