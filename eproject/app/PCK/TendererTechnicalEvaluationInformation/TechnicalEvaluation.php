<?php namespace PCK\TendererTechnicalEvaluationInformation;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Verifier\Verifiable;

class TechnicalEvaluation extends Model implements Verifiable {

    use ModuleAttachmentTrait;

    const TECHNICAL_OPENING_MODULE_NAME    = 'Technical Opening';
    const TECHNICAL_ASSESSMENT_MODULE_NAME = 'Technical Assessment';

    protected $table    = 'technical_evaluations';
    protected $fillable = [ 'submitted_by' ];

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function getOnApprovedView()
    {
        return 'technical_assessment.submitted';
    }

    public function getOnRejectedView()
    {
        return 'technical_assessment.rejected';
    }

    public function getOnPendingView()
    {
        return 'technical_assessment.pending_verification';
    }

    public function getRoute()
    {
        return route('technicalEvaluation.assessment.confirm', array( $this->tender->project->id, $this->tender->id ));
    }

    public function getViewData($locale)
    {
        return array(
            'senderName'          => \Confide::user()->name,
            'project_title'       => $this->tender->project->title,
            'toRoute'             => route('technicalEvaluation.assessment.confirm', array( $this->tender->project->id, $this->tender->id )),
            'current_tender_name'	=> $this->tender->getCurrentTenderNameByLocale($locale),
            'workCategory'        => $this->tender->project->workCategory->name,
            'recipientLocale'     => $locale,
        );
    }

    public function getOnApprovedNotifyList()
    {
        return $this->getProjectEditors();
    }

    public function getOnRejectedNotifyList()
    {
        return $this->getProjectEditors();
    }

    private function getProjectEditors()
    {
        $projectEditors = array();

        foreach($this->tender->project->getProjectEditors() as $editor)
        {
            array_push($projectEditors, $editor->user);
        }

        return $projectEditors;
    }

    public function resetSubmitter()
    {
        $this->update(array( 'submitted_by' => null ));
    }

    public function getOnApprovedFunction()
    {

    }

    public function getOnRejectedFunction()
    {

    }

    public function onReview()
    {

    }

    public function getSubmitterId()
    {
        return $this->submitted_by;
    }

    public function getModuleName()
    {
        return trans('modules.technicalEvaluation');
    }

    public function getEmailSubject($locale)
    {
        return trans('technicalEvaluation.technicalAssessmentNotification', [], 'messages', $locale);
    }
}