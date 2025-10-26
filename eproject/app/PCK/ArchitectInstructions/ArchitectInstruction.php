<?php namespace PCK\ArchitectInstructions;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractualClaim\ContractualClaimInterface;
use PCK\Helpers\ModelOperations;

class ArchitectInstruction extends Model implements StatusType, ContractualClaimInterface {

    use TimestampFormatterTrait, StatusTypeTrait, ModuleAttachmentTrait, ReminderRelations;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model)
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
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function attachedClauses()
    {
        return $this->morphMany('PCK\ClauseItems\AttachedClauseItem', 'attachable')->orderBy('priority', 'asc');
    }

    public function architectInstructionMessages()
    {
        return $this->hasMany('PCK\ArchitectInstructionMessages\ArchitectInstructionMessage');
    }

    public function getDeadlineToComplyAttribute($value)
    {
        if( is_null($value) )
        {
            return null;
        }

        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function haveClause()
    {
        if( $this->attachedClauses->isEmpty() )
        {
            return false;
        }

        return true;
    }

    public function haveDeadline()
    {
        if( ! empty( $this->attributes['deadline_to_comply'] ) )
        {
            return true;
        }

        return false;
    }

    /**
     * Delete related records.
     *
     * @throws \Exception
     */
    public function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->architectInstructionMessages,
            $this->messages,
            $this->thirdLevelMessages,
            $this->extensionOfTimes,
            $this->additionalExpenses,
            $this->lossOrAndExpenses,
            $this->latestExtensionOfTime,
            $this->latestAdditionalExpense,
            $this->latestLossOrAndExpense,
            $this->architectInstructionInterimClaim,
        ));

        $this->engineerInstructions()->detach();
    }

}