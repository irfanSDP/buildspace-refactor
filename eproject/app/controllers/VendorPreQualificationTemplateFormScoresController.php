<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\TemplateForm;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use PCK\Forms\WeightedNodeScoreForm;

class VendorPreQualificationTemplateFormScoresController extends \BaseController {

    protected $weightedNodeScoreForm;

    public function __construct(WeightedNodeScoreForm $weightedNodeScoreForm)
    {
        $this->weightedNodeScoreForm = $weightedNodeScoreForm;
    }

    public function index($vendorGroupId, $vendorWorkCategoryId, $nodeId)
    {
        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        $node = WeightedNode::find($nodeId);

        $ancestors = [];

        foreach($node->ancestorsAndSelf()->orderBy('lft', 'asc')->get() as $ancestorNode)
        {
            $route = route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $ancestorNode->id));

            if( $ancestorNode->children()->count() < 1 ) $route = route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $vendorWorkCategoryId, $ancestorNode->parent_id));

            $ancestors[] = [
                'name' => $ancestorNode->name,
                'route' => $route,
            ];
        }

        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        $vendorWorkCategory = VendorWorkCategory::find($vendorWorkCategoryId);

        $editable = $templateForm->isDraft();

        return View::make('vendor_pre_qualification.scores.index', compact('vendorGroup', 'vendorWorkCategory', 'templateForm', 'node', 'ancestors', 'editable'));
    }

    public function list($vendorGroupId, $vendorWorkCategoryId, $nodeId)
    {
        $templateForm = TemplateForm::getCurrentEditingForm($vendorWorkCategoryId);

        $node = WeightedNode::find($nodeId);

        $data = [];

        foreach($node->scores as $score)
        {
            $data[] = [
                'id'                  => $score->id,
                'name'                => $score->name,
                'value'               => $score->value,
                'route:delete'        => route('vendorPreQualification.formLibrary.form.node.scores.delete', array($vendorGroupId, $vendorWorkCategoryId, $nodeId, $score->id)),
                'amendments_required' => $score->amendments_required,
                'remarks'             => $score->remarks,
            ];
        }

        return Response::json($data);
    }

    public function storeOrUpdate($vendorGroupId, $vendorWorkCategoryId, $nodeId)
    {
        $request = Request::instance();

        $this->weightedNodeScoreForm->validate($input = $request->all());

        if($this->weightedNodeScoreForm->success)
        {
            $score = WeightedNodeScore::find($input['score_id']);

            unset($input['score_id']);

            if($score)
            {
                $input['amendments_required'] = false;

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

    public function destroy($vendorGroupId, $vendorWorkCategoryId, $nodeId, $scoreId)
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

        return Redirect::route('vendorPreQualification.formLibrary.form.node.scores', array($vendorGroupId, $vendorWorkCategoryId, $nodeId));
    }

}