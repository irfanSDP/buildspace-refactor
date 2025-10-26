<?php

use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorPreQualification\VendorGroupGrade;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Verifier\VerifierRepository;
use PCK\Verifier\Verifier;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use PCK\WeightedNode\WeightedNodeRepository;
use PCK\VendorManagement\InstructionSetting;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\TrackRecordProject\TrackRecordProject;

class VendorPreQualificationApprovalsController extends \BaseController {

    protected $verifierRepository;
    protected $weightedNodeRepository;

    public function __construct(VerifierRepository $verifierRepository, WeightedNodeRepository $weightedNodeRepository)
    {
        $this->verifierRepository = $verifierRepository;
        $this->weightedNodeRepository = $weightedNodeRepository;
    }

    public function index($vendorRegistrationId)
    {
        $user = \Confide::user();

        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $vendorPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $vendorRegistrationId)
            ->whereNotNull('weighted_node_id')
            ->get();

        $data = [];

        $grading = VendorGroupGrade::getGradeByGroup($vendorRegistration->company->contract_group_category_id);

        foreach($vendorPreQualifications as $vendorPreQualification)
        {
            $gradeLevel = null;

            if( $grading ) $gradeLevel = $grading->getGrade($vendorPreQualification->score);

            $trackRecord = TrackRecordProject::where('vendor_registration_id', $vendorRegistrationId)
            ->where('vendor_work_category_id', $vendorPreQualification->vendor_work_category_id)
            ->first();
            
            $data[] = [
                'id'                 => $vendorPreQualification->id,
                'company'            => $vendorRegistration->company->name,
                'form'               => $vendorPreQualification->weightedNode->name,
                'vendorCategory'     => ($trackRecord && $trackRecord->vendorCategory) ? $trackRecord->vendorCategory->name : null,
                'vendorWorkCategory' => $vendorPreQualification->vendorWorkCategory->name,
                'status'             => VendorPreQualification::getStatusText($vendorPreQualification->status_id),
                'score'              => $vendorPreQualification->score,
                'grade'              => $gradeLevel ? $gradeLevel->description : null,
                'remarks'            => $gradeLevel ? $gradeLevel->definition : null,
                'route:edit'         => route('vendorManagement.approval.vendorPreQualification.form', array($vendorRegistration->id, $vendorPreQualification->id, $vendorPreQualification->weightedNode->id)),
                'route:view'         => route('vendorManagement.approval.preQualification.approval', array($vendorRegistration->id, $vendorPreQualification->id)),
            ];
        }

        $editable = $vendorRegistration->status == VendorRegistration::STATUS_PROCESSING && VendorManagementUserPermission::hasPermission($user, VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION);

        $canUploadProcessorAttachments = ($vendorRegistration->isProcessing() && $vendorRegistration->processor && $vendorRegistration->processor->user_id == $user->id);

        $instructionSettings = InstructionSetting::first();

        return View::make('vendor_management.approval.pre_qualification.index', compact('data', 'vendorRegistration', 'editable', 'instructionSettings', 'canUploadProcessorAttachments'));
    }

    public function approval($vendorRegistrationId, $vendorPreQualificationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $vendorPreQualification = VendorPreQualification::find($vendorPreQualificationId);

        $form = $vendorPreQualification->weightedNode;

        $flatData = $this->weightedNodeRepository->getWeightedNodeFlatDataStructure($form);

        $data = [$this->weightedNodeRepository->getWeightedNodeTabulatorNestedSetDataStructure($form)];

        \PCK\Helpers\Hierarchy\AdjacencyListNode::traverse($data[0], function($node){
            if(isset($node['hasScores']) && $node['hasScores']) $node['route:getDownloads'] = route('preQualification.node.downloads', array($node['nodeId']));
            return $node;
        }, '_children');

        $editable = $vendorPreQualification->status_id == VendorPreQualification::STATUS_SUBMITTED && $vendorRegistration->status == VendorRegistration::STATUS_PROCESSING;

        $excludedIds = WeightedNode::where('root_id', '=', $form->id)->where('is_excluded', '=', true)->lists('id', 'id');

        $vpqScore = $vendorPreQualification->score ?? 0;

        return View::make('vendor_management.approval.pre_qualification.approval', compact('vendorRegistration', 'vendorPreQualification', 'form', 'data', 'flatData', 'editable', 'vpqScore', 'excludedIds'));
    }

    public function save($vendorRegistrationId, $vendorPreQualificationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $vendorPreQualification = VendorPreQualification::find($vendorPreQualificationId);

        WeightedNodeScore::updateRemarks(Input::get('score_remarks'));

        if(Input::get('submit') == 'reject')
        {
            $vendorPreQualification->status_id = VendorPreQualification::STATUS_REJECTED;
            $vendorPreQualification->save();
        }

        return Redirect::back();
    }
}