<?php namespace PCK\Conversations;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;

class Conversation extends Model implements StatusType, PurposeOfIssueType {

    use TimestampFormatterTrait, StatusTypeTrait, PurposeOfIssueTypeTrait, ModuleAttachmentTrait;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
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

    public function contractGroup()
    {
        return $this->belongsTo('PCK\ContractGroups\ContractGroup', 'send_by_contract_group_id');
    }

    public function viewerGroups()
    {
        return $this->belongsToMany('PCK\ContractGroups\ContractGroup')->withPivot('read')->withTimestamps();
    }

    public function replyMessages()
    {
        return $this->hasMany('PCK\ConversationReplyMessages\ConversationReplyMessage')->orderBy('id', 'asc');
    }

    public function getDeadlineToReplyAttribute($value)
    {
        if( is_null($value) )
        {
            return $value;
        }

        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function isDraft()
    {
        return $this->getOriginal('status') == self::DRAFT;
    }

    public function hasAttachment()
    {
        return false;
    }

    /**
     * Delete related records.
     */
    protected function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->replyMessages,
        ));
    }

}