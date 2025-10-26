<?php namespace PCK\TendererTechnicalEvaluationInformation;

use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\ModelOperations;

class TendererTechnicalEvaluationInformation extends Model {

    protected $table = 'tenderer_technical_evaluation_information';
    protected $fillable = ['shortlisted'];

    protected static function boot()
    {
        parent::boot();

        // defaults remarks column to empty string
        static::creating(function(self $record) {
            $record->remarks = '';
        });

        static::updated(function(self $info)
        {
            $info->logThis();
        });

        static::deleting(function(self $info)
        {
            \DB::transaction(function() use ($info)
            {
                ModelOperations::deleteWithTrigger($info->log);
            });
        });
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function tenderer()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function log()
    {
        return $this->hasMany('PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformationLog', 'information_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Adds an entry to the log.
     *
     * @return bool
     */
    public function logThis()
    {
        $log                 = new TendererTechnicalEvaluationInformationLog;
        $log->information_id = $this->id;
        $log->user_id        = \Confide::user()->id;

        return $log->save();
    }

}