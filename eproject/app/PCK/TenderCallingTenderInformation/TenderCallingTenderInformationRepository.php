<?php namespace PCK\TenderCallingTenderInformation;

use PCK\Tenders\TenderStages;
use PCK\Users\User;
use PCK\Helpers\Key;
use PCK\Tenders\Tender;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\TenderFormVerifierLogs\TenderFormVerifierLog;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\CompanyTenderCallingTenderInformation\CompanyTenderCallingTenderInformation;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use Illuminate\Support\Facades\DB;
use PCK\Helpers\Mailer;
use PCK\Projects\Project;
use Carbon\Carbon;
use PCK\Companies\Company;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;

class TenderCallingTenderInformationRepository extends BaseModuleRepository {

    protected $events;
    protected $emailNotifier;
    protected $systemNotifier;

    public function __construct(Dispatcher $events, EmailNotifier $emailNotifier, SystemNotifier $systemNotifier)
    {
        $this->events = $events;
        $this->emailNotifier = $emailNotifier;
        $this->systemNotifier = $systemNotifier;
    }

    public function cloneInformationToCallingTender(TenderListOfTendererInformation $lotInformation)
    {
        $companyIds = array();
        $user       = \Confide::user();

        $tender = $lotInformation->tender;

        // create a record for TenderCallingTenderInformation
        $callingTenderObject = $tender->callingTenderInformation ?: new TenderCallingTenderInformation();

        $callingTenderObject->date_of_calling_tender                         = $lotInformation->date_of_calling_tender;
        $callingTenderObject->date_of_closing_tender                         = $lotInformation->date_of_closing_tender;
        $callingTenderObject->technical_tender_closing_date                  = $lotInformation->technical_tender_closing_date;
        $callingTenderObject->disable_tender_rates_submission                = $lotInformation->disable_tender_rates_submission;
        $callingTenderObject->allow_contractor_propose_own_completion_period = $lotInformation->allow_contractor_propose_own_completion_period;
        $callingTenderObject->created_by                                     = $user->id;
        $callingTenderObject->updated_by                                     = $user->id;

        $callingTenderObject = $tender->callingTenderInformation()->save($callingTenderObject);

        $lotInformation->load('selectedContractors');

        // get selected contractor with status of OK
        foreach($lotInformation->selectedContractors as $contractor)
        {
            $contractorDeleteStatus = $contractor->pivot->deleted_at;

            if( $contractorDeleteStatus ) continue;

            $isContractorCommitmentStatusYes = ($contractor->pivot->status === ContractorCommitmentStatus::OK);

            if( !$isContractorCommitmentStatusYes ) continue;

            $companyIds[] = $contractor->id;
        }

        $callingTenderObject->selectedContractors()->sync($companyIds);
    }

