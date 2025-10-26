<?php namespace PCK\RequestForInformation;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Base\DirectableTrait;
use PCK\Base\ModuleAttachmentTrait;
use PCK\DocumentControlObject\DocumentControlMessageObject;
use PCK\Helpers\ModelOperations;
use PCK\Verifier\Verifier;

class RequestForInformationMessage extends DocumentControlMessageObject {

    const TYPE_REQUEST  = 1;
    const TYPE_RESPONSE = 2;

    use ModuleAttachmentTrait, DirectableTrait, SoftDeletingTrait, VerifierProcessTrait;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $message)
        {
            \DB::transaction(function() use ($message)
            {
                $message->deleteRelatedModels();
            });
        });
    }

    public function documentControlObject()
    {
        return $this->requestForInformation();
    }

    public function requestForInformation()
    {
        return $this->belongsTo('PCK\RequestForInformation\RequestForInformation', 'document_control_object_id');
    }

    /**
     * Returns a response to the current message.
     *
     * @param bool $approved
     *
     * @return null
     */
    public function getResponse($approved = true)
    {
        $response = static::where('response_to', '=', $this->id)
            ->where('type', '=', self::TYPE_RESPONSE)
            ->first();

        if( ! $approved ) return $response;

        if( ( ! is_null($response) ) && Verifier::isApproved($response) ) return $response;

        return null;
    }

    public function isRequest()
    {
        return $this->type == self::TYPE_REQUEST;
    }

    public function isResponse()
    {
        return $this->type == self::TYPE_RESPONSE;
    }

    private function deleteRelatedModels()
    {
        $replies = self::where('response_to', '=', $this->id)->withTrashed()->get();

        ModelOperations::deleteWithTrigger($replies);
    }

}