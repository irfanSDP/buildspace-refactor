<?php namespace PCK\RequestForInspection;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\DirectableTrait;
use PCK\Base\ModuleAttachmentTrait;
use PCK\DirectedTo\DirectedTo;
use PCK\Helpers\ModelOperations;
use PCK\Helpers\StringOperations;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;

class RequestForInspection extends Model implements Verifiable {

    use RequestVerifierProcessTrait, ModuleAttachmentTrait, DirectableTrait;

    protected $table = 'requests_for_inspection';

    protected $fillable = [
        'project_id',
        'reference_number',
        'inspection_reference',
        'subject',
        'description',
        'location',
        'works',
        'created_by',
        'status',
        'ready_date',
    ];

    const STATUS_REQUESTING           = 1;
    const STATUS_REQUESTING_TEXT      = 'Requesting';
    const STATUS_REMEDY_REQUIRED      = 2;
    const STATUS_REMEDY_REQUIRED_TEXT = 'Remedy';
    const STATUS_COMPLETED            = 3;
    const STATUS_COMPLETED_TEXT       = 'Completed';
    const STATUS_PASSED               = 4;
    const STATUS_PASSED_TEXT          = 'Passed';

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

    public static function getValidStatuses()
    {
        return array(
            static::STATUS_REQUESTING,
            static::STATUS_REMEDY_REQUIRED,
            static::STATUS_COMPLETED,
            static::STATUS_PASSED,
        );
    }

    public static function getStatusName($status)
    {
        switch($status)
        {
            case static::STATUS_REQUESTING:
                return static::STATUS_REQUESTING_TEXT;
            case static::STATUS_REMEDY_REQUIRED:
                return static::STATUS_REMEDY_REQUIRED_TEXT;
            case static::STATUS_COMPLETED:
                return static::STATUS_COMPLETED_TEXT;
            case static::STATUS_PASSED:
                return static::STATUS_PASSED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public static function getNextReferenceNumber($project)
    {
        return static::where('project_id', '=', $project->id)
            ->max('reference_number') + 1;
    }

    public function getNextSequenceNumber()
    {
        return $this->inspections->count() + 1;
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function inspections()
    {
        return $this->hasMany('PCK\RequestForInspection\RequestForInspectionInspection', 'request_id')
            ->orderBy('sequence_number', 'asc');
    }

    public function getReferenceAttribute()
    {
        return StringOperations::pad($this->reference_number, 4, '0');
    }

    private function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger($this->inspections);
        DirectedTo::removeRelations($this);
        Verifier::deleteLog($this);
    }

    public function isCompleted()
    {
        if( $this->inspections->isEmpty() ) return false;

        $isCompleted    = false;
        $lastInspection = $this->inspections->last();

        switch($lastInspection->status)
        {
            case RequestForInspectionInspection::STATUS_PASSED:
                $isCompleted = Verifier::isApproved($lastInspection);
                break;
            case RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION:
                $isCompleted = false;
                break;
            case RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION:
                if( $lastInspection->reply && Verifier::isApproved($lastInspection->reply) ) $isCompleted = true;
                break;
            default:
                throw new \Exception('Invalid status');
        }

        return $isCompleted;
    }

    public function isVisible(User $user)
    {
        if( Verifier::isApproved($this) ) return true;

        if( Verifier::isAVerifier($user, $this) ) return true;

        if( $user->id == $this->created_by && $user->stillInSameAssignedCompany($this->project, $this->created_at) ) return true;

        return false;
    }

    public function getReadyDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getLastReply()
    {
        $lastReply = null;

        foreach($this->inspections->reverse() as $inspection)
        {
            if( $inspection->reply && Verifier::isApproved($inspection->reply) )
            {
                $lastReply = $inspection->reply;
                break;
            }
        }

        return $lastReply;
    }

    public function getInspectionReadyDate()
    {
        $readyDate = $this->ready_date;

        if( $lastReply = $this->getLastReply() )
        {
            $readyDate = $lastReply->ready_date;
        }

        return $readyDate;
    }

    /**
     * Returns the number of approved (i.e. publicly available)
     * Requests for Inspection.
     *
     * @param Project $project
     *
     * @return int
     */
    public static function getPublicCount(Project $project)
    {
        $requests = static::where('project_id', '=', $project->id)->get();

        $publicRequests = $requests->reject(function($request)
        {
            return ( ! Verifier::isApproved($request) );
        });

        return $publicRequests->count();
    }

    public static function canPost(User $user, Project $project)
    {
        return $user->isEditor($project);
    }

    public function canUpdate(User $user)
    {
        if( ! Verifier::isRejected($this) ) return false;

        if( ! $user->isEditor($this->project) ) return false;

        if( $user->id != $this->created_by ) return false;

        if( ! $user->stillInSameAssignedCompany($this->project, $this->created_at) ) return false;

        return true;
    }

}