<?php namespace PCK\SystemEvents;

use PCK\Users\User;
use PCK\Tenders\Tender;
use Illuminate\Mail\Mailer;

class OpenTenderEvents {

    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMailRequestingVerification(Tender $tender, User $user = null)
    {
        // will reload the relation to get the latest listing of selected verifiers
        $tender->load('openTenderVerifiers');

        $data['tenderName'] = $tender->current_tender_name;
        $data['toRoute']    = route('projects.openTender.accessToVerifierDecisionForm', array( $tender->project->id, $tender->id ));

        if( $user )
        {
            $this->prepareOpenTenderVerificationEmail($data, $user, $tender);
        }
        else
        {
            foreach($tender->openTenderVerifiers as $user)
            {
                $this->prepareOpenTenderVerificationEmail($data, $user, $tender);
            }
        }
    }

    private function prepareOpenTenderVerificationEmail(array $data, User $user, Tender $tender)
    {
        $recipientLocale = $user->settings->language->code;
        $subject = trans('email/openTender.openTenderVerificationRequest', [], 'messages', $recipientLocale);

        $data['recipientName']   = $user->name;
        $data['tender']          = [
            'id'                  => $tender->id,
            'current_tender_name' => $tender->current_tender_name
        ];
        $data['project']         = $tender->project;
        $data['workCategory']    = $tender->project->workCategory->name;
        $data['recipientLocale'] = $recipientLocale;

        $this->mailer->queue('open_tenders.email.open_tender_request_verification', $data, function($message) use ($user, $subject)
        {
            $message->to($user->email, $user->name);
            $message->subject($subject);
        });
    }

    public function sendMailRequestingTechnicalEvaluationVerification(Tender $tender, User $user = null)
    {
        $data['tenderName'] = $tender->current_tender_name;
        $data['toRoute']    = route('projects.technicalEvaluation.accessToVerifierDecisionForm', array( $tender->project->id, $tender->id ));

        if( $user )
        {
            $this->prepareTechnicalEvaluationVerificationEmail($data, $user, $tender);
        }
        else
        {
            // will reload the relation to get the latest listing of selected verifiers
            $tender->load('technicalEvaluationVerifiers');

            foreach($tender->technicalEvaluationVerifiers as $user)
            {
                $this->prepareTechnicalEvaluationVerificationEmail($data, $user, $tender);
            }
        }
    }

    private function prepareTechnicalEvaluationVerificationEmail(array $data, User $user, Tender $tender)
    {
        $recipientLocale         = $user->settings->language->code;
        $data['recipientName']   = $user->name;
        $data['tenderName']      = $tender->getCurrentTenderNameByLocale($recipientLocale);
        $data['project']         = $tender->project;
        $data['workCategory']    = $tender->project->workCategory->name;
        $data['recipientLocale'] = $recipientLocale;

        $this->mailer->queue('open_tenders.email.technical_evaluation_request_verification', $data, function($message) use ($user, $recipientLocale)
        {
            $message->to($user->email, $user->name);
            $message->subject(trans('email/openTender.technicalEvaluationVerificationRequest', [], 'messages', $recipientLocale));
        });
    }

}