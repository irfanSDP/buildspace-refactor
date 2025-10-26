<?php

use PCK\Projects\Project;
use PCK\Notifications\EmailNotifier;

class SubmitClaimsController extends \BaseController {

    private $projectRepository;
    private $claimSubmissionForm;
    private $emailNotifier;
    private $invoiceUploadForm;

    public function __construct(PCK\Projects\ProjectRepository $projectRepository, PCK\Forms\ClaimSubmissionForm $claimSubmissionForm, EmailNotifier $emailNotifier, PCK\Forms\InvoiceUploadForm $invoiceUploadForm)
    {
        $this->projectRepository   = $projectRepository;
        $this->claimSubmissionForm = $claimSubmissionForm;
        $this->emailNotifier       = $emailNotifier;
        $this->invoiceUploadForm   = $invoiceUploadForm;
    }

    public function show(Project $project)
    {
        $user         = Confide::user();
        $claimsData   = array();
        $claimCertIds = array();

        $buildSpaceProjectMainInfo = $project->getBsProjectMainInformation();

        $claimRevisions = $buildSpaceProjectMainInfo->projectStructure->postContract->postContractClaimRevisions;

        foreach($claimRevisions as $claimRevision)
        {
            if( ! $claimRevision->claimCertificate ) continue;

            $claimCertIds[] = $claimRevision->claimCertificate->id;
        }

        $claimCertificateInfo = PCK\Buildspace\ClaimCertificate::getClaimCertInfo($claimCertIds);

        foreach($claimRevisions->reverse() as $claimRevision)
        {
            if( ! $claimRevision->claimCertificate ) continue;

            $bqWorkDoneAmount     = "-";
            $voWorkDoneAmount     = "-";
            $materialOnSiteAmount = "-";
            $totalWorkDone        = "-";
            $submittedBy          = "-";
            $submittedAt          = "-";

            if( count($log = $claimRevision->getClaimImportLog()) > 0 )
            {
                $bqWorkDoneAmount     = $claimRevision->getImportedBQWorkDoneAmount();
                $voWorkDoneAmount     = $claimRevision->getImportedVariationOrderWorkDoneAmount();
                $materialOnSiteAmount = $claimRevision->getImportedMaterialOnSiteAmount();

                $totalWorkDone        = number_format(( $bqWorkDoneAmount + $voWorkDoneAmount + $materialOnSiteAmount ), 2);
                $bqWorkDoneAmount     = number_format($bqWorkDoneAmount, 2);
                $voWorkDoneAmount     = number_format($voWorkDoneAmount, 2);
                $materialOnSiteAmount = number_format($materialOnSiteAmount, 2);

                $latestLog = array_shift($log);

                $bsUserId = $latestLog->created_by;

                $bsUser      = PCK\Buildspace\User::find($bsUserId);
                $submittedBy = $bsUser->Profile->getEProjectUser()->name;
                $submittedAt = \Carbon\Carbon::parse($project->getProjectTimeZoneTime($latestLog->created_at))->format(\Config::get('dates.created_at'));
            }

            $claimCertificateApproved = false;
            $certifiedAmount          = "-";

            if( $claimRevision->claimCertificate->status == \PCK\Buildspace\ClaimCertificate::STATUS_TYPE_APPROVED )
            {
                $claimCertificateApproved = true;
                $certifiedAmount          = number_format($claimCertificateInfo[ $claimRevision->claimCertificate->id ]['amountCertified'], 2);
            }

            $canUnlockResubmission = $claimRevision->claim_submission_locked && $claimRevision->version == $claimRevisions->first()->version && ! $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR);

            $invoiceAttachments = \PCK\ModuleUploadedFiles\ModuleUploadedFile::getAttachments($claimRevision, \PCK\ModuleUploadedFiles\ModuleUploadedFile::TYPE_CLAIM_CERTIFICATE_INVOICE);

            $claimsData[] = array(
                'claimVersion'                => $claimRevision->version,
                'bqWorkDoneAmount'            => $bqWorkDoneAmount,
                'voWorkDoneAmount'            => $voWorkDoneAmount,
                'materialOnSiteAmount'        => $materialOnSiteAmount,
                'totalWorkDone'               => $totalWorkDone,
                'certifiedAmount'             => $certifiedAmount,
                'printRoute'                  => route('contractor.finance.claim-certificate.print', array( $claimRevision->claimCertificate->id )),
                'exportClaimsRoute'           => \Config::get('buildspace.BUILDSPACE_URL') . "claimTransfer/exportClaims/pid/{$buildSpaceProjectMainInfo->projectStructure->id}/revision_id/{$claimRevision->id}",
                'isCertApproved'              => $claimCertificateApproved,
                'submittedBy'                 => $submittedBy,
                'submittedAt'                 => $submittedAt,
                'canUnlockResubmission'       => $canUnlockResubmission,
                'route:unlockResubmission'    => route('projects.contractorClaims.unlockSubmission', array( $project->id, $claimRevision->id )),
                'route:unlockResubmissionLog' => route('projects.contractorClaims.unlockSubmission.log', array( $project->id, $claimRevision->id )),
                'route:claimSubmissionLog'    => route('projects.contractorClaims.submission.log', array( $project->id, $claimRevision->id )),
                'route:invoiceUpload'         => route('projects.contractorClaims.invoice', array( $project->id, $claimRevision->id )),
                'route:invoiceAttachments'    => ! $invoiceAttachments->isEmpty() ? route('projects.contractorClaims.invoice.attachments.list', array( $project->id, $claimRevision->id )) : null,
            );
        }

