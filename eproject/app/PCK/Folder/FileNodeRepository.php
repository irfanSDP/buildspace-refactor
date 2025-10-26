<?php namespace PCK\Folder;

use Illuminate\Support\Facades\DB;

class FileNodeRepository {

    public function getTabulatorHierarchicalStructure($includedFileNodeIds, $nodeTypes, $ignoredNodeId)
    {
        $ignoredNodeTreeIds = [];

        $ignoredNode = FileNode::find($ignoredNodeId);

        if($ignoredNode)
        {
            $ignoredNodeTreeIds = FileNode::where('root_id', '=', $ignoredNode->root_id)
                ->where('lft', '>=', $ignoredNode->lft)
                ->where('rgt', '<=', $ignoredNode->rgt)
                ->lists('id');
        }

        $allAvailableFileNodes = FileNode::whereIn('type', $nodeTypes)
            ->whereIn('id', $includedFileNodeIds)
            ->whereNotIn('id', $ignoredNodeTreeIds)
            ->orderBy('lft')
            ->orderBy('priority')
            ->get()
            ->groupBy('root_id');

        $fileNodesByRoot = [];

        foreach($allAvailableFileNodes as $rootId => $availableFileNodesInRoot)
        {
            $fileNodesByRoot[$rootId] = [];

            foreach($availableFileNodesInRoot as $availableFileNode)
            {
                $fileNodesByRoot[$rootId][$availableFileNode['id']] = $availableFileNode;
            }
        }

        $topLevelFileNodeIds = [];

        $descendantFileNodeIds = [];

        foreach($fileNodesByRoot as $rootId => $fileNodesInCurrentRoot)
        {
            foreach($fileNodesInCurrentRoot as $fileNodeId => $fileNode)
            {
                if(in_array($fileNodeId, $descendantFileNodeIds)) continue;

                $topLevelFileNodeIds[] = $fileNodeId;

                foreach($fileNodesInCurrentRoot as $comparisonFileNodeId => $comparisonFileNode)
                {
                    if($comparisonFileNode->lft > $fileNode->lft && $comparisonFileNode->rgt < $fileNode->rgt)
                    {
                        $descendantFileNodeIds[] = $comparisonFileNodeId;
                    }
                }
            }
        }

        $allAvailableFileNodes = FileNode::select('file_nodes.id', 'file_nodes.root_id', 'topf.id as top_level_id', 'file_nodes.parent_id', 'file_nodes.name', 'file_nodes.description', 'file_nodes.lft', 'file_nodes.rgt', 'file_nodes.depth', 'file_nodes.type', 'file_nodes.upload_id')
            ->with('upload')
            ->join(\DB::raw('file_nodes AS topf'), function($join){
                $join->on('topf.root_id', '=', 'file_nodes.root_id');
                $join->on('topf.lft', '<=', 'file_nodes.lft');
                $join->on('topf.rgt', '>=', 'file_nodes.rgt');
            })
            ->whereIn('topf.id', $topLevelFileNodeIds)
            ->whereIn('file_nodes.id', $includedFileNodeIds)
            ->whereNotIn('file_nodes.id', $ignoredNodeTreeIds)
            ->whereIn('file_nodes.type', $nodeTypes)
            ->orderBy('file_nodes.root_id')
            ->orderBy('file_nodes.lft')
            ->get();

        $topLevelNodes = $allAvailableFileNodes->filter(function($item) use ($topLevelFileNodeIds) {
            return in_array($item->id, $topLevelFileNodeIds);
        });

        $allAvailableFileNodesByParentId = $allAvailableFileNodes->groupBy('parent_id');

        $output = [];

        foreach($topLevelNodes as $node)
        {
            $output[] = $this->process($allAvailableFileNodesByParentId, $node);
        }

        $globalSpacePlaceholder = [
            'id'          => 0,
            'name'        => trans('folders.drive'),
            'description' => '',
            'route:next'     => route('folders', array(0)),
            '_children'   => $output,
        ];

        return [$globalSpacePlaceholder];
    }

    protected function process($nodeCollectionByParentId, FileNode $node, $childField = '_children')
    {
        $record = [
            'id'             => $node->id,
            'name'           => $node->name,
            'description'    => $node->description,
            'type'           => $node->type,
            'route:next'     => route('folders', array($node->id)),
            'route:download' => $node->upload ? $node->upload->download_url : null,
        ];

        if(isset($nodeCollectionByParentId[$node->id]) && !empty($nodeCollectionByParentId[$node->id]))
        {
            $record[$childField] = [];

            foreach($nodeCollectionByParentId[$node->id] as $child)
            {
                $record[$childField][] = $this->process($nodeCollectionByParentId, $child);
            }
        }

        return $record;
    }
}