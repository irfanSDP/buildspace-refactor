<?php namespace PCK\RequestForInspection;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\DirectableTrait;
use PCK\Base\ModuleAttachmentTrait;
use PCK\DirectedTo\DirectedTo;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;

class RequestForInspectionReply extends Model implements Verifiable {

    const TYPE_REQUEST  = 1;
    const TYPE_RESPONSE = 2;

    use ModuleAttachmentTrait, DirectableTrait, ReplyVerifierProcessTrait;

    protected $table = 'request_for_inspection_replies';

    protected $fillable = [
        'request_id',
        'inspection_id',
        'comments',
        'ready_date',
        'completed_date',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function(self $object)
        {
            if( $object->inspection->status == RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION )
            {
                $object->completed_date = null;
            }

            if( $object->inspection->status == RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION )
            {
                $object->ready_date = null;
            }
        });

        static::deleting(function(self $object)
        {
            \DB::transaction(function() use ($object)
            {
                $object->deleteRelatedModels();
            });
        });
    }

    private function deleteRelatedModels()
    {
        DirectedTo::removeRelations($this);
        Verifier::deleteLog($this);
    }

    public function getReadyDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getCompletedDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function inspection()
    {
        return $this->belongsTo('PCK\RequestForInspection\RequestForInspectionInspection', 'inspection_id');
    }

    public function isVisible(User $user)
    {
        if( Verifier::isApproved($this) ) return true;

        if( Verifier::isAVerifier($user, $this) ) return true;

        if( $user->id == $this->created_by && $user->stillInSameAssignedCompany($this->project, $this->created_at) ) return true;

        return false;
    }

    public function updateRequestStatus()
    {
        switch($this->inspection->status)
        {
            case RequestForInspectionInspection::STATUS_PASSED:
                throw new \Exception('Passed inspection should not have a reply');
            case RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION:
                $this->inspection->request->status = RequestForInspection::STATUS_REQUESTING;
                break;
            case RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION:
                $this->inspection->request->status = RequestForInspection::STATUS_COMPLETED;
                break;
            default:
                throw new \Exception('Invalid status');
        }

        $this->inspection->request->save();
    }

    public static function canPost(User $user, RequestForInspectionInspection $inspection)
    {
        if( ! Verifier::isApproved($inspection) ) return false;

        // Inspection already replied to.
        if( $inspection->reply ) return false;

        return true;
    }

    public function canUpdate(User $user)
    {
        if( ! Verifier::isRejected($this) ) return false;

        if( $this->created_by != $user->id ) return false;

        if( ! $user->stillInSameAssignedCompany($this->project, $this->created_at) ) return false;

        return true;
    }

}