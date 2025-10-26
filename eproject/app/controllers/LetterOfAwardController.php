<?php

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\LetterOfAward\LetterOfAward;
use PCK\LetterOfAward\LetterOfAwardRepository;
use PCK\LetterOfAward\LetterOfAwardClauseCommentRepository;
use PCK\LetterOfAward\LetterOfAwardUserPermission;
use PCK\Verifier\Verifier;
use PCK\Helpers\PdfHelper;

class LetterOfAwardController extends BaseController {

    private $letterOfAwardRepo;
    private $letterOfAwardClauseCommentRepo;

    public function __construct(LetterOfAwardRepository $letterOfAwardRepo, LetterOfAwardClauseCommentRepository $letterOfAwardClauseCommentRepo)
    {
        $this->letterOfAwardRepo              = $letterOfAwardRepo;
        $this->letterOfAwardClauseCommentRepo = $letterOfAwardClauseCommentRepo;
    }

    public function index(Project $project)
    {
        $letterOfAward = $project->letterOfAward;

        $user                        = \Confide::user();
        $reviewers                   = LetterOfAwardRepository::getUsersInModule($project, LetterOfAwardUserPermission::REVIEWER);
        $canSubmitForApproval        = $letterOfAward->canUserSubmitForApproval($user);
        $canApproveOrReject          = $letterOfAward->canApproveOrRejectApproval($user);
        $isApproved                  = $letterOfAward->status == LetterOfAward::APPROVED;
        $reviewersWithEditorOption   = LetterOfAwardRepository::getUsersInModule($project, LetterOfAwardUserPermission::REVIEWER, true);
        $canUserEditLetterOfAward    = $letterOfAward->canUserEditLetterOfAward($user);
        $canUserCommentLetterOfAward = $letterOfAward->canUserCommentLetterOfAward($user);
        $unreadCommentsCount         = array_sum($this->letterOfAwardClauseCommentRepo->getUnreadCommentsCountGroupedByClause($letterOfAward));
        $verifierLogs                = Verifier::getLog($letterOfAward, true);

        return View::make('letter_of_award.letterOfAward.index', [
            'project'                     => $project,
            'letterOfAward'               => $letterOfAward,
            'isTemplate'                  => $letterOfAward->is_template,
            'reviewers'                   => $reviewers,
            'canSubmitForApproval'        => $canSubmitForApproval,
            'canApproveOrReject'          => $canApproveOrReject,
            'isApproved'                  => $isApproved,
            'reviewersWithEditorOption'   => $reviewersWithEditorOption,
            'canUserEditLetterOfAward'    => $canUserEditLetterOfAward,
            'canUserCommentLetterOfAward' => $canUserCommentLetterOfAward,
            'unreadCommentsCount'         => $unreadCommentsCount,
            'verifierLogs'                => $verifierLogs,
            'printRoute'                  => route('letterOfAward.process', [$project->id]),
            'printSettingsEditRoute'      => route('letterOfAward.print.settings.edit', [$project->id]),
            'editLogRoute'                => route('letterOfAward.log.get', [$project->id]),
        ]);
    }

    public function contractDetailsEdit(Project $project)
    {
        $letterOfAward            = $project->letterOfAward;
        $user                     = \Confide::user();
        $canUserEditLetterOfAward = $letterOfAward->canUserEditLetterOfAward($user);

        return View::make('letter_of_award.letterOfAward.contract_details.edit', [
            'project'                  => $project,
            'contractDetails'          => $letterOfAward->contractDetail,
            'isTemplate'               => $letterOfAward->is_template,
            'indexRoute'               => route('letterOfAward.index', [ $project->id ]),
            'populateContentsRoute'    => route('letterOfAward.contractDetails.get', [ $project->id ]),
            'saveContentsRoute'        => route('letterOfAward.contractDetails.save', [ $project->id ]),
            'canUserEditLetterOfAward' => $canUserEditLetterOfAward,
        ]);
    }

