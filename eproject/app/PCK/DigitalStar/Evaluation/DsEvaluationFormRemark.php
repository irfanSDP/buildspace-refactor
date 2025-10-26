<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;

class DsEvaluationFormRemark extends Model {

    protected $table = 'ds_evaluation_form_remarks';

    protected $fillable = [
        'ds_evaluation_form_id',
        'ds_role_id',
        'user_id',
        'company_id',
        'action',
        'remarks',
    ];

    const ACTION_SUBMIT = 1;
    const ACTION_REJECT = 2;

    public function evaluationForm()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsEvaluationForm', 'ds_evaluation_form_id');
    }

    public function role()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsRole', 'ds_role_id');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }
}