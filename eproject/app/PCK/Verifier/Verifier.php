<?php namespace PCK\Verifier;

use Carbon\Carbon;
use Confide;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;
use PCK\Users\User;

class Verifier extends Model {

    use SoftDeletingTrait;

    protected $table = 'verifiers';
    protected $fillable = ['approved', 'verified_at', 'remarks'];

    public function object()
    {
        return $this->morphTo();
    }

    public function verifier()
    {
        return $this->belongsTo('PCK\Users\User', 'verifier_id');
    }

    /**
     * Sets the user as a verifier for the object.
     *
     * @param User       $user
     * @param Verifiable $object
     *
     * @return bool
     */
    protected static function setVerifier(User $user, Verifiable $object)
    {
        if( ! $object instanceof Model ) return false;

        $record = new self;
        $record->object()->associate($object);
        $record->verifier_id     = $user->id;
        $record->sequence_number = self::getNextSequenceNumber($object);

        return $record->save();
    }

    protected static function setVerifierAsApproved(User $user, Verifiable $object)
    {
        if( ! $object instanceof Model ) return false;

        $record = new self;
        $record->object()->associate($object);
        $record->verifier_id     = $user->id;
        $record->sequence_number = self::getNextSequenceNumber($object);
        $record->approved        = true;
        $record->verified_at = Carbon::now();

        return $record->save();
    }

    /**
     * Sets multiple users as verifiers for the object.
     *
     * @param array      $verifierIds
     * @param Verifiable $object
     */
    public static function setVerifiers(array $verifierIds, Verifiable $object)
    {
        // Removes previous verifier records.
        static::deleteLog($object);

        static::addVerifiers($verifierIds, $object);

        if( \PCK\Forum\ObjectThread::objectHasThread($object) )
        {
            self::syncForumUsers($object);
        }
    }

    public static function addVerifiers(array $verifierIds, Verifiable $object)
    {
        foreach($verifierIds as $key => $verifierId)
        {
            if(empty($verifierId)) unset($verifierIds[$key]);
        }

        $currentVerifierIds = Verifier::whereIn('verifier_id', $verifierIds)
            ->where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->lists('verifier_id');

        foreach(array_unique($verifierIds) as $verifierId)
        {
            if( ( ! $verifierId ) || empty( $verifierId ) || in_array($verifierId, $currentVerifierIds)) continue;

            static::setVerifier(User::find($verifierId), $object);
        }
    }

    /**
     * Updates the remarks column.
     * Todo: merge with the "approve" method.
     *
     * @param Verifiable $object
     * @param string     $remarks
     */
    public static function updateVerifierRemarks(Verifiable $object, $remarks) {
        $currentVerifier = self::getCurrentVerifier($object);

        self::where('object_type', '=', get_class($object))
            ->where('object_id' ,'=', $object->id)
            ->where('verifier_id', '=', $currentVerifier->id)
            ->update(array('remarks' => $remarks));
    }

    /**
     * Returns the next valid sequence number for the object.
     *
     * @param Verifiable $object
     *
     * @return mixed
     */
    private static function getNextSequenceNumber(Verifiable $object)
    {
        return self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->max('sequence_number') + 1;
    }

    /**
     * Returns true if the user is the current verifier.
     *
     * @param User       $user
     * @param Verifiable $object
     *
     * @return bool
     */
    public static function isCurrentVerifier(User $user, Verifiable $object)
    {
        if( ! $currentVerifier = self::getCurrentVerifier($object) ) return false;

        return ( $user->id == $currentVerifier->id );
    }

    /**
     * Returns the current verifier.
     * Returns null if there is none.
     *
     * @param Verifiable $object
     *
     * @return null
     */
    public static function getCurrentVerifier(Verifiable $object)
    {
        if( ! $record = self::getCurrentVerifierRecord($object) ) return null;

        return $record->verifier;
    }

    /**
     * Returns the record for the previous verifier who already verified
     * @param Verifiable $object
     * 
     * @return null
     */

    public static function getPreviousVerifierRecord(Verifiable $object)
    {
        if( self::isApproved($object) || self::isRejected($object) ) return null;

        $currentVerifier = self::getCurrentVerifierRecord($object);

        if($currentVerifier->sequence_number === 1) return null;

        return self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->where('sequence_number' , ($currentVerifier->sequence_number - 1))
            ->whereNotNull('approved')
            ->orderBy('sequence_number')
            ->first();
    }

    /**
     * Returns the record for the current verifier.
     *
     * @param Verifiable $object
     *
     * @return null
     */
    public static function getCurrentVerifierRecord(Verifiable $object)
    {
        if( self::isApproved($object) || self::isRejected($object) ) return null;

        return self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->whereNull('approved')
            ->orderBy('sequence_number')
            ->first();
    }