    public function saveCallingTenderInformation(Tender $tender, array $inputs)
    {
        $logStatus = false;
        $user      = \Confide::user();

        $sentToVerify        = isset( $inputs['send_to_verify'] );
        $verificationReject  = isset( $inputs['verification_reject'] );
        $verificationConfirm = isset( $inputs['verification_confirm'] );
        $datesExtension      = isset( $inputs['dates_extension'] );
        $verifierRemark      = $inputs['verifier_remark'] ?? null;

        $object = $tender->callingTenderInformation;

        if( ( $tender->callingTenderInformation && $object->stillInProgress() ) OR $sentToVerify )
        {
            $this->syncSelectedVerifiers($object, $inputs);

            $object->date_of_calling_tender                         = $inputs['date_of_calling_tender'];
            $object->date_of_closing_tender                         = $inputs['date_of_closing_tender'];
            $object->technical_tender_closing_date                  = $inputs['technical_tender_closing_date'] ?? $inputs['date_of_closing_tender'];
            $object->disable_tender_rates_submission                = $inputs['disable_tender_rates_submission'] ?? false;
            $object->updated_by                                     = $user->id;
            $object->allow_contractor_propose_own_completion_period = false;

            if( isset( $inputs['allow_contractor_propose_own_completion_period'] ) AND $inputs['allow_contractor_propose_own_completion_period'] )
            {
                $object->allow_contractor_propose_own_completion_period = $inputs['allow_contractor_propose_own_completion_period'];
            }
        }

        if( $datesExtension )
        {
            $this->syncSelectedVerifiers($object, $inputs);

            $object->date_of_calling_tender                         = $inputs['date_of_calling_tender'];
            $object->date_of_closing_tender                         = $inputs['date_of_closing_tender'];
            $object->technical_tender_closing_date                  = $inputs['technical_tender_closing_date'] ?? $inputs['date_of_closing_tender'];
            $object->disable_tender_rates_submission                = $inputs['disable_tender_rates_submission'] ?? false;
            $object->status                                         = TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_IN_PROGRESS;
            $object->updated_by                                     = $user->id;
            $object->allow_contractor_propose_own_completion_period = false;

            if( isset( $inputs['allow_contractor_propose_own_completion_period'] ) AND $inputs['allow_contractor_propose_own_completion_period'] )
            {
                $object->allow_contractor_propose_own_completion_period = $inputs['allow_contractor_propose_own_completion_period'];
            }

            $logStatus = TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_IN_PROGRESS;
        }

        $hasVerifiers = ! $object->verifiers->isEmpty();

        if( $sentToVerify )
        {
            $object->status     = TenderCallingTenderInformation::NEED_VALIDATION;
            $object->updated_by = $user->id;

            $logStatus = TenderCallingTenderInformation::NEED_VALIDATION;
        }

        if( $verificationReject )
        {
            $object->rejectVerification();
            $logStatus = TenderCallingTenderInformation::USER_VERIFICATION_REJECTED;
        }

        if( $verificationConfirm )
        {
            $this->setVerificationConfirmToCurrentVerifier($object);

            if( $object->verifiers->isEmpty() )
            {
                // Set different type of status based on the current form's operation.
                // Normal form or extend deadline form.
                if( $object->extendingDateInProgress() )
                {
                    $object->status = TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_ALLOWED;
                }
                else
                {
                    $object->status = TenderCallingTenderInformation::SUBMISSION;
                }
            }

            $logStatus = TenderCallingTenderInformation::USER_VERIFICATION_CONFIRMED;
        }

        if( ( ! $hasVerifiers ) && ( $sentToVerify || $datesExtension ) )
        {
            // Set different type of status based on the current form's operation.
            // Normal form or extend deadline form.
            if( $datesExtension )
            {
                $object->status = TenderCallingTenderInformation::EXTEND_DATE_VALIDATION_ALLOWED;
            }
            else
            {
                $object->status = TenderCallingTenderInformation::SUBMISSION;
            }

            $logStatus = TenderCallingTenderInformation::USER_VERIFICATION_CONFIRMED;
        }

        $object = $tender->callingTenderInformation()->save($object);

        // send requesting verification email
        if( $sentToVerify OR $datesExtension )
        {
            $viewName = isset($inputs['dates_extension']) ? 'calling_tender_extend_dateline' : 'calling_tender';
            $this->emailNotifier->sendCTTenderVerificationEmail($tender, $object, $viewName);
            $this->systemNotifier->sendTenderVerificationNotification($tender, $tender->callingTenderInformation, $viewName, '#s3');
        }

        // send verifier's decision email
        if( $verificationReject OR $verificationConfirm )
        {
            $object->load('updatedBy');

            $viewName = 'calling_tender_confirm';

            if( $verificationReject )
            {
                $viewName = 'calling_tender_reject';
            }
            else
            {
                $isExtend = $tender->callingTenderInformation->extendingDateInProgress();
                $view = $isExtend ? 'calling_tender_extend_dateline' : 'calling_tender';
                $this->emailNotifier->sendCTTenderVerificationEmail($tender, $object, $view);
                $this->systemNotifier->sendTenderVerificationNotification($tender, $tender->callingTenderInformation, $view, '#s3');
            }

            // sends email notifications whenever tender processes are approved or rejected
            $recipientLocale = $object->updatedBy->settings->language->code;
            $subject = '[' . $tender->project->reference . '] ' . trans('email.eTenderNotification', [], 'messages', $recipientLocale);
            $view = 'notifications.email.tender.' . $viewName;
            $this->emailNotifier->sendTenderProcessApproveOrRejectEmail($tender, $subject, $view, $object->updatedBy);

            // sends system notifications whenever tender processes are approved or rejected
            $this->sendTenderProcessApproveOrRejectNotification($tender->project, $tender, array( $object->updatedBy ), \Confide::user(), $viewName, 'projects.tender.show', '#s3');
        }

        if( $logStatus )
        {
            $log                  = new TenderFormVerifierLog();
            $log->user_id         = $user->id;
            $log->type            = $logStatus;
            $log->verifier_remark = $verifierRemark;

            $object->verifierLogs()->save($log);
        }

        return $object;
    }

    private function sendTenderProcessApproveOrRejectNotification(
        Project $project, Tender $tender, array $recipients, User $sender, $viewName, $routeName, $tabId)
    {
        $url = route($routeName, array($project->id, $tender->id), false);
        $url .= $tabId;
        $notificationInfo = array();
        $currentTime = Carbon::now();
        $view = \View::make("notifications.system.{$viewName}");

        foreach($recipients as $recipient) {
            // don't send notification to self
            if($recipient->id == $sender->id) continue;

            $notificationInfo = array(
                'from_id'       => $sender->id,
                'from_type'     => 'PCK\Users\User',
                'to_id'         => $recipient->id,
                'to_type'       => 'PCK\Users\User',
                'category_id'   => 1,
                'url'           => $url,
                'extra'         => $view,
                'created_at'    => $currentTime,
                'updated_at'    => $currentTime
            );
        }

        if( ! empty( $notificationInfo ) ) \Notifynder::sendMultiple($notificationInfo);
    }

