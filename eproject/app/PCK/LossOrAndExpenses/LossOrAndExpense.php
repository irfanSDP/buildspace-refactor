<?php namespace PCK\LossOrAndExpenses;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\ContractualClaim\ContractualClaimInterface;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;

class LossOrAndExpense extends Model implements StatusType, ContractualClaimInterface {

    use TimestampFormatterTrait, StatusTypeTrait, ModuleAttachmentTrait, ReminderRelations;

    protected $appends = array( 'deadline_to_submit_notice_to_claim' );

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

    public function architectInstruction()
    {
        return $this->belongsTo('PCK\ArchitectInstructions\ArchitectInstruction');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function attachedClauses()
    {
        return $this->morphMany('PCK\ClauseItems\AttachedClauseItem', 'attachable')->orderBy('priority', 'asc');
    }

    public function getCommencementDateOfEventAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function getDeadlineToSubmitNoticeToClaimAttribute()
    {
        return self::calculateDeadlineToSubmitNoticeToClaim($this->project, $this->commencement_date_of_event, $this->project->pam2006Detail->deadline_submitting_note_of_intention_claim_l_and_e);
    }

    public static function calculateDeadlineToSubmitNoticeToClaim(Project $project, $startDate, $claimDuration)
    {
        $calendarRepo = \App::make('PCK\Calendars\CalendarRepository');

        return $calendarRepo->calculateFinalDate($project, $startDate, $claimDuration);
    }

    /**
     * Deletes related records.
     */
    public function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->firstLevelMessages,
            $this->secondLevelMessages,
            $this->thirdLevelMessages,
            $this->fourthLevelMessages,
            $this->contractorConfirmDelay,
            $this->lossOrAndExpenseClaim,
            $this->lossOrAndExpenseInterimClaim,
        ));
    }

}