    public function getContractDetails(Project $project)
    {
        $letterOfAward = $project->letterOfAward;

        return $this->letterOfAwardRepo->getContractDetail($letterOfAward);
    }

    public function saveContractDetails(Project $project)
    {
        $inputs        = Input::all();
        $letterOfAward = $project->letterOfAward;

        $success = $this->letterOfAwardRepo->saveContractDetails($letterOfAward, $inputs['contents']);

        return Response::json([
            'success' => $success
        ]);
    }

    public function signatoryEdit(Project $project)
    {
        $letterOfAward            = $project->letterOfAward;
        $user                     = \Confide::user();
        $canUserEditLetterOfAward = $letterOfAward->canUserEditLetterOfAward($user);

        return View::make('letter_of_award.letterOfAward.signatory.edit', [
            'project'                  => $project,
            'signatory'                => $letterOfAward->signatory,
            'isTemplate'               => $letterOfAward->isTemplate,
            'indexRoute'               => route('letterOfAward.index', [ $project->id ]),
            'populateContentsRoute'    => route('letterOfAward.signatory.get', [ $project->id ]),
            'saveContentsRoute'        => route('letterOfAward.signatory.save', [ $project->id ]),
            'canUserEditLetterOfAward' => $canUserEditLetterOfAward,
        ]);
    }

    public function getSignatory(Project $project)
    {
        $letterOfAward = $project->letterOfAward;

        return $this->letterOfAwardRepo->getSignatory($letterOfAward);
    }

    public function saveSignatory(Project $project)
    {
        $inputs        = Input::all();
        $letterOfAward = $project->letterOfAward;

        $success = $this->letterOfAwardRepo->saveSignatory($letterOfAward, $inputs['contents']);

        return Response::json([
            'success' => $success
        ]);
    }

    public function clausesEdit(Project $project)
    {
        $letterOfAward                      = $project->letterOfAward;
        $user                               = \Confide::user();
        $structuredClauses                  = $this->letterOfAwardRepo->getStructuredClauses($letterOfAward);
        $canUserEditLetterOfAward           = $letterOfAward->canUserEditLetterOfAward($user);
        $canUserCommentLetterOfAward        = $letterOfAward->canUserCommentLetterOfAward($user);
        $unreadCommentsCountGroupedByClause = $this->letterOfAwardClauseCommentRepo->getUnreadCommentsCountGroupedByClause($letterOfAward);

        return View::make('letter_of_award.letterOfAward.clauses.edit', [
            'project'                            => $project,
            'clauses'                            => json_encode($structuredClauses),
            'isTemplate'                         => $letterOfAward->is_template,
            'indexRoute'                         => route('letterOfAward.index', [ $project->id ]),
            'populateContentsRoute'              => route('letterOfAward.clause.get', [ $project->id ]),
            'saveContentsRoute'                  => route('letterOfAward.clause.save', [ $project->id ]),
            'canUserEditLetterOfAward'           => $canUserEditLetterOfAward,
            'canUserCommentLetterOfAward'        => $canUserCommentLetterOfAward,
            'unreadCommentsCountGroupedByClause' => json_encode($unreadCommentsCountGroupedByClause),
        ]);
    }

    public function saveClauses(Project $project)
    {
        $inputs        = Input::all();
        $letterOfAward = $project->letterOfAward;
        $success       = $this->letterOfAwardRepo->saveClauses($letterOfAward, $inputs);

        return Response::json([
            'success' => $success,
            'url'     => route('letterOfAward.index', [ $project->id ]),
        ]);
    }

