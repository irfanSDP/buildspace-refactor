<?php namespace PCK\RequestForInspection;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\DirectedTo\DirectedTo;
use PCK\Helpers\ModelOperations;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;

class RequestForInspectionInspection extends Model implements Verifiable {

    const STATUS_PASSED                       = 1;
    const STATUS_REMEDY_WITH_RE_INSPECTION    = 2;
    const STATUS_REMEDY_WITHOUT_RE_INSPECTION = 3;

    use ModuleAttachmentTrait, InspectionVerifierProcessTrait;

    protected $table = 'request_for_inspection_inspections';

    protected $fillable = [
        'request_id',
        'comments',
        'remarks',
        'inspected_at',
        'status',
        'sequence_number',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $object)
        {
            \DB::transaction(function() use ($object)
            {
                $object->deleteRelatedModels();
            });
        });
    }

    public function request()
    {
        return $this->belongsTo('PCK\RequestForInspection\RequestForInspection', 'request_id');
    }

    public function reply()
    {
        return $this->hasOne('PCK\RequestForInspection\RequestForInspectionReply', 'inspection_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    private function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger($this->reply);
        Verifier::deleteLog($this);
    }

    public function updateRequestStatus()
    {
        switch($this->status)
        {
            case static::STATUS_PASSED:
                $this->request->status = RequestForInspection::STATUS_PASSED;
                break;
            case static::STATUS_REMEDY_WITH_RE_INSPECTION:
                $this->request->status = RequestForInspection::STATUS_REMEDY_REQUIRED;
                break;
            case static::STATUS_REMEDY_WITHOUT_RE_INSPECTION:
                $this->request->status = RequestForInspection::STATUS_REMEDY_REQUIRED;
                break;
            default:
                throw new \Exception('Invalid status');
        }

        $this->request->save();
    }

    public function isVisible(User $user)
    {
        if( Verifier::isApproved($this) ) return true;

        if( Verifier::isAVerifier($user, $this) ) return true;

        if( $user->id == $this->created_by && $user->stillInSameAssignedCompany($this->project, $this->created_at) ) return true;

        return false;
    }

    public function getInspectedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function isFinal()
    {
        return $this->status == static::STATUS_PASSED;
    }

    public static function canPost(User $user, RequestForInspection $request)
    {
        $project = $request->project;

        if( ! $user->isEditor($project) ) return false;

        if( $request->inspections->isEmpty() )
        {
            // Inspection after request.
            if( ! DirectedTo::isDirectedTo($user->getAssignedCompany($project)->getContractGroup($project), $request) ) return false;
        }
        else
        {
            // Inspection after reply or after another inspection has been submitted for drafting.
            $inspection = $request->inspections->last();

            // Inspection already submitted. No new Reply to follow up on.
            if( ! $inspection->reply ) return false;

            // Reply not approved.
            if( ! Verifier::isApproved($inspection->reply) ) return false;

            if( ! DirectedTo::isDirectedTo($user->getAssignedCompany($project)->getContractGroup($project), $inspection->reply) ) return false;
        }

        return true;
    }

    public function canUpdate(User $user)
    {
        if( ! $user->isEditor($this->project) ) return false;

        if( ! Verifier::isRejected($this) ) return false;

        if( $this->created_by != $user->id ) return false;

        if( ! $user->stillInSameAssignedCompany($this->project, $this->created_at) ) return false;

        return true;
    }

    public function getSubmitterId()
    {
        return $this->created_by;
    }
}