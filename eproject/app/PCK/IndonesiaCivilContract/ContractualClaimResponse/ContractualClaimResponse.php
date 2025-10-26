<?php namespace PCK\IndonesiaCivilContract\ContractualClaimResponse;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ContractualClaimResponse extends Model {

    use TimestampFormatterTrait, ModuleAttachmentTrait;

    const TYPE_PLAIN = 1;

    const TYPE_AGREE_ON_PROPOSED_VALUE      = 2;
    const TYPE_AGREE_ON_PROPOSED_VALUE_TEXT = 'type_agreeOnProposedValue';

    const TYPE_REJECT_PROPOSED_VALUE      = 3;
    const TYPE_REJECT_PROPOSED_VALUE_TEXT = 'type_rejectProposedValue';

    const TYPE_GRANT      = 4;
    const TYPE_GRANT_TEXT = 'type_grant';

    protected $fillable = array(
        'user_id',
        'subject',
        'content',
        'sequence',
        'type',
        'proposed_value',
    );

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $self)
        {
            \DB::transaction(function() use ($self)
            {
                $self->attachedClauses()->delete();
            });
        });
    }

    protected $table = 'indonesia_civil_contract_contractual_claim_responses';

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function getSubmittedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function object()
    {
        return $this->morphTo();
    }

    public static function getCurrentSequenceNumber(Model $object)
    {
        return self::where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->max('sequence') ?? 0;
    }

    public static function getNextSequenceNumber(Model $object)
    {
        return self::getCurrentSequenceNumber($object) + 1;
    }

}