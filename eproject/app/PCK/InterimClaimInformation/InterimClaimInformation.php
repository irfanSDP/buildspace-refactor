<?php namespace PCK\InterimClaimInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractGroups\Types\Role;
use PCK\InterimClaims\InterimClaim;

class InterimClaimInformation extends Model {

    use TimestampFormatterTrait;

    protected $appends = [ 'honouring_certificate_period' ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model)
        {
            $ic = $model->interimClaim;

            // will add status checking later, we don't want to process drafted record
            if( $model->type == Role::CONTRACTOR )
            {
                $ic->amount_claimed = $model->net_amount_of_payment_certified;
            }

            if( $model->type == Role::INSTRUCTION_ISSUER )
            {
                $ic->amount_granted = $model->net_amount_of_payment_certified;
                $ic->status         = InterimClaim::GRANTED;
            }

            $ic->save();
        });

        static::deleting(function ($model)
        {
            $model->deleteRelatedModels();
        });
    }

    public function interimClaim()
    {
        return $this->belongsTo('PCK\InterimClaims\InterimClaim');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function nettAdditionOmissionAttachments()
    {
        return $this->hasMany('PCK\ICInfoNettAddOmiAttachments\ICInfoNettAddOmiAttachment');
    }

    public function grossValuesAttachments()
    {
        return $this->hasMany('PCK\ICInfoGrossValuesAttachments\ICInfoGrossValuesAttachment');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function getDateOfCertificateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function getHonouringCertificatePeriodAttribute()
    {
        return self::calculateDeadlineToSubmitNoticeToClaim($this->interimClaim->project, $this->created_at, $this->interimClaim->project->pam2006Detail->period_of_honouring_certificate);
    }

    public static function calculateDeadlineToSubmitNoticeToClaim(Project $project, $startDate, $claimDuration)
    {
        $calendarRepo = \App::make('PCK\Calendars\CalendarRepository');

        return $calendarRepo->calculateFinalDate($project, $startDate, $claimDuration);
    }

    /**
     * Deletes related records.
     *
     * @throws \Exception
     */
    protected function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->nettAdditionOmissionAttachments,
            $this->grossValuesAttachments,
        ));
    }
}