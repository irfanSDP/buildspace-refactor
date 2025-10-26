<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;

class VendorPerformanceEvaluationProcessorEditDetail extends Model
{
    protected $table = 'vendor_performance_evaluation_processor_edit_details';

    public function processorEditLog()
    {
        return $this->belongsTo(VendorPerformanceEvaluationProcessorEditLog::class, 'vendor_performance_evaluation_processor_edit_log_id');
    }

    public function weightedNode()
    {
        return $this->belongsTo(WeightedNode::class, 'weighted_node_id');
    }

    public function previousWeightedNodeScore()
    {
        return $this->belongsTo(WeightedNodeScore::class, 'previous_score_id');
    }

    public function currentWeightedNodeScore()
    {
        return $this->belongsTo(WeightedNodeScore::class, 'current_score_id');
    }

    public static function createDetails(VendorPerformanceEvaluationProcessorEditLog $processorEditLog, array $editDetails)
    {
        foreach($editDetails as $detail)
        {
            $record = new self();

            $record->vendor_performance_evaluation_processor_edit_log_id = $processorEditLog->id;
            $record->weighted_node_id                                    = $detail['weighted_node_id'];
            $record->previous_score_id                                   = $detail['previous_score_id'];
            $record->is_previous_node_excluded                           = $detail['is_previous_node_excluded'];
            $record->current_score_id                                    = $detail['current_score_id'];
            $record->is_current_node_excluded                            = $detail['is_current_node_excluded'];

            $record->save();
        }
    }

    public static function getEditDetails(VendorPerformanceEvaluationProcessorEditLog $processorEditLog)
    {
        $records = self::where('vendor_performance_evaluation_processor_edit_log_id', $processorEditLog->id)->orderBy('id', 'ASC')->get();

        $details = [];

        foreach($records as $record)
        {
            array_push($details, [
                'id'                      => $record->id,
                'node_name'               => $record->weightedNode->name,
                'previous_score_id'       => $record->previous_score_id,
                'previous_score_name'     => $record->previousWeightedNodeScore ? $record->previousWeightedNodeScore->name : null,
                'previous_score'          => $record->previousWeightedNodeScore ? $record->previousWeightedNodeScore->value : null,
                'previous_score_excluded' => $record->is_previous_node_excluded,
                'current_score_id'        => $record->current_score_id,
                'current_score_name'      => $record->currentWeightedNodeScore ? $record->currentWeightedNodeScore->name : null,
                'current_score'           => $record->currentWeightedNodeScore ? $record->currentWeightedNodeScore->value : null,
                'current_score_excluded'  => $record->is_current_node_excluded,
            ]);
        }

        return $details;
    }
}