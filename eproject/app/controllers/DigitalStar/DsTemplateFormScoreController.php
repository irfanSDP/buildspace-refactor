<?php

namespace DigitalStar;

use PCK\DigitalStar\TemplateForm\DsTemplateForm;
use PCK\Forms\WeightedNodeScoreForm;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use Redirect;
use Request;
use Response;
use View;

class DsTemplateFormScoreController extends \BaseController
{
    protected $weightedNodeScoreForm;

    public function __construct(WeightedNodeScoreForm $weightedNodeScoreForm)
    {
        $this->weightedNodeScoreForm = $weightedNodeScoreForm;
    }

    public function index($templateFormId, $nodeId)
    {
        $node = WeightedNode::find($nodeId);

        $ancestors = [];

        foreach ($node->ancestorsAndSelf()->orderBy('lft', 'asc')->get() as $ancestorNode) {
            $route = route('digital-star.templateForm.nodes', array($templateFormId, $ancestorNode->id));

            if ($ancestorNode->children()->count() < 1) $route = route('digital-star.templateForm.nodes', array($templateFormId, $ancestorNode->parent_id));

            $ancestors[] = [
                'name' => $ancestorNode->name,
                'route' => $route,
            ];
        }

        $templateForm = DsTemplateForm::find($templateFormId);

        $editable = $templateForm->isDraft();

        return View::make('digital_star.scores.index', compact('templateForm', 'node', 'ancestors', 'editable'));
    }

    public function list($templateFormId, $nodeId)
    {
        //$templateForm = DsTemplateForm::find($templateFormId);

        $node = WeightedNode::find($nodeId);

        $data = [];

        foreach ($node->scores as $score) {
            $data[] = [
                'id' => $score->id,
                'name' => $score->name,
                'value' => $score->value,
                'route:delete' => route('digital-star.templateForm.node.scores.delete', array($templateFormId, $nodeId, $score->id)),
            ];
        }

        return Response::json($data);
    }

    public function storeOrUpdate($templateFormId, $nodeId)
    {
        $request = Request::instance();

        $this->weightedNodeScoreForm->validate($input = $request->all());

        if ($this->weightedNodeScoreForm->success) {
            $score = WeightedNodeScore::find($input['score_id']);

            unset($input['score_id']);

            if ($score) {
                $score->update($input);
            } else {
                $input['node_id'] = $nodeId;

                $score = WeightedNodeScore::create($input);
            }
        }

        return array(
            'success' => $this->weightedNodeScoreForm->success,
            'errors' => $this->weightedNodeScoreForm->getErrorMessages(),
        );
    }

    public function destroy($templateFormId, $nodeId, $scoreId)
    {
        try {
            $score = WeightedNodeScore::find($scoreId);
            $score->delete();

            \Flash::success(trans('forms.deleted'));
        } catch (\Exception $e) {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::route('digital-star.templateForm.node.scores', array($templateFormId, $nodeId));
    }

}