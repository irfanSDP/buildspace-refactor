<?php namespace PCK\IndonesiaCivilContract\ArchitectInstruction;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\IndonesiaCivilContract\ArchitectInstruction\VerifiableTrait;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;

class ArchitectInstruction extends Model implements Verifiable {

    use TimestampFormatterTrait, ModuleAttachmentTrait, VerifiableTrait;

    const STATUS_SUBMITTED      = 1;
    const STATUS_SUBMITTED_TEXT = 'submitted';

    const STATUS_DRAFT      = 2;
    const STATUS_DRAFT_TEXT = 'draft';

    const STATUS_PENDING      = 3;
    const STATUS_PENDING_TEXT = 'pending';

    protected $table = 'indonesia_civil_contract_architect_instructions';

    protected $fillable = array(
        'project_id',
        'user_id',
        'reference',
        'instruction',
        'deadline_to_comply',
        'status',
    );

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $self)
        {
            \DB::transaction(function() use ($self)
            {
                $self->attachedClauses()->delete();
                $self->responses()->delete();
            });
        });
    }

    public function getStatusTextAttribute()
    {
        switch($this->status)
        {
            case self::STATUS_DRAFT:
                return trans('forms.' . self::STATUS_DRAFT_TEXT);

            case self::STATUS_SUBMITTED:
                return trans('forms.' . self::STATUS_SUBMITTED_TEXT);

            case self::STATUS_PENDING:
                return trans('forms.' . self::STATUS_PENDING_TEXT);

            default:
                throw new \InvalidArgumentException('Invalid AI\'s Type');
        }
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function attachedClauses()
    {
        return $this->morphMany('PCK\ClauseItems\AttachedClauseItem', 'attachable')->orderBy('priority', 'asc');
    }

    public function responses()
    {
        return $this->morphMany('PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse', 'object')->orderBy('sequence', 'asc');
    }

    public function requestsForInformation()
    {
        return $this->belongsToMany('PCK\RequestForInformation\RequestForInformation', 'indonesia_civil_contract_ai_rfi', 'indonesia_civil_contract_architect_instruction_id', 'document_control_object_id')
            ->orderBy('reference_number', 'desc')
            ->withTimestamps();
    }

    public function extensionsOfTime()
    {
        return $this->hasMany('PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime', 'indonesia_civil_contract_ai_id')
            ->orderBy('id', 'desc');
    }

    public function lossAndExpenses()
    {
        return $this->hasMany('PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpense', 'indonesia_civil_contract_ai_id')
            ->orderBy('id', 'desc');
    }

    public function isVisible(User $user)
    {
        if( $this->user_id == $user->id ) return true;

        if( ( $this->status == static::STATUS_PENDING ) && Verifier::isBeingVerified($this) ) return Verifier::isCurrentVerifier($user, $this);

        return $this->status != static::STATUS_DRAFT;
    }

    public function isEditable(User $user)
    {
        if( $this->user_id != $user->id ) return false;

        return $this->status == static::STATUS_DRAFT;
    }

    public function canRespond(User $user)
    {
        if( ! $this->contractorsTurn() )
        {
            return ProjectModulePermission::isAssigned($this->project, $user, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION);
        }
        else // Contractor's turn.
        {
            return $user->getAssignedCompany($this->project)->id == $this->project->getSelectedContractor()->id;
        }
    }

    public function getNextResponseSequenceNumber()
    {
        // Reload to reflect latest status.
        $this->load('responses');

        return ( $this->responses->last()->sequence ?? 0 ) + 1;
    }

    /**
     * Determine whose turn it is.
     * Person in charge's turn if next sequence number is even; first respondent is contractor.
     *
     * @return bool
     */
    public function contractorsTurn()
    {
        return ( ( $this->getNextResponseSequenceNumber() % 2 ) != 0 );
    }

}