    public function syncSelectedContractorStatus(TenderCallingTenderInformation $callingTenderInfo, array $inputs)
    {
        if( ! isset( $inputs['status'] ) ) return;

        foreach($inputs['status'] as $companyId => $status)
        {
            $status = empty( $status ) ? null : $status;

            $callingTenderInfo->selectedContractors()->updateExistingPivot($companyId, ['status' => $status]);

            $result = \DB::table('company_tender_calling_tender_information')
                ->select('id')
                ->where('tender_calling_tender_information_id', '=', $callingTenderInfo->id)
                ->where('company_id', '=', $companyId)
                ->first();

            $this->updateContractorsCommitmentStatusLog($result->id, $status);
        }
    }

    public function syncSelectedVerifiers(TenderCallingTenderInformation $callingTenderInformation, array $inputs)
    {
        $data = array();

        foreach($inputs['verifiers'] ?? array() as $verifier)
        {
            if( $verifier <= 0 ) continue;

            $data[] = $verifier;
        }

        $callingTenderInformation->verifiers()->sync(array());
        $callingTenderInformation->verifiers()->sync($data);

        // reload the relation, in order not keep cache copy
        $callingTenderInformation->load('verifiers');
    }

    private function setRejectedStatusToAllVerifiers(TenderCallingTenderInformation $ctInformation)
    {
        $statuses = array(
            TenderCallingTenderInformation::USER_VERIFICATION_IN_PROGRESS,
            TenderCallingTenderInformation::USER_VERIFICATION_CONFIRMED
        );

        \DB::table('tender_calling_tender_information_user')
            ->where('tender_calling_tender_information_id', '=', $ctInformation->id)
            ->whereIn('status', $statuses)
            ->update(array( 'status' => TenderCallingTenderInformation::USER_VERIFICATION_REJECTED ));

        $ctInformation->load('verifiers');
    }

    private function setVerificationConfirmToCurrentVerifier(TenderCallingTenderInformation $object)
    {
        $user = \Confide::user();

        $object->verifiers()->updateExistingPivot($user->id, array(
            'status' => TenderCallingTenderInformation::USER_VERIFICATION_CONFIRMED
        ));

        $object->load('verifiers');
    }

    public function sendEmailNotificationToSelectedContractors(array $contractorCompanyAdminsId, $projectId, $tenderId, $emailDetails)
    {
        $tenderStage = TenderStages::TENDER_STAGE_CALLING_TENDER;
        $tenderCTInformation = TenderCallingTenderInformation::where('tender_id', '=', $tenderId)->first();
        $project = Project::find($projectId);

        foreach($contractorCompanyAdminsId as $id) {
            $admin   = User::find($id);
            $subject = '[' . $project->reference . '] ' . trans('tenders.tenderInvitation', [], 'messages', $admin->settings->language->code);

            $this->emailNotifier->sendStatusConfirmationEmailToSelectedContractor($project, $tenderStage, $tenderCTInformation, $admin, $emailDetails, $subject);
        }
    }

    public function updateCompanyTenderCallingTenderInfoConfirmationStatusUnauthenticated($key, $input)
    {
        $commitmentStatusId = $input['option'];

        $result = \DB::table('company_tender_calling_tender_information')
            ->select('id')
            ->where('status_key', '=', $key)
            ->first();

        $company_tender_calling_tender_information_id = $result->id;

        $result = \DB::table('company_tender_calling_tender_information')
            ->where('status_key', '=', $key)
            ->update(array(
                'status'     => $commitmentStatusId,
                'status_key' => null
            ));

        if( $result )
        {
            $log = $this->updateContractorsCommitmentStatusLog($company_tender_calling_tender_information_id, $commitmentStatusId);

            //send email to business unit
            $companyTenderCallingTenderInformation = CompanyTenderCallingTenderInformation::find($company_tender_calling_tender_information_id);
            $projectId                             = $companyTenderCallingTenderInformation->tenderCallingTenderInformation->tender->project->id;
            $replyDetails                          = array();
            $replyDetails['companyId']             = $companyTenderCallingTenderInformation->company_id;
            $replyDetails['status']                = $log->status;
            $replyDetails['replied_at_time']       = $log->created_at;

            $this->sendEmailNotificationForStatusConfirmationReply($projectId, $replyDetails);
        }

        return $result;
    }

