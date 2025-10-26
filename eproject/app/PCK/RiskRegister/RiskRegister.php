<?php namespace PCK\RiskRegister;

use PCK\DocumentControlObject\DocumentControlObject;
use PCK\Helpers\ModelOperations;
use PCK\Helpers\StringOperations;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class RiskRegister extends DocumentControlObject {

    const RISK_REGISTER_MODULE_NAME = 'Risk Register';
    
    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $riskRegister)
        {
            \DB::transaction(function() use ($riskRegister)
            {
                $riskRegister->deleteRelatedModels();
            });
        });
    }

    public function getReferenceAttribute()
    {
        return strtoupper(trans('riskRegister.risk')) . '-' . StringOperations::pad($this->reference_number, 4, '0');
    }

    public function canPostMessage(User $user)
    {
        if( ! $user->isEditor($this->project) ) return false;

        return true;
    }

    /**
     * Returns true if the risk is visible to the user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isVisible(User $user = null)
    {
        $riskPost = $this->getFirstMessage();

        return $riskPost->isVisible($user);
    }

    public function getLatestRisk()
    {
        return $this->hasMany($this->message_type, 'document_control_object_id')
            ->where('type', '=', RiskRegisterMessage::TYPE_RISK)
            ->orderBy('sequence_number', 'desc')
            ->first();
    }

    /**
     * Returns true if the user is allowed to update the published risk.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canUpdatePublishedRisk(User $user)
    {
        if( $user->id != $this->issuer->id ) return false;

        if( ! $user->isEditor($this->project) ) return false;

        foreach($this->messages as $message)
        {
            if( $message->isComment() ) continue;

            if( Verifier::isBeingVerified($message) ) return false;

            if( Verifier::isRejected($message) ) return false;
        }

        return true;
    }

    /**
     * Returns true if the user is allowed to post a comment.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canPostComment(User $user)
    {
        if( ! Verifier::isApproved($this->getFirstMessage()) ) return false;

        if( ! $user->isEditor($this->project) ) return false;

        return $this->isVisible($user);
    }

    private function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger($this->messages()->withTrashed()->get()->sortByDesc('sequence_number'));
    }

    public static function canPostRisk(User $user, Project $project)
    {
        return $user->isEditor($project);
    }

}