<?php namespace PCK\WeightedNode;

use Illuminate\Database\Eloquent\Model;

class WeightedNodeScore extends Model {
	protected $fillable = ['node_id', 'name', 'value', 'amendments_required', 'remarks', 'is_selected'];

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode', 'node_id');
    }

    public static function select($scoreId)
    {
        $score = WeightedNodeScore::find($scoreId);

        WeightedNodeScore::where('node_id', '=', $score->node_id)
            ->update(['is_selected' => false]);

        WeightedNodeScore::where('id', '=', $score->id)
            ->update(['is_selected' => true]);
    }

    public function getRemarksAttribute($value)
    {
        if( is_null($value) ) return '';

        return $value;
    }

    public static function updateRemarks($scoreRemarks)
    {
        if(empty($scoreRemarks)) return;

        $resolvedScores = [];

        foreach($scoreRemarks as $id => $remarks)
        {
            if( ! empty(trim($remarks)) )
            {
                $node = WeightedNodeScore::where('id', '=', $id)->update(array('amendments_required' => true, 'remarks' => trim($remarks)));
            }
            else
            {
                $resolvedScores[] = $id;
            }
        }

        WeightedNodeScore::whereIn('id', $resolvedScores)->update(array('amendments_required' => false, 'remarks' => null));
    }

    public static function flushRemarks($scoreRemarks)
    {
        if(empty($scoreRemarks)) return;

        WeightedNodeScore::whereIn('id', array_keys($scoreRemarks))->update(array('amendments_required' => false, 'remarks' => null));
    }

    public static function getNodeIdsBySelectedScoreIds($rootNodeId)
    {
        return self::whereHas('weightedNode', function($q) use ($rootNodeId) {
            $q->where('root_id', '=', $rootNodeId);
        })->where('is_selected', '=', true)
        ->lists('node_id', 'id');
    }
}