    public function updateContractorsCommitmentStatusLog($company_tender_calling_tender_information_id, $commitmentStatusId, $input = array())
    {
        $company_tenderCallingTenderInfo = CompanyTenderCallingTenderInformation::find($company_tender_calling_tender_information_id);

        if( ! $user = \Confide::user() ) $user = $company_tenderCallingTenderInfo->company->companyAdmin;

        return $company_tenderCallingTenderInfo->contractorsCommitmentStatusLog()
            ->create(array(
                "user_id" => $user->id,
                "status"  => $commitmentStatusId,
                "remarks" => $input['remarks'] ?? null,
            ));
    }

    public function getContractorsCommitmentStatusLog($tenderId, $companyId)
    {
        $record = \DB::table('company_tender_calling_tender_information')
            ->join('tender_calling_tender_information', 'company_tender_calling_tender_information.tender_calling_tender_information_id', '=', 'tender_calling_tender_information.id')
            ->select('company_tender_calling_tender_information.id')
            ->where('company_tender_calling_tender_information.company_id', '=', $companyId)
            ->where('tender_calling_tender_information.tender_id', '=', $tenderId)
            ->first();

        $company_tenderCallingTenderInfo = CompanyTenderCallingTenderInformation::find($record->id);

        $log = array();

        foreach($company_tenderCallingTenderInfo->contractorsCommitmentStatusLog as $logEntry)
        {
            $user   = User::find($logEntry->user_id);
            $status = ContractorCommitmentStatus::getText($logEntry->status);

            array_push($log, array( 'user' => $user->name, 'userEmail' => $user->email, 'status' => $status, 'date' => $logEntry->created_at, 'remarks' => $logEntry->remarks ));
        }

        return $log;
    }

    public function getPendingCallingTendersByUser(User $user, $includeFutureTasks, Project $project = null)
    {
        $listOfTendersCallingTenderInfo = [];
        $proceed = false;

        if($project)
        {
            if($project->latestTender->callingTenderInformation)
            {
                $callingTender = $project->latestTender->callingTenderInformation;

                if($callingTender && $callingTender->isBeingValidated())
                {
                    $proceed = ($includeFutureTasks) ? in_array($user->id, $callingTender->verifiers->lists('id')) : ($callingTender->latestVerifier->first() && ($user->id === $callingTender->latestVerifier->first()->id));
                    
                    if($proceed)
                    {
                        array_push($listOfTendersCallingTenderInfo, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => TenderCallingTenderInformation::CALLING_TENDER_MODULE_NAME,
                            'days_pending'             => TenderCallingTenderOfTendererInformationUser::getDaysPending($callingTender, $user),
                            'tender_id'                => $project->latestTender->id,
                            'is_future_task'           => !($callingTender->latestVerifier->first() && ($user->id === $callingTender->latestVerifier->first()->id)),
                            'route'                    => route('projects.tender.show', array('projectId' => $project->id, 'tenderId' => $project->latestTender->id)) . '#s3'
                        ]);
                    }
                }
            }
        }
        else
        {
            $listOfVerifiers = DB::table('tender_calling_tender_information_user')
                                    ->join('tender_calling_tender_information', 'tender_calling_tender_information_user.tender_calling_tender_information_id', '=', 'tender_calling_tender_information.id')
                                    ->join('tenders', 'tender_calling_tender_information.tender_id' , '=', 'tenders.id')
                                    ->join('projects', 'tenders.project_id', '=', 'projects.id')
                                    ->select('tender_calling_tender_information_user.id', 'tender_calling_tender_information_user.tender_calling_tender_information_id', 'tender_calling_tender_information_user.user_id')
                                    ->where('tender_calling_tender_information_user.status', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
                                    ->where('tender_calling_tender_information_user.user_id', $user->id)
                                    ->whereNull('projects.deleted_at')
                                    ->get();

            foreach($listOfVerifiers as $verifier)
            {
                $callingTender = TenderCallingTenderInformation::find($verifier->tender_calling_tender_information_id);

                if($callingTender && $callingTender->isBeingValidated())
                {
                    $proceed      = ($includeFutureTasks) ? in_array($user->id, $callingTender->verifiers->lists('id')) : ($callingTender->latestVerifier && ($user->id === $callingTender->latestVerifier->first()->id));

                    if($proceed)
                    {
                        $project      = $callingTender->tender->project;
                        $partialRoute = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

                        array_push($listOfTendersCallingTenderInfo, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => TenderCallingTenderInformation::CALLING_TENDER_MODULE_NAME,
                            'days_pending'             => TenderCallingTenderOfTendererInformationUser::getDaysPending($callingTender, $user),
                            'tender_id'                => $callingTender->tender->id,
                            'is_future_task'           => !($callingTender->latestVerifier && ($user->id === $callingTender->latestVerifier->first()->id)),
                            'route'                    => route($partialRoute, array('projectId' => $project->id, 'tenderId' => $callingTender->tender->id)) . '#s3'
                        ]);
                    }
                }
            }
        }

        return $listOfTendersCallingTenderInfo;
    }
}