<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class DsEvaluationLog extends Model
{
    protected $table = 'ds_evaluation_logs';

    protected $fillable = [
        'ds_evaluation_form_id',
        'ds_action_type_id',
        'ds_role_id',
        'user_id',
        'company_id',
    ];

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

    public function actionType()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsActionType', 'ds_action_type_id');
    }

    public static function logAction($formId, $actionTypeSlug, $roleId, User $user)
    {
        $actionType = DsActionType::getBySlug($actionTypeSlug);
        $company = $user->company;

        $log                        = new self();
        $log->ds_evaluation_form_id = $formId;
        $log->ds_action_type_id     = $actionType ? $actionType->id : null;
        $log->ds_role_id            = $roleId;
        $log->user_id               = $user->id;
        $log->company_id            = $company ? $company->id : null;
        $log->save();

        return $log->id;
    }
}