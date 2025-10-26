<?php namespace PCK\InterimClaims;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractualClaim\ContractualClaimInterface;
use PCK\Helpers\ModelOperations;

class InterimClaim extends Model implements StatusType, ContractualClaimInterface {

    use TimestampFormatterTrait, StatusTypeTrait, ModuleAttachmentTrait, ReminderRelations;

    protected $appends = array( 'max_retention_fund' );

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model)
        {
            // delete attached attachments if available
            $model->attachments()->delete();

            $model->deleteRelatedModels();
        });
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function interimClaimInformation()
    {
        return $this->hasMany('PCK\InterimClaimInformation\InterimClaimInformation');
    }

    public function getIssueCertificateDeadlineAttribute($date)
    {
        return Carbon::parse($date)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function getMaxRetentionFundAttribute()
    {
        $limit       = $this->project->pam2006Detail->limit_retention_fund / 100;
        $contractSum = $this->project->pam2006Detail->contract_sum;

        return $contractSum * $limit;
    }

    public function getCertifiedRetentionFund($value)
    {
        if( is_null($value) )
        {
            $value = 0;
        }

        $limit = $this->project->pam2006Detail->percentage_of_certified_value_retained / 100;

        return $value * $limit;
    }

    /**
     * Deletes related records.
     */
    protected function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->interimClaimInformation,
            $this->architectInstructionInterimClaims,
            $this->lossOrAndExpenseInterimClaims,
            $this->additionalExpenseInterimClaims,
            $this->architectClaimInformation,
            $this->contractorClaimInformation,
            $this->qsConsultantClaimInformation,
        ));
    }

}