<?php namespace PCK\ContractorsCommitmentStatusLogs;

use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

class ContractorsCommitmentStatusLog extends Model {

    use TimestampFormatterTrait, PresentableTrait;

    protected $table = 'contractors_commitment_status_logs';

    protected $fillable = [
        'user_id',
        'status',
        'remarks',
    ];

    public function loggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

}