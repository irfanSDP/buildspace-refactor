<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\TemplateForm;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use PCK\Forms\WeightedNodeScoreForm;

class VendorPerformanceEvaluationTemplateFormScoresController extends \BaseController {

    protected $weightedNodeScoreForm;

    public function __construct(WeightedNodeScoreForm $weightedNodeScoreForm)
    {
        $this->weightedNodeScoreForm = $weightedNodeScoreForm;
    }

    public function index($templateFormId, $nodeId)
    {
        $node = WeightedNode::find($nodeId);

        $ancestors = [];

        foreach($node->ancestorsAndSelf()->orderBy('lft', 'asc')->get() as $ancestorNode)
        {
            $route = route('vendorPerformanceEvaluation.templateForm.nodes', array($templateFormId, $ancestorNode->id));

            if( $ancestorNode->children()->count() < 1 ) $route = route('vendorPerformanceEvaluation.templateForm.nodes', array($templateFormId, $ancestorNode->parent_id));

            $ancestors[] = [
                'name' => $ancestorNode->name,
                'route' => $route,
            ];
        }

        $templateForm = TemplateForm::find($templateFormId);

        $editable = $templateForm->isDraft();

        return View::make('vendor_performance_evaluation.scores.index', compact('templateForm', 'node', 'ancestors', 'data', 'editable'));
    }

    public function list($templateFormId, $nodeId)
    {
        $templateForm = TemplateForm::find($templateFormId);

        $node = WeightedNode::find($nodeId);

        $data = [];

        foreach($node->scores as $score)
        {
            $data[] = [
                'id'           => $score->id,
                'name'         => $score->name,
                'value'        => $score->value,
                'route:delete' => route('vendorPerformanceEvaluation.templateForm.node.scores.delete', array($templateFormId, $nodeId, $score->id)),
            ];
        }

        return Response::json($data);
    }

    public function storeOrUpdate($templateFormId, $nodeId)
    {
        $request = Request::instance();

        $this->weightedNodeScoreForm->validate($input = $request->all());

        if($this->weightedNodeScoreForm->success)
        {
            $score = WeightedNodeScore::find($input['score_id']);

            unset($input['score_id']);

            if($score)
            {
                $score->update($input);
            }
            else
            {
                $input['node_id'] = $nodeId;

                $score = WeightedNodeScore::create($input);
            }
        }

        return array(
            'success' => $this->weightedNodeScoreForm->success,
            'errors'  => $this->weightedNodeScoreForm->getErrorMessages(),
        );
    }

    public function destroy($templateFormId, $nodeId, $scoreId)
    {
        try
        {
            $score = WeightedNodeScore::find($scoreId);
            $score->delete();

            \Flash::success(trans('forms.deleted'));
        }
        catch(\Exception $e)
        {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::route('vendorPerformanceEvaluation.templateForm.node.scores', array($templateFormId, $nodeId));
    }

}