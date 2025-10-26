<?php

use Carbon\Carbon;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\VendorManagement\InstructionSetting;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ObjectLog\ObjectLog;

class VendorsVendorPreQualificationController extends \BaseController {

    protected $weightedNodeRepository;

    public function __construct(WeightedNodeRepository $weightedNodeRepository)
    {
        $this->weightedNodeRepository = $weightedNodeRepository;
    }

    public function index()
    {
        $user = \Confide::user();

        $relevantWorkCategoryIds = TrackRecordProject::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)->lists('vendor_work_category_id');

        $vendorPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)
                                                            ->whereIn('vendor_work_category_id', $relevantWorkCategoryIds)
                                                            ->get();

        $data = [];

        foreach($vendorPreQualifications as $preQualification)
        {
            $form = $preQualification->weightedNode;
        
            if( is_null($form) ) continue;
        
            $data[] = [
                'id'                 => $form->id,
                'name'               => $form->name,
                'vendorCategory'     => TrackRecordProject::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)
                                                            ->where('vendor_work_category_id', '=', $preQualification->vendor_work_category_id)
                                                            ->first()
                                                            ->vendorCategory->name,
                'vendorWorkCategory' => $preQualification->vendorWorkCategory->name,
                'route:view'         => route('vendors.vendorPreQualification.form', $form->id),
            ];
        }
        
        $data[] = ['name' => ''];

        $instructionSettings = InstructionSetting::first();

        return View::make('vendor_pre_qualification.process.index', compact('data', 'instructionSettings'));
    }

    public function form($formId)
    {
        $form = WeightedNode::find($formId);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        $allNodeIds = WeightedNode::where('root_id', '=', $form->id)->lists('id');

        $nodeIdsBySelectedScoreIds = WeightedNodeScore::getNodeIdsBySelectedScoreIds($form->id);

        $preQualification = VendorPreQualification::where('weighted_node_id', '=', $form->id)
            ->first();

        $readOnly = $preQualification->status_id != VendorPreQualification::STATUS_DRAFT;

        \PCK\Helpers\Hierarchy\AdjacencyListNode::traverse($data[0], function($node) use ($readOnly) {
            if(isset($node['hasScores']) && $node['hasScores'])
            {
                if($readOnly)
                {
                    $node['route:getDownloads'] = route('preQualification.node.downloads', array($node['nodeId']));
                }
                else
                {
                    $node['route:getUploads'] = route('preQualification.node.uploads', array($node['nodeId']));
                    $node['route:doUpload']   = route('preQualification.node.doUpload', array($node['nodeId']));
                }
            }
            return $node;
        }, '_children');

        $excludedIds = WeightedNode::where('root_id', '=', $form->id)->where('is_excluded', '=', true)->lists('id', 'id');

        return View::make('vendor_pre_qualification.process.form', compact('form', 'data', 'readOnly', 'nodeIdsBySelectedScoreIds', 'excludedIds'));
    }

    public function processorForm($vendorRegistrationId, $vendorPreQualificationId, $formId)
    {
        $vendorRegistration     = VendorRegistration::find($vendorRegistrationId);
        $vendorPreQualification = VendorPreQualification::find($vendorPreQualificationId);
        $form                   = WeightedNode::find($formId);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        $allNodeIds = WeightedNode::where('root_id', '=', $form->id)->lists('id');

        $nodeIdsBySelectedScoreIds = WeightedNodeScore::getNodeIdsBySelectedScoreIds($form->id);

        $preQualification = VendorPreQualification::where('weighted_node_id', '=', $form->id)->first();

        $readOnly = (!$vendorRegistration->isProcessing()) || ($vendorPreQualification->status_id != VendorPreQualification::STATUS_SUBMITTED) || ($vendorRegistration->processor && $vendorRegistration->processor->user_id != \Confide::user()->id);

        \PCK\Helpers\Hierarchy\AdjacencyListNode::traverse($data[0], function($node) use ($readOnly) {
            if(isset($node['hasScores']) && $node['hasScores'])
            {
                if($readOnly)
                {
                    $node['route:getDownloads'] = route('preQualification.node.downloads', array($node['nodeId']));
                }
                else
                {
                    $node['route:getUploads'] = route('preQualification.node.uploads', array($node['nodeId']));
                    $node['route:doUpload']   = route('preQualification.node.doUpload', array($node['nodeId']));
                }
            }
            return $node;
        }, '_children');

        $excludedIds = WeightedNode::where('root_id', '=', $form->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $vpqScore = $vendorPreQualification->score ?? 0;

        return View::make('vendor_management.approval.pre_qualification.form', compact('form', 'data', 'readOnly', 'nodeIdsBySelectedScoreIds', 'vendorPreQualification', 'vendorRegistration', 'excludedIds', 'vpqScore'));
    }

    public function formUpdate($formId)
    {
        $input = Input::all();

        $form = WeightedNode::find($formId);

        $formSubmitType = $input['submit_type'];

        WeightedNode::where('root_id', '=', $form->id)
            ->whereIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => true]);

        WeightedNode::where('root_id', '=', $form->id)
            ->whereNotIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => false]);

        unset($input['_token'], $input['submit_type'], $input['excluded_ids']);

        foreach($input as $nodeId => $scoreId)
        {
            $node = WeightedNode::find($nodeId);

            if($node->root_id != $form->id) continue;

            $score = WeightedNodeScore::find($scoreId);

            if($score->node_id != $node->id) continue;

            WeightedNodeScore::select($score->id);
        }

        $vendorPreQualification = VendorPreQualification::where('weighted_node_id', '=', $form->id)->first();

        $vendorPreQualification->score = $form->getScore();

        $vendorPreQualification->save();

        \Flash::success(trans('forms.savedFormX', ['name' => $form->name]));

        return Redirect::route('vendors.vendorPreQualification.index');
    }

    public function processorFormUpdate($vendorRegistrationId, $vendorPreQualificationId, $formId)
    {
        $input = Input::all();

        $vendorRegistration     = VendorRegistration::find($vendorRegistrationId);
        $vendorPreQualification = VendorPreQualification::find($vendorPreQualificationId);
        $form                   = WeightedNode::find($formId);

        $formSubmitType = $input['submit_type'];

        WeightedNode::where('root_id', '=', $form->id)
            ->whereIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => true]);

        WeightedNode::where('root_id', '=', $form->id)
            ->whereNotIn('id', $input['excluded_ids'] ?? [])
            ->update(['is_excluded' => false]);

        unset($input['_token'], $input['submit_type'], $input['excluded_ids']);

        foreach($input as $nodeId => $scoreId)
        {
            $node = WeightedNode::find($nodeId);

            if($node->root_id != $form->id) continue;

            $score = WeightedNodeScore::find($scoreId);

            if($score->node_id != $node->id) continue;

            WeightedNodeScore::select($score->id);
        }

        $vendorPreQualification->score = $form->getScore();

        $vendorPreQualification->save();

        ObjectLog::recordAction($vendorRegistration, ObjectLog::ACTION_EDIT, ObjectLog::MODULE_VENDOR_REGISTRATION_VENDOR_PREQUALIFICATION);

        \Flash::success(trans('forms.savedFormX', ['name' => $form->name]));

        return Redirect::route('vendorManagement.approval.preQualification', [$vendorRegistration->id]);
    }

    public function getDownloads($nodeId)
    {
        $data = array();

        $node = WeightedNode::find($nodeId);

        foreach($node->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'    => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => Carbon::parse($upload->created_at)->format(\Config::get('dates.created_at')),
            );
        }

        return $data;
    }

    public function getUploads($nodeId)
    {
        $input = Input::all();

        $node = WeightedNode::find($nodeId);

        $uploadedFiles = $this->getAttachmentDetails($node);

        $data = array();

        foreach($uploadedFiles as $file)
        {
            $file['imgSrc']      = $file->generateThumbnailURL();
            $file['deleteRoute'] = $file->generateGeneralDeleteURL();
            $file['createdAt']   = Carbon::parse($file->created_at)->format(\Config::get('dates.created_at'));
            $file['size']        = \PCK\Base\Helpers::formatBytes($file->size);

            $data[] = $file;
        }

        return $data;
    }

    public function doUpload($nodeId)
    {
        $input = Input::all();

        $node = WeightedNode::find($nodeId);

        \PCK\Helpers\ModuleAttachment::saveAttachments($node, $input);

        return array(
            'success' => true,
        );
    }

    public function getActionLogs($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $actionLogs = ObjectLog::getActionLogs($vendorRegistration, ObjectLog::MODULE_VENDOR_REGISTRATION_VENDOR_PREQUALIFICATION);
        
        return Response::json($actionLogs);
    }

    public function getLiveVpqScore($vendorRegistrationId, $vendorPreQualificationId)
    {
        $inputs = Input::all();

        $vendorRegistration     = VendorRegistration::find($vendorRegistrationId);
        $vendorPreQualification = VendorPreQualification::find($vendorPreQualificationId);

        $selectedScoreIds = isset($inputs['selectedScoreIds']) ? array_values($inputs['selectedScoreIds']) : [];
        $exludedScoreIds  = isset($inputs['excludedScoreIds']) ? array_keys($inputs['excludedScoreIds']) : [];

        $vpqScore = $vendorPreQualification->weightedNode->calculateScore($selectedScoreIds, $exludedScoreIds);

        return Response::json(['vpqScore' => $vpqScore]);
    }
}