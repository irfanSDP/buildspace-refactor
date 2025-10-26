<?php namespace PCK\EngineerInstructions;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\ContractualClaim\ContractualClaimInterface;

class EngineerInstruction extends Model implements StatusType, ContractualClaimInterface {

    use TimestampFormatterTrait, StatusTypeTrait, ModuleAttachmentTrait;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model)
        {
            // delete attached attachments if available
            $model->attachments()->delete();

            $model->architectInstructions()->detach();
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

    public function architectInstructions()
    {
        return $this->belongsToMany('PCK\ArchitectInstructions\ArchitectInstruction')->withTimestamps()->orderBy('id', 'desc');
    }

    public function getDeadlineToComplyWithAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

}