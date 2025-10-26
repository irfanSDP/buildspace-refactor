<?php namespace PCK\Exceptions;

use Illuminate\Support\MessageBag;

/*
* @deprecated Use FormValidationException instead.
*/
class ValidationException extends \Exception {

    private $messageBag;

    public function setMessageBag(MessageBag $messageBag)
    {
        $this->messageBag = $messageBag;
    }

    public function getMessageBag()
    {
        return $this->messageBag;
    }

    public function getErrors()
    {
        return $this->getMessageBag();
    }
}