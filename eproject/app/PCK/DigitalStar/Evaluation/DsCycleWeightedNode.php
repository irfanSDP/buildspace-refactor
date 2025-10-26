<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;

class DsCycleWeightedNode extends Model {

    protected $table = 'ds_cycle_weighted_nodes';

    protected $fillable = ['ds_cycle_id', 'weighted_node_id', 'type'];

    public function cycle()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsCycle');
    }

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode');
    }
}