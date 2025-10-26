<?php namespace PCK\TechnicalEvaluationVerifierLogs;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

class TechnicalEvaluationVerifierLog extends Model {

    use PresentableTrait;

    protected $presenter = 'PCK\TenderFormVerifierLogs\TenderFormVerifierLogPresenter';

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

}