    public function print(Project $project)
    {
        $letterOfAward        = $project->letterOfAward;
        $headerHeightInPixels = Input::get('h');

        $contractDetails = $this->letterOfAwardRepo->getContractDetail($letterOfAward);
        $printSettings   = $letterOfAward->printSetting;

        $data = [
            'contractDetails' => $contractDetails,
            'signatory'       => $this->letterOfAwardRepo->getSignatory($letterOfAward),
            'clauses'         => $this->letterOfAwardRepo->getStructuredClauses($letterOfAward),
            'printSettings'   => [
                'clause_font_size' => $printSettings->clause_font_size,
            ],
        ];

        $pdfHelper = new PdfHelper('letter_of_award.letterOfAward.print.layout', $data);

        $pdfHelper->setHeaderHtml($this->letterOfAwardRepo->getHeaderHtml($contractDetails, $printSettings));
        $pdfHelper->setOptions($this->letterOfAwardRepo->generatePdfOptions($printSettings, $headerHeightInPixels));

        return $pdfHelper->printPDF();
    }

    public function processLetterOfAward(Project $project)
    {
        $letterOfAward = $project->letterOfAward;
        $printSettings = $letterOfAward->printSetting;

        $contractDetails = $letterOfAward->contractDetail;

        $content = $this->letterOfAwardRepo->getHeaderHtml($contractDetails, $printSettings);

        $content = PdfHelper::removeBreaksFromHtml($content);

        $routeGenerate = route('letterOfAward.print', [ $letterOfAward->project->id ]);

        return View::make('letter_of_award.letterOfAward.print.getHeight', [
            'content'       => $content,
            'routeGenerate' => $routeGenerate,
        ]);
    }

    public function editPrintSettings(Project $project)
    {
        $letterOfAward = $project->letterOfAward;

        return View::make('letter_of_award.letterOfAward.print.settings_edit', [
            'project'       => $project,
            'isTemplate'    => $letterOfAward->is_template,
            'printSettings' => $letterOfAward->printSetting,
            'indexRoute'    => route('letterOfAward.index', [$project->id]),
            'saveRoute'     => route('letterOfAward.print.settings.save', [$project->id]),
        ]);
    }

    public function savePrintSettings(Project $project)
    {
        $inputs        = Input::all();
        $letterOfAward = $project->letterOfAward;

        $this->letterOfAwardRepo->savePrintSettings($letterOfAward, $inputs);

        return Redirect::route('letterOfAward.index', [ $project->id ]);
    }

    public function submit(Project $project)
    {
        $inputs        = Input::all();
        $letterOfAward = $project->letterOfAward;

        $this->letterOfAwardRepo->submitForApproval($letterOfAward, $inputs);

        return Redirect::back();
    }

    public function notifyReviewer(Project $project)
    {
        $success = $this->letterOfAwardRepo->notifyReviewer($project);

        return Response::json([
            'success' => $success,
        ]);
    }

    public function sendCommentNotification(Project $project)
    {
        $success = $this->letterOfAwardRepo->sendCommentNotification($project);

        return Response::json([
            'success' => $success,
        ]);
    }

    public function getLogs(Project $project)
    {
        $letterOfAward = $project->letterOfAward;
        $logs          = $this->letterOfAwardRepo->getLogs($letterOfAward);

        foreach($logs as $key => $log)
        {
            $logs[ $key ]['date'] = Carbon::parse($project->getProjectTimeZoneTime($log['date']))->format(\Config::get('dates.full_format'));
        }

        return Response::json([
            'logs' => $logs,
        ]);
    }

    public function getUserHasPendingApprovalLetterOfAward(Project $project, $userId, $moduleId)
    {
        $user                    = User::find($userId);
        $hasPendingLetterOfAward = ($moduleId == LetterOfAwardUserPermission::EDITOR) ? false : $this->letterOfAwardRepo->getUserHasPendingApprovalLetterOfAward($project->letterOfAward, $user);
        $message                 = $hasPendingLetterOfAward ? trans('letterOfAward.unableToDeleteUser') . '. ' . trans('letterOfAward.userCurrentlyInvolvedInApproval') . '.' : null;

        return Response::json([
            'hasPendingLetterOfAward' => $hasPendingLetterOfAward,
            'message'                 => $message,
        ]);
    }
}

