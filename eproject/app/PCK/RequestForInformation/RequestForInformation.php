<?php namespace PCK\RequestForInformation;

use PCK\DirectedTo\DirectedTo;
use PCK\DocumentControlObject\DocumentControlObject;
use PCK\Helpers\ModelOperations;
use PCK\Helpers\StringOperations;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class RequestForInformation extends DocumentControlObject {

    const REQUEST_FOR_INFORMATION_MODULE_NAME = 'Request For Information';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $rfi)
        {
            \DB::transaction(function() use ($rfi)
            {
                $rfi->deleteRelatedModels();
            });
        });
    }

    public function getReferenceAttribute()
    {
        return trans('requestForInformation.rfi') . '-' . StringOperations::pad($this->reference_number, 4, '0');
    }

    public function canPostMessage(User $user)
    {
        if( ! $user->isEditor($this->project) ) return false;

        return ( $this->canRequest($user) || $this->canRespond($user) );
    }

    /**
     * Returns true if the user is allowed to make a request on the RFI.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canRequest(User $user)
    {
        if( $this->issuer->id != $user->id ) return false;

        if( ! $user->isEditor($this->project) ) return false;

        // Regular (new) request.
        if( $this->getLastMessage()->isResponse() && Verifier::isApproved($this->getLastMessage()) ) return true;

        // Edit request.
        if( $this->getLastMessage()->isRequest() && Verifier::isRejected($this->getLastMessage()) ) return true;

        return false;
    }

    /**
     * Returns true if the user is allowed to respond to the RFI.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canRespond(User $user)
    {
        if( ! $user->isEditor($this->project) ) return false;

        $contractGroup = $user->getAssignedCompany($this->project)->getContractGroup($this->project);

        if( ! DirectedTo::isDirectedTo($contractGroup, $this->getLastRequest()) ) return false;

        // Regular (new) response.
        if( $this->getLastMessage()->isRequest() && Verifier::isApproved($this->getLastMessage()) ) return true;

        // Edit response.
        if( $this->getLastMessage()->isResponse() && Verifier::isRejected($this->getLastMessage()) ) return true;

        return false;
    }

    public function getLastRequest()
    {
        return $this->hasMany($this->message_type, 'document_control_object_id')
            ->where('type', '=', RequestForInformationMessage::TYPE_REQUEST)
            ->orderBy('sequence_number', 'desc')
            ->first();
    }

    /**
     * Returns the last visible request to the user.
     *
     * @return null|RequestForInformationMessage
     */
    public function getLastVisibleRequest()
    {
        $questions = $this->hasMany($this->message_type, 'document_control_object_id')
            ->where('type', '=', RequestForInformationMessage::TYPE_REQUEST)
            ->orderBy('sequence_number', 'desc')
            ->get();

        foreach($questions as $question)
        {
            if( $question->isVisible() ) return $question;
        }

        return null;
    }

    private function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger($this->messages()->withTrashed()->get()->sortByDesc('sequence_number'));
    }

    public static function canCreateRfiMessage(User $user, Project $project)
    {
        return $user->isEditor($project);
    }

    public function canPushMessage(User $user)
    {
        return ( $this->canRespond($user) || $this->canRequest($user) );
    }

}