    /**
     * Sets the approval status of the object.
     *
     * @param Verifiable $object
     * @param bool       $approvedStatus
     *
     * @return bool
     */
    public static function approve(Verifiable $object, $approvedStatus = true)
    {
        if( ! $record = self::getCurrentVerifierRecord($object) ) return false;

        if( Confide::user()->id != $record->verifier_id ) return false;
        
        $record->approved = $approvedStatus;

        $record->verified_at = Carbon::now();

        return $record->save();
    }

    /**
     * Returns true if is object has obtained the approval of all verifiers.
     *
     * @param Verifiable $object
     *
     * @return mixed
     */
    public static function isApproved(Verifiable $object)
    {
        $rejectedRecords = self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->get()
            ->reject(function($record)
            {
                return ( $record->approved );
            });

        return ( $rejectedRecords->isEmpty() );
    }

    /**
     * Returns true if the object has been rejected by any verifier at any point.
     *
     * @param Verifiable $object
     *
     * @return bool
     */
    public static function isRejected(Verifiable $object)
    {
        $record = self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->where('approved', '=', false)
            ->first();

        return ( $record ) ? true : false;
    }

    /**
     * Returns true if object is still undergoing verification,
     * i.e. not all verifiers have verified.
     *
     * @param Verifiable $object
     *
     * @return bool
     */
    public static function isBeingVerified(Verifiable $object)
    {
        $currentVerifierRecord = self::getCurrentVerifierRecord($object);

        return ( ! empty( $currentVerifierRecord ) );
    }

    /**
     * Returns the records of all verifiers who have approved or rejected.
     *
     * @param Verifiable $object
     * @param bool       $withTrashed
     *
     * @return mixed
     */
    public static function getLog(Verifiable $object, $withTrashed = false)
    {
        $records = self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->whereNotNull('approved')
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $withTrashed )
        {
            $pastRecords = static::withTrashed()->where('object_type', '=', get_class($object))
                ->where('object_id', '=', $object->id)
                ->whereNotNull('approved')
                ->orderBy('verified_at', 'asc')
                ->get();

            $records = $pastRecords->merge($records);
        }

