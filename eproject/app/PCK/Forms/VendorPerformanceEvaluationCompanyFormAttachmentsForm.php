<?php namespace PCK\Forms;

use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\WeightedNode\WeightedNodeRepository;

class VendorPerformanceEvaluationCompanyFormAttachmentsForm extends CustomFormValidator {
    protected $companyForm;

    public function setCompanyForm(VendorPerformanceEvaluationCompanyForm $companyForm)
    {
        $this->companyForm = $companyForm;
    }

    public function isAttachmentsRequired()
    {
        $settings = VendorPerformanceEvaluationModuleParameter::first();

        if( ! $settings->attachments_required ) return false;

        return $this->companyForm->weightedNode->getScore() <= $settings->attachments_required_score_threshold;
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        if( $this->isAttachmentsRequired() && $this->companyForm->attachments->isEmpty())
        {
            $messageBag->add('uploaded_files', trans('forms.attachmentsRequired'));
        }

        $weightedNodeRepository = \App::make(WeightedNodeRepository::class);

        if( count($weightedNodeRepository->getUnansweredNodeIds($this->companyForm->weightedNode)) > 0 )
        {
            $messageBag->add('evaluation_form_questions', trans('forms.allMustBeAnswered'));
        }

        return $messageBag;
    }
}