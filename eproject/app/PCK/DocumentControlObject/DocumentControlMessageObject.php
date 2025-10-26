<?php namespace PCK\DocumentControlObject;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;

abstract class DocumentControlMessageObject extends Model implements Verifiable {

    abstract public function documentControlObject();

    public function composer()
    {
        return $this->belongsTo('PCK\Users\User', 'composed_by');
    }

    public function responseTo()
    {
        return $this->belongsTo($this->message_type, 'response_to');
    }

    public static function getNextSequenceNumber(DocumentControlObject $documentControlObject)
    {
        return static::where('document_control_object_id', '=', $documentControlObject->id)->max('sequence_number') + 1;
    }

    /**
     * Returns the number of days left to the reply deadline of the latest question.
     *
     * @return int
     */
    public function getDaysLeft()
    {
        $deadline = Carbon::parse($this->reply_deadline);
        $now      = Carbon::now();

        $diffInDays = $deadline->diffInDays($now);

        if( ( $deadline < $now ) && ( $diffInDays != 0 ) ) $diffInDays = -$diffInDays;

        return $diffInDays;
    }

    /**
     * Returns true if the message is visible to the user.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function isVisible(User $user = null)
    {
        if( ! $user ) $user = \Confide::user();

        if( Verifier::isApproved($this) ) return true;

        if( Verifier::isCurrentVerifier($user, $this) ) return true;

        if( $this->composer->id == $user->id ) return true;

        if( ( $this->composer->getAssignedCompany($this->documentControlObject->project)->id == $user->getAssignedCompany($this->documentControlObject->project)->id ) ) return true;

        return false;
    }

    /**
     * Moves object to the end of the message thread.
     *
     * @return bool
     */
    public function moveToThreadEnd()
    {
        $newSequenceNumber = static::getNextSequenceNumber($this->documentControlObject);

        $this->sequence_number = $newSequenceNumber;

        return $this->save();
    }

}