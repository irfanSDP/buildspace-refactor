<?php

namespace DigitalStar;

use PCK\Base\Helpers;
use PCK\DigitalStar\TemplateForm\DsTemplateForm;
use PCK\Forms\WeightedNodeForm;
use PCK\WeightedNode\WeightedNode;
use Redirect;
use Request;
use Response;
use View;

class DsTemplateFormNodeController extends \BaseController
{
    protected $weightedNodeForm;

    public function __construct(WeightedNodeForm $weightedNodeForm)
    {
        $this->weightedNodeForm = $weightedNodeForm;
    }

    public function index($templateFormId, $parentNodeId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $parentNode = WeightedNode::find($parentNodeId);

        $ancestors = [];

        foreach ($parentNode->ancestorsAndSelf()->orderBy('lft', 'asc')->get() as $ancestorNode) {
            $ancestors[] = [
                'name' => $ancestorNode->name,
                'route' => route('digital-star.templateForm.nodes', array($templateFormId, $ancestorNode->id)),
            ];
        }

        return View::make('digital_star.nodes.index', compact('templateForm', 'parentNode', 'ancestors'));
    }

    public function list($templateFormId, $parentNodeId)
    {
        $templateForm = DsTemplateForm::find($templateFormId);

        $data = [];

        $parentNode = WeightedNode::find($parentNodeId);

        $totalWeight = $parentNode->children()->sum('weight');

        $isDraft = $templateForm->isDraft();

        foreach ($parentNode->children->sortBy('name')->sortByDesc('weight') as $child) {
            $data[] = [
                'id' => $child->id,
                'name' => $child->name,
                'weight' => $child->weight,
                'percentage' => round(Helpers::divide($child->weight, $totalWeight) * 100, 2),
                'route:next' => route('digital-star.templateForm.nodes', array($templateFormId, $child->id)),
                'can_go_next' => $child->scores->isEmpty(),
                'route:scores' => route('digital-star.templateForm.node.scores', array($templateFormId, $child->id)),
                'can_add_score' => $child->children->isEmpty(),
                'can_edit' => $isDraft,
                'deletable' => $isDraft,
                'route:delete' => route('digital-star.templateForm.delete', array($templateFormId, $parentNodeId, $child->id)),
            ];
        }

        return Response::json($data);
    }

    public function storeOrUpdate($templateFormId, $parentNodeId)
    {
        $request = Request::instance();

        $this->weightedNodeForm->validate($input = $request->all());

        if ($this->weightedNodeForm->success) {
            if ($input['node_id'] == -1) {
                $parentNode = WeightedNode::find($parentNodeId);

                unset($input['node_id']);

                $input['root_id'] = $parentNode->root_id;
                $input['parent_id'] = $parentNode->id;

                $newNode = WeightedNode::create($input);

                $newNode->makeChildOf($parentNode);
            } else {
                $node = WeightedNode::find($input['node_id']);

                unset($input['node_id']);

                $node->update($input);
            }
        }

        return array(
            'success' => $this->weightedNodeForm->success,
            'errors' => $this->weightedNodeForm->getErrorMessages(),
        );
    }

    public function destroy($templateFormId, $parentNodeId, $nodeId)
    {
        try {
            $node = WeightedNode::find($nodeId);
            $node->delete();

            \Flash::success(trans('forms.deleted'));
        } catch (\Exception $e) {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::route('digital-star.templateForm.nodes', array($templateFormId, $parentNodeId));
    }
}
