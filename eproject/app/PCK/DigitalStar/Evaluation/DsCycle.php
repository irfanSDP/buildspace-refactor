<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use PCK\DigitalStar\ModuleParameters\DsModuleParameter;

class DsCycle extends Model {

    protected $table = 'ds_cycles';

    protected $fillable = ['start_date', 'end_date', 'is_completed', 'vendor_management_grade_id', 'remarks'];

    public function evaluations()
    {
        return $this->hasMany('PCK\DigitalStar\Evaluation\DsEvaluation', 'ds_cycle_id');
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public function cycleWeightedNodes()
    {
        return $this->hasMany('PCK\DigitalStar\Evaluation\DsCycleWeightedNode');
    }

    public function cycleTemplateForms()
    {
        return $this->hasMany('PCK\DigitalStar\Evaluation\DsCycleTemplateForm');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function(self $model)
        {
            foreach($model->evaluations as $evaluation)
            {
                if(Carbon::parse($model->start_date)->gt(Carbon::parse($evaluation->start_date)))
                {
                    $evaluation->start_date = $model->start_date;
                    $evaluation->save();
                }

                if(Carbon::parse($model->end_date)->lt(Carbon::parse($evaluation->end_date)))
                {
                    $evaluation->end_date = $model->end_date;
                    $evaluation->save();
                }
            }
        });

        static::saved(function(self $model)
        {
            if(!$model->is_complete && Carbon::parse($model->end_date)->isPast())
            {
                $model->end();
            }
        });
    }

    public static function latest()
    {
        return self::orderBy('id', 'DESC')->first();
    }

    public static function latestActive()
    {
        return self::where('is_completed', false)->orderBy('id', 'DESC')->first();
    }

    public static function latestCompleted()
    {
        return self::where('is_completed', true)->orderBy('id', 'DESC')->first();
    }

    public static function hasOngoingCycle()
    {
        return self::where('is_completed', '=', false)->count() > 0;
    }

    public function generateScores()
    {
        $evaluationScores = DsEvaluationScore::where('ds_cycle_id', '=', $this->id)->get();

        $vendorManagementGrade = $this->vendorManagementGrade;

        $cycleWeightedNodes = DsCycleWeightedNode::join('weighted_nodes', 'weighted_nodes.id', '=', 'ds_cycle_weighted_nodes.weighted_node_id')
            ->where('ds_cycle_weighted_nodes.ds_cycle_id', '=', $this->id)
            ->get();

        foreach ($evaluationScores as $evaluationScore)
        {
            $data = [
                'vendor_management_grade_level_id' => null,
                'company_score_weighted' => 0,
                'project_score_weighted' => 0,
            ];

            $cycleScore = DsCycleScore::firstOrCreate([
                'ds_cycle_id' => $this->id,
                'company_id' => $evaluationScore->company_id,
            ]);

            foreach ($cycleWeightedNodes as $cycleWeightedNode)
            {
                switch ($cycleWeightedNode->type)
                {
                    case 'company':
                        if ($evaluationScore->project_score > 0) {
                            $weight = $cycleWeightedNode->weight;
                        } else {
                            $weight = 100;
                        }
                        $weighted = $evaluationScore->company_score * ($weight/100);
                        $data['company_score_weighted'] = $weighted > 0 ? $weighted : 0;
                        break;

                    case 'project':
                        if ($evaluationScore->company_score > 0) {
                            $weight = $cycleWeightedNode->weight;
                        } else {
                            $weight = 100;
                        }
                        $weighted = $evaluationScore->project_score * ($weight/100);
                        $data['project_score_weighted'] = $weighted > 0 ? $weighted : 0;
                        break;
                }
            }
            $data['total_score'] = $data['company_score_weighted'] + $data['project_score_weighted'];

            $gradeLevel = $vendorManagementGrade->getGrade($data['total_score']);
            if ($gradeLevel) {
                $data['vendor_management_grade_level_id'] = $gradeLevel->id;
            }

            $cycleScore->fill($data);
            $cycleScore->save();

            // Update company's latest score
            DsCompanyScore::updateScore($cycleScore->company_id, $data['total_score']);
        }
    }

    public static function getClonedVendorManagementGrade()
    {
        $moduleParameter = DsModuleParameter::first();

        if (is_null($moduleParameter->vendorManagementGrade)) return null;

        return $moduleParameter->vendorManagementGrade->clone();
    }

    public function end()
    {
        if ( $this->is_completed ) return;

        \Log::info("Completing Digital Star Cycle [id:{$this->id}]");

        $vendorManagementGrade = self::getClonedVendorManagementGrade();
        if (! $vendorManagementGrade) {
            \Log::error("No vendor management grade found for cycle [id:{$this->id}]");
            return;
        }

        $this->vendor_management_grade_id = $vendorManagementGrade->id;
        $this->is_completed = true;
        $this->save();

        \Log::info("Generating scores for cycle [id:{$this->id}]");

        $this->generateScores();

        /*\Log::info("Processing companies for cycle [id:{$this->id}]");

        $this->processCompanies();*/

        \Log::info("Completed Digital Star Cycle [id:{$this->id}]");

        /*\Queue::push('PCK\QueueJobs\GenerateVendorEvaluationForms', array(
            'cycle_id' => $this->id,
        ),'default');*/
    }

    /*public function processCompanies()
    {
        $cycleScores = CycleScore::where('ds_cycle_id', '=', $this->id)->get();

        $passingScore = DsModuleParameter::getValue('passing_score');

        foreach($cycleScores as $cycleScore)
        {
            $vendor = Vendor::where('vendor_work_category_id', '=', $cycleScore->vendor_work_category_id)
                ->where('company_id', '=', $cycleScore->company_id)
                ->first();

            if(!$vendor) continue;

            $vendor->vendor_evaluation_cycle_score_id = $cycleScore->id;
            $vendor->save();

            if($vendor->type == Vendor::TYPE_ACTIVE && $cycleScore->score < $passingScore)
            {
                $vendor->moveToNominatedWatchList();
            }

            if($vendor->type == Vendor::TYPE_WATCH_LIST && $cycleScore->score >= $passingScore && Carbon::parse($vendor->watch_list_release_date)->isPast())
            {
                $vendor->moveToNominatedWatchList();
            }
        }
    }*/

    public static function getLatestCompletedCycle()
    {
        return self::where('is_completed', '=', true)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->start_date)->format(\Config::get('dates.months'));

        $end = Carbon::parse($this->end_date)->format(\Config::get('dates.months'));

        return trans('general.aToB', array('a' => $start, 'b' => $end));
    }
}