<?php namespace PCK\IndonesiaCivilContract\ExtensionOfTime;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Projects\Project;
use PCK\Users\User;

class ExtensionOfTime extends Model {

    use TimestampFormatterTrait, ModuleAttachmentTrait;

    const STATUS_SUBMITTED      = 1;
    const STATUS_SUBMITTED_TEXT = 'submitted';

    const STATUS_DRAFT      = 2;
    const STATUS_DRAFT_TEXT = 'draft';

    const STATUS_APPROVED      = 4;
    const STATUS_APPROVED_TEXT = 'approved';

    const STATUS_REJECTED      = 8;
    const STATUS_REJECTED_TEXT = 'rejected';

    const STATUS_GRANTED      = 16;
    const STATUS_GRANTED_TEXT = 'granted';

    protected $table = 'indonesia_civil_contract_extensions_of_time';

    protected $fillable = array(
        'project_id',
        'user_id',
        'indonesia_civil_contract_ai_id',
        'reference',
        'subject',
        'details',
        'status',
        'days',
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

            case self::STATUS_APPROVED:
                return trans('forms.' . self::STATUS_APPROVED_TEXT);

            case self::STATUS_REJECTED:
                return trans('forms.' . self::STATUS_REJECTED_TEXT);

            case self::STATUS_GRANTED:
                return trans('forms.' . self::STATUS_GRANTED_TEXT);

            default:
                throw new \InvalidArgumentException('Invalid LE\'s Status');
        }
    }

    public static function getResponseTypeText(ContractualClaimResponse $response, $type = null)
    {
        $types = array(
            ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE => trans('extensionOfTime.' . ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE_TEXT),
            ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE   => trans('extensionOfTime.' . ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE_TEXT),
            ContractualClaimResponse::TYPE_GRANT                   => trans('extensionOfTime.' . ContractualClaimResponse::TYPE_GRANT_TEXT, array( 'days' => number_format($response->proposed_value, 0) )),
        );

        if( empty( $types[ $type ] ) ) return null;

        return ( $type ) ? $types[ $type ] : $types;
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

    public function architectInstruction()
    {
        return $this->belongsTo('PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction', 'indonesia_civil_contract_ai_id');
    }

    public function earlyWarnings()
    {
        return $this->belongsToMany('PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarning', 'indonesia_civil_contract_ew_eot', 'indonesia_civil_contract_eot_id', 'indonesia_civil_contract_ew_id')
            ->orderBy('id', 'desc')
            ->withTimestamps();
    }

    public function isVisible(User $user)
    {
        if( $this->user_id == $user->id ) return true;

        return $this->status != static::STATUS_DRAFT;
    }

    public function isEditable(User $user)
    {
        if( $this->user_id != $user->id ) return false;

        return $this->status == static::STATUS_DRAFT;
    }

    public function canRespond(User $user)
    {
        $lastResponse = $this->responses->last();

        // Determine whose turn it is.
        $peopleInChargesTurn = ( ! $lastResponse ) || ( $lastResponse->type == ContractualClaimResponse::TYPE_PLAIN );

        if( $peopleInChargesTurn )
        {
            return ProjectModulePermission::isAssigned($this->project, $user, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_EXTENSION_OF_TIME);
        }
        else // Contractor's turn.
        {
            if( $lastResponse->type == ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE ) return false;
            if( ( $lastResponse->type == ContractualClaimResponse::TYPE_GRANT ) && ( $lastResponse->proposed_value >= $this->days ) ) return false;

            return $user->getAssignedCompany($this->project)->id == $this->project->getSelectedContractor()->id;
        }
    }

    public function getNextResponseSequenceNumber()
    {
        // Reload to reflect latest status.
        $this->load('responses');

        return ( $this->responses->last()->sequence ?? 0 ) + 1;
    }

    public function proposedAmount()
    {
        return $this->days;
    }

    public function grantedAmount()
    {
        if( $this->status == self::STATUS_GRANTED ) return (int)$this->responses()->where('type', '=', ContractualClaimResponse::TYPE_GRANT)->get()->last()->proposed_value;

        if( $this->status == self::STATUS_APPROVED ) return $this->proposedAmount();

        if( $this->status == self::STATUS_REJECTED ) return 0;

        return null;
    }

    /**
     * Determine whose turn it is.
     * Person in charge's turn if next sequence number is odd; first respondent is person in charge.
     *
     * @return bool
     */
    public function contractorsTurn()
    {
        return ( ( $this->getNextResponseSequenceNumber() % 2 ) == 0 );
    }

}