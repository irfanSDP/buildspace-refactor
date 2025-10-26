<?php namespace PCK\WeightedNode;

use Illuminate\Support\Facades\DB;

class WeightedNodeRepository {

    public function getWeightedNodeTabulatorNestedSetDataStructure(WeightedNode $node, $childField = '_children')
    {
        $record = [
            'id'                 => "node-{$node->id}",
            'depth'              => $node->depth,
            'nodeId'             => $node->id,
            'description'        => $node->name,
            'type'               => 'node',
            'remarks'            => $node->remarks,
            'amendmentsRequired' => $node->amendments_required,
            'hasScores'          => false,
            'is_excluded'        => $node->is_excluded,
        ];

        if(!$node->children->isEmpty())
        {
            $record[$childField] = [];

            foreach($node->children as $child)
            {
                $record[$childField][] = $this->getWeightedNodeTabulatorNestedSetDataStructure($child);
            }
        }
        elseif(!$node->scores->isEmpty())
        {
            $record['hasScores'] = true;
            $record[$childField] = [];

            foreach($node->scores as $score)
            {
                $record[$childField][] = [
                    'id'                 => $score->id,
                    'nodeId'             => $node->id,
                    'description'        => $score->name,
                    'type'               => 'score',
                    'name'               => $node->id,
                    'selected'           => $score->is_selected,
                    'remarks'            => $score->remarks,
                    'score'              => $score->value,
                    'amendmentsRequired' => $score->amendments_required,
                ];
            }
        }

        return $record;
    }

    public function getWeightedNodeFlatDataStructure(WeightedNode $node)
    {
        $records = [];

        foreach($node->getDescendantsAndSelf()->sortBy('lft') as $node)
        {
            $records[] = [
                'id'                 => "node-{$node->id}",
                'nodeId'             => $node->id,
                'description'        => $node->name,
                'depth'              => $node->depth,
                'type'               => 'node',
                'remarks'            => $node->remarks,
                'amendmentsRequired' => $node->amendments_required,
                'is_excluded'        => $node->is_excluded,
                'hasScore'           => !$node->scores->isEmpty(),
            ];

            if($node->children()->count() < 1)
            {
                foreach($node->scores as $score)
                {
                    $records[] = [
                        'id'                 => $score->id,
                        'description'        => $score->name,
                        'nodeId'             => $score->node_id,
                        'depth'              => $node->depth+1,
                        'type'               => 'score',
                        'name'               => $node->id,
                        'selected'           => $score->is_selected,
                        'remarks'            => $score->remarks,
                        'score'              => $score->value,
                        'amendmentsRequired' => $score->amendments_required,
                    ];
                }
            }
        }

        return $records;
    }

    public function getNodesWithScores(WeightedNode $node)
    {
        $records = $this->getWeightedNodeFlatDataStructure($node);

        $nodes = [];

        foreach($records as $record)
        {
            if($record['type'] != 'node') continue;
            if(!$record['hasScore']) continue;

            $nodes[$record['nodeId']] = $record;
        }

        return $nodes;
    }

    public function getSelectedScoreNodes(WeightedNode $node)
    {
        $records = $this->getWeightedNodeFlatDataStructure($node);

        $nodes = [];

        foreach($records as $record)
        {
            if($record['type'] == 'node') continue;
            if(!$record['selected']) continue;

            $nodes[$record['nodeId']] = $record['id'];
        }

        return $nodes;
    }

    // gets unanswered node ids
    // if empty, all nodes are answered or marked as excluded
    public function getUnansweredNodeIds(WeightedNode $node)
    {
        $query = "WITH included_nodes AS (
                      SELECT DISTINCT wn.id
                      FROM weighted_nodes wn 
                      INNER JOIN weighted_node_scores wns ON wns.node_id = wn.id
                      WHERE wn.root_id = {$node->id}
                      AND wn.is_excluded IS FALSE 
                  ),
                  scored_nodes AS (
                      SELECT node_id
                      FROM weighted_node_scores
                      WHERE node_id IN (
                          SELECT id from included_nodes
                      )
                      AND is_selected IS TRUE
                      ORDER BY node_id ASC, id ASC
                  )
                  SELECT id FROM included_nodes
                  EXCEPT
                  SELECT node_id FROM scored_nodes
                  ORDER BY id ASC;";

        return array_column(DB::select(DB::raw($query)), 'id');
    }
}