<?php namespace PCK\WeatherRecords;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\ContractualClaim\ContractualClaimInterface;
use PCK\Helpers\ModelOperations;

class WeatherRecord extends Model implements StatusType, ContractualClaimInterface {

    use TimestampFormatterTrait, StatusTypeTrait, SoftDeletingTrait, ModuleAttachmentTrait;

    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($wr)
        {
            // delete attached attachments if available
            $wr->attachments()->delete();

            $wr->deleteRelatedModels();
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

    public function verifiedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'verified_by');
    }

    public function weatherRecordReports()
    {
        return $this->hasMany('PCK\WeatherRecordReports\WeatherRecordReport')->orderBy('id', 'asc');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
    }

    public function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->weatherRecordReports,
        ));
    }

}