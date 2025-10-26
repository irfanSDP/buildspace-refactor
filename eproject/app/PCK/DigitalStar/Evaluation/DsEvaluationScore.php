<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;

class DsEvaluationScore extends Model {

    protected $table = 'ds_evaluation_scores';

    protected $fillable = [
        'ds_cycle_id',
        'ds_evaluation_id',
        'company_id',
        'company_score',
        'company_score_original',
        'project_score',
        'project_score_original',
    ];

    public function cycle() {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsCycle', 'ds_cycle_id');
    }

    public function evaluation()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsEvaluation', 'ds_evaluation_id');
    }

    public function company() {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }
}