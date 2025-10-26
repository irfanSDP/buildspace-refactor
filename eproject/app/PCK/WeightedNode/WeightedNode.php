<?php namespace PCK\WeightedNode;

use Baum\Node;
use PCK\Base\ModuleAttachmentTrait;

class WeightedNode extends Node {

    use ModuleAttachmentTrait;

    protected $table = 'weighted_nodes';

    protected $orderColumn = 'priority';

    // /**
    // * With Baum, all NestedSet-related fields are guarded from mass-assignment
    // * by default.
    // *
    // * @var array
    // */
    // protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

    protected $scoped = array('root_id');

    protected static function boot() {
        parent::boot();

        static::creating(function($node){
            if(is_null($node->name)) $node->name = '';
            if(is_null($node->priority)) $node->priority = $node->getNewPriority();
            if(is_null($node->root_id)) $node->root_id = -1;
        });

        static::created(function($node){
            if( $node->root_id == -1) 
            {
                $node->root_id = $node->id;
                $node->save();
            }
        });
    }

    public function scores()
    {
        return $this->hasMany('PCK\WeightedNode\WeightedNodeScore', 'node_id')->orderBy('value', 'desc')->orderBy('name');
    }

    public function makeChildOf($node)
    {
        parent::makeChildOf($node);

        $this->priority = $this->getNewPriority();

        $this->save();
    }

    public function getNewPriority()
    {
        if($this->id)
        {
            $maxPriorityValue = self::where('root_id', '=', $this->root_id)
                ->where('parent_id', '=', $this->parent_id)
                ->where('id', '!=', $this->id)
                ->max('priority');
        }
        else
        {
            $maxPriorityValue = self::where('root_id', '=', $this->root_id)
                ->where('parent_id', '=', $this->parent_id)
                ->max('priority');
        }

        return $maxPriorityValue+1;
    }

    public function createNew()
    {
        $record = new self([
            'name'     => $this->name,
            'weight'   => $this->weight,
            'root_id'  => null,
            'priority' => 1,
        ]);

        $record->save();

        return $record;
    }

    public function clone()
    {
        $clone = new self([
            'name'     => $this->name,
            'weight'   => $this->weight,
            'root_id'  => null,
            'priority' => 1,
        ]);

        $clone->save();

        $clone->copyChildren($this);

        return $clone;
    }

    public function copy(self $templateNode)
    {
        $this->name = $templateNode->name;

        foreach($this->getImmediateDescendants() as $childNode)
        {
            $childNode->delete();
        }

        $this->copyChildren($templateNode);

        return $this->save();
    }

    public function copyChildren(self $templateNode)
    {
        foreach($templateNode->getImmediateDescendants() as $templateChildNode)
        {
            $newNode = new self([
                'name'     => $templateChildNode->name,
                'weight'   => $templateChildNode->weight,
                'root_id'  => $this->root_id,
                'priority' => $templateChildNode->priority,
            ]);

            $newNode->save();

            $newNode->makeChildOf($this);

            $templateChildNode->copyAttachmentsTo($newNode);

            foreach($templateChildNode->scores as $score)
            {
                WeightedNodeScore::create(array(
                    'node_id'     => $newNode->id,
                    'name'        => $score->name,
                    'value'       => $score->value,
                    'is_selected' => $score->is_selected,
                ));
            }

            $newNode->copyChildren($templateChildNode);
        }
    }

    public function getRemarksAttribute($value)
    {
        if( is_null($value) ) return '';

        return $value;
    }

    public function descendentAmendmentRequired()
    {
        $hasRelevantDescendants = self::where('root_id', '=', $this->root_id)
            ->where('lft', '>', $this->lft)
            ->where('rgt', '<', $this->rgt)
            ->where('amendments_required', '=', true)
            ->count() > 0;

        if( $hasRelevantDescendants ) return true;

        $descendentIds = self::where('root_id', '=', $this->root_id)
            ->where('lft', '>=', $this->lft)
            ->where('rgt', '<=', $this->rgt)
            ->lists('id');

        return WeightedNodeScore::whereIn('node_id', $descendentIds)
            ->where('amendments_required', '=', true)
            ->count() > 0;
    }

    public static function updateRemarks($nodeRemarks)
    {
        if(empty($nodeRemarks)) return;

        $resolvedNodes = [];

        foreach($nodeRemarks as $id => $remarks)
        {
            if( ! empty(trim($remarks)) )
            {
                $node = WeightedNode::where('id', '=', $id)->update(array('amendments_required' => true, 'remarks' => trim($remarks)));
            }
            else
            {
                $resolvedNodes[] = $id;
            }
        }

        WeightedNode::whereIn('id', $resolvedNodes)->update(array('amendments_required' => false, 'remarks' => null));
    }

    public static function flushRemarks($nodeRemarks)
    {
        if(empty($nodeRemarks)) return;

        WeightedNode::whereIn('id', array_keys($nodeRemarks))->update(array('amendments_required' => false, 'remarks' => null));
    }

    public function calculateScore(array $selectedScoreIds, array &$excludedNodeIds = array())
    {
        $total = 0;

        if($this->children->isEmpty())
        {
            $selectedScore = $this->scores()->whereIn('id', $selectedScoreIds)->first();

            $maxScore = $this->scores->max('value');

            if( $maxScore <= 0 ) return 0;

            $value = 0;

            if( ! is_null( $selectedScore) )
            {
                $value = $selectedScore->value;
            }

            $total = $value / $maxScore * 100;

            return round($total, 0);
        }

        $childrenScores = [];

        $totalWeight = 0;

        foreach($this->children as $child)
        {
            if(in_array($child->id, $excludedNodeIds)) continue;

            $weight = $child->weight;

            $score = $child->calculateScore($selectedScoreIds, $excludedNodeIds);

            // If a node has children, and the children are all excluded, then we count the node as excluded as well, i.e. node.weight = 0
            if(!$child->children->isEmpty() && empty(array_diff($child->children->lists('id'), $excludedNodeIds)))
            {
                $excludedNodeIds[] = $child->id;
                continue;
            }

            $totalWeight += $weight;

            $childrenScores[] = array(
                'weight' => $weight,
                'score'  => $score,
            );
        }

        if( $totalWeight == 0 ) return 0;

        foreach($childrenScores as $childScore)
        {
            $total += $childScore['score'] * ($childScore['weight']/$totalWeight);
        }

        return (int)round($total, 0);
    }

    public function getScore()
    {
        $nodeIds = self::where('root_id', '=', $this->root_id)->lists('id');

        $selectedScoreIds = WeightedNodeScore::whereIn('node_id', $nodeIds)
            ->where('is_selected', '=', true)
            ->lists('id');

        $excludedNodeIds = self::where('root_id', '=', $this->root_id)
            ->where('is_excluded', '=', true)
            ->lists('id');

        $total = $this->calculateScore($selectedScoreIds, $excludedNodeIds);

        return (int)round($total, 0);
    }
}