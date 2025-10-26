<?php namespace PCK\DigitalStar\Evaluation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\Helpers;

class DsEvaluation extends Model {

    const STATUS_DRAFT = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 4;

    protected $table = 'ds_evaluations';

    protected $fillable = ['ds_cycle_id', 'company_id', 'status_id', 'created_by', 'updated_by', 'deleted_by', 'start_date', 'end_date'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            $model->status_id = self::STATUS_DRAFT;
            $model->created_by = \Auth::id();
        });

        static::saving(function(self $model)
        {
            $model->updated_by = \Auth::id();
        });

        static::saved(function(self $model)
        {
            if ($model->isStatus(self::STATUS_DRAFT) && Carbon::parse($model->start_date)->isPast()) {
                $model->start();
            }

            if ($model->isStatus(self::STATUS_IN_PROGRESS) && Carbon::parse($model->end_date)->isPast()) {
                $model->end();
            }
        });

        static::deleted(function(self $model)
        {
            self::where('id', $model->id)->update(['deleted_by' => \Auth::id()]);

            foreach($model->forms as $companyForm)
            {
                $companyForm->delete();
            }
        });
    }

    public function cycle()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsCycle', 'ds_cycle_id');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function forms()
    {
        return $this->hasMany('PCK\DigitalStar\Evaluation\DsEvaluationForm', 'ds_evaluation_id');
    }

    public function companyForms()
    {
        return $this->forms()->whereNull('project_id')->get();
    }

    public function projectForms()
    {
        return $this->forms()->whereNotNull('project_id')->get();
    }

    public function projectForm($projectId)
    {
        return $this->forms()->where('project_id', $projectId)->first();
    }

    /*public function evaluators()
    {
        return $this->hasMany(VendorPerformanceEvaluator::class, 'vendor_performance_evaluation_id');
    }*/

    public static function getStatusText($status)
    {
        switch($status)
        {
            case self::STATUS_DRAFT:
                $text = trans('digitalStar/vendorManagement.draft');
                break;
            case self::STATUS_IN_PROGRESS:
                $text = trans('digitalStar/vendorManagement.inProgress');
                break;
            case self::STATUS_COMPLETED:
                $text = trans('digitalStar/vendorManagement.completed');
                break;
            default:
                throw new \Exception("Invalid type");
        }

        return $text;
    }

    public function isStatus($status)
    {
        return $this->status_id == $status;
    }

    public function previousScore($type, $cycleId, $companyId)
    {
        $colName = $type.'_score';

        $record = DsEvaluationScore::select($colName.' AS score')
            ->where('ds_cycle_id', '<', $cycleId)
            ->where('company_id', '=', $companyId)
            ->where($colName, '>', 0)
            ->orderBy('ds_cycle_id', 'desc')
            ->first();

        return $record ? $record->score : 0;
    }

    public function calculateScores()
    {
        $evaluationForms = DsEvaluationForm::where('ds_evaluation_id', '=', $this->id)
            ->where('status_id', '=', DsEvaluationForm::STATUS_COMPLETED)
            ->orderBy('project_id', 'asc')
            ->get();

        $data = [
            'company_score' => $this->previousScore('company', $this->ds_cycle_id, $this->company_id),
            'project_score' => $this->previousScore('project', $this->ds_cycle_id, $this->company_id),
        ];

        $projectScoreList = [];

        foreach ($evaluationForms as $form)
        {
            $score = $form->weightedNode->getScore();

            if (! is_null($form->project_id))
            {   // Project
                $projectScoreList[] = $score;
            } else {    // Company
                if ($score > 0) {
                    $data['company_score'] = $score;
                }
                $data['company_score_original'] = $score;
            }
        }

        if (! empty($projectScoreList))
        {
            $projectScore = Helpers::divide(array_sum($projectScoreList), count($projectScoreList));
            if ($projectScore > 0) {
                $data['project_score'] = $projectScore;
            }
            $data['project_score_original'] = $projectScore;
        }

        return $data;
    }

    public function generateScores()
    {
        $data = $this->calculateScores();

        $evaluationScore = DsEvaluationScore::firstOrNew(array(
            'ds_cycle_id' => $this->ds_cycle_id,
            'ds_evaluation_id' => $this->id,
            'company_id' => $this->company_id,
        ));
        
        $evaluationScore->fill($data);
        $evaluationScore->save();
    }

    public function start()
    {
        if( ! $this->isStatus(self::STATUS_DRAFT) ) return;

        $this->status_id = self::STATUS_IN_PROGRESS;

        $this->save();
    }

    public function generateFormsWhenInProgress()
    {
        if($this->isStatus(self::STATUS_IN_PROGRESS))
        {
            $this->generateForms();
        }
    }

    public function generateForms()
    {
        DsEvaluationForm::cloneFormsIfNone($this);
    }

    public function end()
    {
        if( ! $this->isStatus(self::STATUS_IN_PROGRESS) ) return;

        $this->generateScores();

        $this->status_id = self::STATUS_COMPLETED;

        $this->save();
    }

}