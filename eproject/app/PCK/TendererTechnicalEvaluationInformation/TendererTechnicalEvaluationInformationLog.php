<?php namespace PCK\TendererTechnicalEvaluationInformation;

use Illuminate\Database\Eloquent\Model;

class TendererTechnicalEvaluationInformationLog extends Model {

    protected $table = 'tenderer_technical_evaluation_information_log';

    public function info()
    {
        return $this->belongsTo('PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformation', 'information_id');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

}