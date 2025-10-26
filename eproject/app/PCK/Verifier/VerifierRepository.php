<?php namespace PCK\Verifier;

use PCK\Users\User;

class VerifierRepository {

    public function onReview(Verifiable $object)
    {
        if( ( $function = $object->onReview() ) && is_callable($function) )
        {
            call_user_func($function);
        }
    }

    public function onApproved(Verifiable $object)
    {
        if( ( $function = $object->getOnApprovedFunction() ) && is_callable($function) )
        {
            call_user_func($function);
        }
    }

    public function onRejected(Verifiable $object)
    {
        if( ( $function = $object->getOnRejectedFunction() ) && is_callable($function) )
        {
            call_user_func($function);
        }

        if( \PCK\Forum\ObjectThread::objectHasThread($object) )
        {
            $thread = \PCK\Forum\ObjectThread::getObjectThread($object);
            $thread->users()->sync(array());
        }
    }

    public function onPending(Verifiable $object)
    {
        if( method_exists($object, 'getOnPendingFunction') )
        {
            if( ( $function = $object->getOnPendingFunction() ) && is_callable($function) )
            {
                call_user_func($function);
            }
        }

        if( \PCK\Forum\ObjectThread::objectHasThread($object) )
        {
            Verifier::syncForumUsers($object);
        }
    }

    public function sendNotifications(Verifiable $object)
    {
        if( Verifier::isApproved($object) ) Verifier::sendApprovedNotification($object);

        if( Verifier::isBeingVerified($object) ) Verifier::sendPendingNotification($object);

        if( Verifier::isRejected($object) ) Verifier::sendRejectedNotification($object);
    }

    public function executeFollowUp(Verifiable $object)
    {
        $this->onReview($object);

        if( Verifier::isApproved($object) ) $this->onApproved($object);

        if( Verifier::isRejected($object) ) $this->onRejected($object);

        if( Verifier::isBeingVerified($object) ) $this->onPending($object);

        if( $object->disableNotifications !== true ) $this->sendNotifications($object);
    }

    public function updateVerifierRemarks(Verifiable $object, $remarks)
    {
        Verifier::updateVerifierRemarks($object, $remarks);
    }

    public function approve(Verifiable $object, $approvedStatus, User $user = null)
    {
        Verifier::approve($object, $approvedStatus);
        
        $this->executeFollowUp($object);
    }

    public function setVerifiers(array $verifierIds, Verifiable $object)
    {
        Verifier::setVerifiers($verifierIds, $object);
    }

    public function getCurrentVerifier(Verifiable $object)
    {
        return Verifier::getCurrentVerifier($object);
    }

    public function isCurrentVerifier(User $user, Verifiable $object)
    {
        return Verifier::isCurrentVerifier($user, $object);
    }
}