        return $records;
    }

    /**
     * Removes all verification log records of the object.
     *
     * @param Verifiable $object
     *
     * @return mixed
     */
    public static function deleteLog(Verifiable $object)
    {
        return self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->delete();
    }

    /**
     * Sends notification to the current verifier.
     *
     * @param Verifiable $object
     */
    public static function sendPendingNotification(Verifiable $object)
    {
        $currentVerifier = self::getCurrentVerifier($object);

        if( ! $currentVerifier ) return;

        $locale = $currentVerifier->settings->language->code;
        $subject = ($object->getEmailSubject($locale) == "") ? trans('email.eProjectNotification') : $object->getEmailSubject($locale);

        $emailView  = 'notifications.email.' . $object->getOnPendingView();
        $systemView = 'notifications.system.' . $object->getOnPendingView();
        $viewData   = $object->getViewData($locale);
        $route      = $object->getRoute();
        
        Mailer::queue(null, $emailView, $currentVerifier, $subject, $viewData);

        SystemNotifier::send(array( $currentVerifier ), $route, $systemView);
    }

    /**
     * Sends notification to notify that all verifiers have approved.
     *
     * @param Verifiable $object
     */
    public static function sendApprovedNotification(Verifiable $object)
    {
        $emailView  = 'notifications.email.' . $object->getOnApprovedView();
        $systemView = 'notifications.system.' . $object->getOnApprovedView();
        $route      = $object->getRoute();
        
        $recipients = $object->getOnApprovedNotifyList();

        foreach($recipients as $recipient)
        {
            $locale = $recipient->settings->language->code;
            $subject = ($object->getEmailSubject($locale) == "") ? trans('email.eProjectNotification') : $object->getEmailSubject($locale);
            $viewData   = $object->getViewData($locale);

            Mailer::queue(null, $emailView, $recipient, $subject, $viewData);
        }

        SystemNotifier::send($recipients, $route, $systemView);
    }

    /**
     * Sends notification to notify that a verifier has rejected.
     *
     * @param Verifiable $object
     */
    public static function sendRejectedNotification(Verifiable $object)
    {
        $emailView  = 'notifications.email.' . $object->getOnRejectedView();
        $systemView = 'notifications.system.' . $object->getOnRejectedView();
        $route      = $object->getRoute();

        $recipients = $object->getOnRejectedNotifyList();

        foreach($recipients as $recipient)
        {
            $locale   = $recipient->settings->language->code;
            $subject  = ($object->getEmailSubject($locale) == "") ? trans('email.eProjectNotification') : $object->getEmailSubject($locale);
            $viewData = $object->getViewData($locale);

            Mailer::queue(null, $emailView, $recipient, $subject, $viewData);
        }

        SystemNotifier::send($recipients, $route, $systemView);
    }

    /**
     * Returns the time when the object was verified.
     *
     * @param Verifiable $object
     *
     * @return bool|Carbon
     */
    public static function verifiedAt(Verifiable $object)
    {
        if( ! static::isApproved($object) ) return false;

        $records = static::getLog($object);

        if( $records->isEmpty() )
        {
            return $object->updated_at;
        }

        return static::getLog($object)->last()->created_at;
    }

    /**
     * Returns true if the user is (or will be)
     * a verifier of the object at any point.
     *
     * @param User       $user
     * @param Verifiable $object
     *
     * @return bool
     */
    public static function isAVerifier(User $user, Verifiable $object)
    {
        $record = self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->where('verifier_id', '=', $user->id)
            ->first();

        return $record ? true : false;
    }

    /**
     * Returns the records of all verifiers.
     * Similar to getLog(), except this includes the future verifiers.
     *
     * @param Verifiable $object
     * @param bool       $withTrashed
     *
     * @return mixed
     */
    public static function getAssignedVerifierRecords(Verifiable $object, $withTrashed = false)
    {
        $records = self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $withTrashed )
        {
            $pastRecords = static::withTrashed()->where('object_type', '=', get_class($object))
                ->where('object_id', '=', $object->id)
                ->whereNotNull('approved')
                ->orderBy('verified_at', 'asc')
                ->get();

            $records = $pastRecords->merge($records);
        }

        return $records;
    }

    /**
     * Returns all verifiers which have not approved, in ascending order
     * 
     * @param Verifiable $object
     * 
     * @return array
     */
    public static function getVerifiersInLine(Verifiable $object)
    {
        if( self::isApproved($object) || self::isRejected($object) ) return null;

        $verifiersInLine = new Collection();

        $records = self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->orderBy('sequence_number', 'asc')
            ->whereNull('approved')
            ->get();

        foreach($records as $record)
        {
            $verifiersInLine->push($record->verifier);
        }

        return $verifiersInLine;
    }

    public static function removeVerifier(Verifiable $object, $verifierId)
    {
        if( self::isApproved($object) || self::isRejected($object) ) return null;

        self::where('object_type', '=', get_class($object))
            ->where('object_id', '=', $object->id)
            ->where('verifier_id', '=', $verifierId)
            ->whereNull('approved')
            ->delete();

        return true;
    }

    public static function hasPendingVerifications(User $user)
    {
        $pendingVerificationCount = self::where('verifier_id', '=', $user->id)
                                        ->whereNull('approved')
                                        ->get()
                                        ->count();

        return ($pendingVerificationCount > 0);
    }

    /**
     * Find out if a user is an incoming verifier for a given object
     * 
     * @param User $user
     * @param Verifiable $object
     * 
     * @return bool
     */
    public static function isAVerifierInline(User $user, Verifiable $object)
    {
        $verifiersInLine = self::getVerifiersInLine($object);

        if( ! $verifiersInLine ) return false;

        return in_array($user->id, $verifiersInLine->lists('id'));
    }

    public static function canInitiateForumThread(User $user, Verifiable $object)
    {
        return self::isCurrentVerifier($user, $object) && ( ! \PCK\Forum\ObjectThread::objectHasThread($object) );
    }

    public static function syncForumUsers(Verifiable $object)
    {
        if( ! \PCK\Forum\ObjectThread::objectHasThread($object) ) return;

        $thread = \PCK\Forum\ObjectThread::getObjectThread($object);

        $userIds = array();

        $previousVerifiers = \PCK\Verifier\Verifier::getAssignedVerifierRecords($object)->filter(function($record){
            return ( ! is_null($record->approved) );
        });

        foreach($previousVerifiers as $record)
        {
            $userIds[] = $record->verifier_id;
        }

        $userIds[] = \PCK\Verifier\Verifier::getCurrentVerifierRecord($object)->verifier_id;

        $submittedById = $object->getSubmitterId();

        if( ! is_null($submittedById) ) $userIds[] = $submittedById;

        $thread->syncThreadUsers($userIds);
    }

    public static function initForumThread(\PCK\Projects\Project $project, $object)
    {
        $user = Confide::user();

        $thread = \PCK\Forum\Thread::init($project, $user, $object, $object->getModuleName());

        self::syncForumUsers($object);

        return $thread;
    }
}