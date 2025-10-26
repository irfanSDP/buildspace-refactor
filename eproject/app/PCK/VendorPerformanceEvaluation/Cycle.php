<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PCK\Vendor\Vendor;
use PCK\Companies\Company;
use PCK\Base\Helpers;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class Cycle extends Model {

    protected $table = 'vendor_performance_evaluation_cycles';

    protected $fillable = ['start_date', 'end_date', 'remarks'];

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

    public function evaluations()
    {
        return $this->hasMany(VendorPerformanceEvaluation::class, 'vendor_performance_evaluation_cycle_id');
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo(VendorManagementGrade::class, 'vendor_management_grade_id');
    }

    public function generateScores()
    {
        $evaluationIds = $this->evaluations->lists('id');

        $vendorWorkCategoryIds = EvaluationScore::whereIn('vendor_performance_evaluation_id', $evaluationIds)->get()
            ->groupBy('company_id');

        foreach($vendorWorkCategoryIds as $companyId => $scores)
        {
            $scoresByCategory = [];

            foreach($scores as $score)
            {
                if(!array_key_exists($score->vendor_work_category_id, $scoresByCategory)) $scoresByCategory[$score->vendor_work_category_id] = [];

                $scoresByCategory[$score->vendor_work_category_id][] = $score->score;
            }

            foreach($scoresByCategory as $vendorWorkCategoryId => $scoreValues)
            {
                $cycleScore = CycleScore::firstOrNew(array(
                    'vendor_work_category_id'                => $vendorWorkCategoryId,
                    'company_id'                             => $companyId,
                    'vendor_performance_evaluation_cycle_id' => $this->id,
                ));

                $averageScore = Helpers::divide(array_sum($scoreValues), count($scoreValues));

                $cycleScore->score             = round($averageScore);
                $cycleScore->deliberated_score = round($averageScore);
                $cycleScore->save();
            }
        }
    }

    public static function getClonedVendorManagementGrade()
    {
        $settings = VendorPerformanceEvaluationModuleParameter::first();

        if(is_null($settings->vendorManagementGrade)) return null;

        return $settings->vendorManagementGrade->clone();
    }

    public function end()
    {
        if( $this->is_completed ) return;

        \Log::info("Completing Vendor Performance Evaluation Cycle [id:{$this->id}]");

        $this->is_completed = true;
        $this->vendor_management_grade_id = self::getClonedVendorManagementGrade()->id;
        $this->save();

        \Log::info("Generating scores for cycle [id:{$this->id}]");

        $this->generateScores();

        \Log::info("Processing companies for cycle [id:{$this->id}]");

        $this->processCompanies();

        \Log::info("Completed Vendor Performance Evaluation Cycle [id:{$this->id}]");

        \Queue::push('PCK\QueueJobs\GenerateVendorEvaluationForms', array(
            'cycle_id' => $this->id,
        ),'default');
    }

    public function processCompanies()
    {
        $cycleScores = CycleScore::where('vendor_performance_evaluation_cycle_id', '=', $this->id)->get();

        $passingScore = VendorPerformanceEvaluationModuleParameter::getValue('passing_score');

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
    }

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