        JavaScript::put(array( "claimsData" => $claimsData ));

        $canSubmitClaim = $user->canSubmitClaim($project);

        return View::make('contractorClaims.show', array(
            'project'        => $project,
            'canSubmitClaim' => $canSubmitClaim,
        ));
    }

    public function update($project)
    {
        $claimsFile = Input::file('claims');

        $input = Input::all();

        $this->claimSubmissionForm->validate($input);

        try
        {
            $response = $this->projectRepository->submitClaims($project, $claimsFile);

            Flash::error(trans('general.somethingWentWrong'));

            if( $response['running'] )
            {
                $claimRevision = $project->getBsProjectMainInformation()->projectStructure->postContract->postContractClaimRevisions->first();

                $claimRevision = PCK\Buildspace\PostContractClaimRevision::where('post_contract_id', '=', $project->getBsProjectMainInformation()->projectStructure->postContract->id)
                    ->where('version', '=', $claimRevision->version)
                    ->first();

                PCK\Helpers\ModuleAttachment::saveAttachments($claimRevision, $input);

                Flash::success(trans('finance.claimsSubmitted') . " ({$project->title}, " . trans('finance.claimNo:number', array( 'claimNumber' => $claimRevision->version )) . ")");
            }
        }
        catch(\Exception $e)
        {
            Flash::error($e->getMessage());
            \Log::info($e->getTraceAsString());
        }

        return Redirect::back();
    }

    public function getAttachmentList($project)
    {
        $bsPostContract = $project->getBsProjectMainInformation()->projectStructure->postContract;

        $claimRevision = PCK\Buildspace\PostContractClaimRevision::where('post_contract_id', '=', $bsPostContract->id)
            ->where('version', '=', Input::get('version'))
            ->first();

        $data = array();

        foreach($claimRevision->getAttachmentDetails() as $upload)
        {
            $data[] = array(
                'filename'    => $upload->filename,
                'download_url' => $upload->download_url,
                'uploaded_by'  => $upload->createdBy->name,
                'uploaded_at'  => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($upload->created_at))->format(\Config::get('dates.created_at')),
            );
        }

        return json_encode($data);
    }

    public function unlockSubmission($project, $claimRevisionId)
    {
        DB::connection('buildspace')->table(with(new PCK\Buildspace\PostContractClaimRevision)->getTable())
            ->where('id', $claimRevisionId)
            ->update(array( 'claim_submission_locked' => false ));

        PCK\Buildspace\UnlockClaimSubmissionLog::addEntry($claimRevisionId);

        $claimRevision = PCK\Buildspace\PostContractClaimRevision::find($claimRevisionId);

        $this->emailNotifier->sendClaimRejectedNotifications($claimRevision);

        Flash::success(trans('finance.claimSubmissionUnlocked'));

        return Redirect::back();
    }

    public function getClaimSubmissionLog($project, $claimRevisionId)
    {
        $claimRevision = PCK\Buildspace\PostContractClaimRevision::find($claimRevisionId);

        $records = array();

        foreach(array_reverse($claimRevision->getClaimImportLog()) as $entry)
        {
            $bsUser = PCK\Buildspace\User::find($entry->created_by);

            $record = array(
                'created_at' => \Carbon\Carbon::parse($entry->created_at)->format(\Config::get('dates.created_at')),
                'created_by' => $bsUser->Profile->getEProjectUser()->name,
            );

            $records[] = $record;
        }

        return json_encode($records);
    }

    public function getUnlockSubmissionLog($project, $claimRevisionId)
    {
        $claimRevision = PCK\Buildspace\PostContractClaimRevision::find($claimRevisionId);

        $records = array();

        foreach(array_reverse($claimRevision->getUnlockClaimSubmissionLog()) as $entry)
        {
            $bsUser = PCK\Buildspace\User::find($entry->created_by);

            $record = array(
                'created_at' => \Carbon\Carbon::parse($entry->created_at)->format(\Config::get('dates.created_at')),
                'created_by' => $bsUser->Profile->getEProjectUser()->name,
            );

            $records[] = $record;
        }

        return json_encode($records);
    }

    public function invoiceUpload($project, $claimRevisionId)
    {
        $this->invoiceUploadForm->validate(Input::all());

        $claimRevision = PCK\Buildspace\PostContractClaimRevision::find($claimRevisionId);

        try
        {
            PCK\Helpers\ModuleAttachment::saveAttachments($claimRevision, Input::all(), PCK\ModuleUploadedFiles\ModuleUploadedFile::TYPE_CLAIM_CERTIFICATE_INVOICE);

            Flash::success(trans('forms.uploadSuccessful'));
        }
        catch(Exception $exception)
        {
            \Log::error($exception);

            Flash::error(trans('forms.uploadUnsuccessful'));
        }

        return Redirect::back();
    }

    public function invoiceAttachmentList($project, $claimRevisionId)
    {
        $claimRevision = PCK\Buildspace\PostContractClaimRevision::find($claimRevisionId);

        $records = [];

        foreach(\PCK\ModuleUploadedFiles\ModuleUploadedFile::getAttachments($claimRevision, \PCK\ModuleUploadedFiles\ModuleUploadedFile::TYPE_CLAIM_CERTIFICATE_INVOICE) as $attachment)
        {
            $records[] = array(
                'filename'    => $attachment->file->filename,
                'download_url' => $attachment->file->download_url,
                'uploaded_by'  => $attachment->file->createdBy->name,
                'uploaded_at'  => $project->getProjectTimeZoneTime($attachment->file->created_at),
            );
        }

        return $records;
    }
}   