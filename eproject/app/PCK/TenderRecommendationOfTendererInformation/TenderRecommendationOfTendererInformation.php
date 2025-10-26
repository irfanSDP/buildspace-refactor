<?php namespace PCK\TenderRecommendationOfTendererInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use PCK\TenderFormVerifierLogs\FormLevelStatus as TenderFormLevelStatus;

class TenderRecommendationOfTendererInformation extends Model implements TenderFormLevelStatus {

    use TimestampFormatterTrait;

    const MODAL_ID = 'recTendererContractorDialog';

    protected $table = 'tender_rot_information';

    CONST COMPLETION_PERIOD_METRIC_TYPE_MONTHS = 1;
    CONST COMPLETION_PERIOD_METRIC_TYPE_WEEKS  = 2;
    CONST COMPLETION_PERIOD_METRIC_TYPE_DAYS   = 4;

    const RECOMMENDATION_OF_TENDERER_MODULE_NAME = 'Recommendation of Tenderer';
    
    protected $with = array( 'verifiers' );

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $tenderRecommendationOfTendererInformation)
        {
            $tenderRecommendationOfTendererInformation->deleteRelatedModels();
        });
    }

    public static function getCompletionPeriodMetrics()
    {
        return array(
            self::COMPLETION_PERIOD_METRIC_TYPE_MONTHS => self::getCompletionPeriodMetricText(self::COMPLETION_PERIOD_METRIC_TYPE_MONTHS),
            self::COMPLETION_PERIOD_METRIC_TYPE_WEEKS  => self::getCompletionPeriodMetricText(self::COMPLETION_PERIOD_METRIC_TYPE_WEEKS),
            self::COMPLETION_PERIOD_METRIC_TYPE_DAYS   => self::getCompletionPeriodMetricText(self::COMPLETION_PERIOD_METRIC_TYPE_DAYS),
        );
    }

    public static function getCompletionPeriodMetricText($value)
    {
        switch($value)
        {
            case self::COMPLETION_PERIOD_METRIC_TYPE_MONTHS:
                return trans('tenders.months');
            case self::COMPLETION_PERIOD_METRIC_TYPE_WEEKS:
                return trans('tenders.weeks');
            case self::COMPLETION_PERIOD_METRIC_TYPE_DAYS:
                return trans('tenders.days');
            default:
                throw new \Exception('Invalid value for Completion Period Metric.');
        }
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function procurementMethod()
    {
        return $this->belongsTo('PCK\ProcurementMethod\ProcurementMethod');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public function selectedContractors()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender_rot_information', 'tender_rot_information_id')
            ->with('contractor')
            ->orderBy('name', 'ASC')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * List all verifiers, past and present.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_rot_information_user', 'tender_rot_information_id');
    }

    /**
     * List current verifiers.
     *
     * @return mixed
     */
    public function verifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_rot_information_user', 'tender_rot_information_id')
            ->wherePivot('status', '=', self::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id');
    }

    public function currentBatchVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_rot_information_user', 'tender_rot_information_id')
            ->wherePivot('status', '!=', self::USER_VERIFICATION_REJECTED)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id');
    }

    /**
     * Returns the latest verifier that has not yet verified.
     *
     * @return mixed
     */
    public function latestVerifier()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_rot_information_user', 'tender_rot_information_id')
            ->wherePivot('status', '=', self::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id')
            ->limit(1);
    }

    public function verifierLogs()
    {
        return $this->morphMany('PCK\TenderFormVerifierLogs\TenderFormVerifierLog', 'loggable')
            ->orderBy('id', 'ASC');
    }

    public function latestVerifierLog()
    {
        return $this->morphMany('PCK\TenderFormVerifierLogs\TenderFormVerifierLog', 'loggable')
            ->orderBy('updated_at', 'DESC')
            ->limit(1);
    }

    public function tenderstages()
    {
        return $this->morphMany('PCK\ExpressionOfInterest\ExpressionOfInterestTokens', 'tenderstageable');
    }

    public function getCompletionPeriodAttribute($value)
    {
        return $value + 0;
    }

    public function getProposedDateOfCallingTenderAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getProposedDateOfClosingTenderAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getTechnicalTenderClosingDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getTargetDateOfSitePossessionAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function stillInProgress()
    {
        return $this->status === self::IN_PROGRESS;
    }

    public function isBeingValidated()
    {
        return $this->status === self::NEED_VALIDATION;
    }

    public function isSubmitted()
    {
        return $this->status === self::SUBMISSION;
    }

    /**
     * Delete related records.
     */
    protected function deleteRelatedModels()
    {
        $this->selectedContractors()->detach();

        $this->allVerifiers()->detach();

        ModelOperations::deleteWithTrigger(array(
            $this->verifierLogs,
        ));
    }

    public function syncApprovalForumUsers()
    {
        $object = $this;

        if( ! \PCK\Forum\ObjectThread::objectHasThread($object) ) return;

        $thread = \PCK\Forum\ObjectThread::getObjectThread($object);

        $userIds = $object->allVerifiers()->wherePivot('status', '=', self::USER_VERIFICATION_CONFIRMED)->get()->lists('id');

        if( $latestVerifier = $object->latestVerifier->first() ) $userIds[] = $latestVerifier->id;

        $userIds[] = $object->updated_by;

        $thread->syncThreadUsers($userIds);
    }

    public function rejectVerification()
    {
        \DB::table('tender_rot_information_user')
        ->where('tender_rot_information_id', '=', $this->id)
        ->whereIn('status', [self::USER_VERIFICATION_IN_PROGRESS, self::USER_VERIFICATION_CONFIRMED])
        ->update(array( 'status' => self::USER_VERIFICATION_REJECTED ));
        
        $this->load('verifiers');

        $this->status = self::IN_PROGRESS;

        if( \PCK\Forum\ObjectThread::objectHasThread($this) )
        {
            $thread = \PCK\Forum\ObjectThread::getObjectThread($this);
            $thread->users()->sync(array());
        }

        $this->save();
    }
}