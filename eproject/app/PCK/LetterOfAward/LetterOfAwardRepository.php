<?php namespace PCK\LetterOfAward;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PCK\LetterOfAward\LetterOfAwardPrintSetting;
use PCK\LetterOfAward\LetterOfAward;
use PCK\LetterOfAward\LetterOfAwardContractDetail;
use PCK\LetterOfAward\LetterOfAwardClause;
use PCK\LetterOfAward\LetterOfAwardSignatory;

use HTMLPurifier, HTMLPurifier_Config;

class LetterOfAwardRepository {

    protected $emailNotifier;
    protected $systemNotifier;

    public function __construct(EmailNotifier $emailNotifier, SystemNotifier $systemNotifier)
    {
        $this->emailNotifier  = $emailNotifier;
        $this->systemNotifier = $systemNotifier;
    }

    public function createNewTemplate($inputs)
    {
        $letterOfAward = new LetterOfAward();
        $letterOfAward->is_template = true;
        $letterOfAward->name = $inputs['name'];
        $letterOfAward->save();

        $letterOfAwardContractDetail = new LetterOfAwardContractDetail();
        $letterOfAwardContractDetail->letter_of_award_id = $letterOfAward ->id;
        $letterOfAwardContractDetail->save();

        $letterOfAwardClasue = new LetterOfAwardClause;
        $letterOfAwardClasue->letter_of_award_id = $letterOfAward ->id;
        $letterOfAwardClasue->sequence_number = 1;
        $letterOfAwardClasue->save();

        $letterOfAwardSignatory = new LetterOfAwardSignatory;
        $letterOfAwardSignatory->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardSignatory->save();

        $letterOfAwardPrintSettings = new \PCK\LetterOfAward\LetterOfAwardPrintSetting;
        $letterOfAwardPrintSettings->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardPrintSettings->save();
    }

    public function createEntry(Project $project, $templateId)
    {
        $letterOfAward = $this->createLetterOfAwardEntry($project);
        $template = LetterOfAward::find($templateId);
        $rootClauses   = LetterOfAwardClause::getRootClauses($template);
        $clausesArray  = [];

        foreach($rootClauses as $clause)
        {
            array_push($clausesArray, $this->getClausesArray($letterOfAward, $clause));
        }

        $this->cloneContractDetailFromTemplate($letterOfAward, $template->contractDetail);
        $this->cloneClausesFromTemplate($letterOfAward, $clausesArray);
        $this->cloneSignatoryFromTemplate($letterOfAward, $template->signatory);
        $this->clonePrintSettingsFromTemplate($letterOfAward, $template);

        return $letterOfAward;
    }

    private function createLetterOfAwardEntry(Project $project)
    {
        $letterOfAward              = new LetterOfAward();
        $letterOfAward->project_id  = $project->id;
        $letterOfAward->is_template = false;
        $letterOfAward->status      = LetterOfAward::EDITABLE;
        $letterOfAward->save();

        return $letterOfAward;
    }

    public function getClausesArray(LetterOfAward $letterOfAward, $clause)
    {
        $childClauses = LetterOfAwardClause::getChildrenOf($clause);
        $hasChildren  = ! $childClauses->isEmpty();

        $clausesArray = [
            'id'                 => $clause->id,
            'letter_of_award_id' => $clause->letter_of_award_id,
            'contents'           => $clause->contents,
            'display_numbering'  => $clause->display_numbering,
            'sequence_number'    => $clause->sequence_number,
        ];

        if( $hasChildren )
        {
            $childTemp = [];

            foreach($childClauses as $childClause)
            {
                array_push($childTemp, $this->getClausesArray($letterOfAward, $childClause));
            }

            $clausesArray['children'] = $childTemp;
        }

        return $clausesArray;
    }

    private function cloneContractDetailFromTemplate(LetterOfAward $letterOfAward, LetterOfAwardContractDetail $template)
    {
        $letterOfAwardContractDetails                     = new LetterOfAwardContractDetail();
        $letterOfAwardContractDetails->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardContractDetails->contents           = $template->contents;
        $letterOfAwardContractDetails->save();
    }

    private function cloneSignatoryFromTemplate(LetterOfAward $letterOfAward, LetterOfAwardSignatory $template)
    {
        $letterOfAwardCSignatory                     = new LetterOfAwardSignatory();
        $letterOfAwardCSignatory->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardCSignatory->contents           = $template->contents;
        $letterOfAwardCSignatory->save();
    }

    private function cloneClausesFromTemplate(LetterOfAward $letterOfAward, $structuredClauses)
    {
        $sequenceNumber = 1;

        foreach($structuredClauses as $structuredClause)
        {
            $this->createClause($letterOfAward, $structuredClause, $sequenceNumber++);
        }
    }

    private function createClause($letterOfAward, $clause, $sequenceNumber, $parentKey = null)
    {
        $hasChildren = array_key_exists('children', $clause);

        $letterOfAwardClause                     = new LetterOfAwardClause();
        $letterOfAwardClause->letter_of_award_id = $letterOfAward->id;
        $letterOfAwardClause->contents           = $clause['contents'];
        $letterOfAwardClause->display_numbering  = $clause['display_numbering'];
        $letterOfAwardClause->sequence_number    = $sequenceNumber;
        $letterOfAwardClause->parent_id          = $parentKey;

        $letterOfAwardClause->save();

        if( $hasChildren )
        {
            $sequenceNumber = 1;
            foreach($clause['children'] as $childClause)
            {
                $this->createClause($letterOfAward, $childClause, $sequenceNumber++, $letterOfAwardClause->id);
            }
        }
    }

    private function clonePrintSettingsFromTemplate(LetterOfAward $letterOfAward, LetterOfAward $template)
    {
        $printSettingsTemplate = $template->printSetting;

        $printSettings                     = new LetterOfAwardPrintSetting();
        $printSettings->letter_of_award_id = $letterOfAward->id;
        $printSettings->header_font_size   = $printSettingsTemplate->header_font_size;
        $printSettings->clause_font_size   = $printSettingsTemplate->clause_font_size;
        $printSettings->margin_top         = $printSettingsTemplate->margin_top;
        $printSettings->margin_bottom      = $printSettingsTemplate->margin_bottom;
        $printSettings->margin_left        = $printSettingsTemplate->margin_left;
        $printSettings->margin_right       = $printSettingsTemplate->margin_right;
        $printSettings->header_spacing     = $printSettingsTemplate->header_spacing;
        $printSettings->save();
    }

    public function getContractDetail($letterOfAward)
    {
        return $letterOfAward->contractDetail->contents;
    }

    public function saveContractDetails(LetterOfAward $letterOfAward, $contents)
    {
        $letterOfAwardContractDetails           = $letterOfAward->contractDetail;
        $letterOfAwardContractDetails->contents = $contents;
        $success                                = $letterOfAwardContractDetails->save();

        $this->saveLog($letterOfAward, LetterOfAwardLog::CONTRACT_DETAILS);

        return $success;
    }

    public function getSignatory(LetterOfAward $letterOfAward)
    {
        return $letterOfAward->signatory->contents;
    }

    public function saveSignatory(LetterOfAward $letterOfAward, $contents)
    {
        $letterOfAwardTemplateSignatory           = $letterOfAward->signatory;
        $letterOfAwardTemplateSignatory->contents = $contents;
        $success                                  = $letterOfAwardTemplateSignatory->save();

        $this->saveLog($letterOfAward, LetterOfAwardLog::SIGNATORY);

        return $success;
    }

    public function getStructuredClauses($letterOfAward)
    {
        $rootClauses = LetterOfAwardClause::getRootClauses($letterOfAward);

        $clausesArray = [];

        $config = HTMLPurifier_Config::createDefault();
        $config->loadArray([
            'Core.Encoding' => 'UTF-8',
            'HTML.Doctype' => 'XHTML 1.0 Strict',
            'HTML.Allowed' => 'div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true
        ]);

        $purifier = new HTMLPurifier($config);

        foreach($rootClauses as $clause)
        {
            $html = $purifier->purify($clause->contents);
            $data = [
                'id'               => $clause->id,
                'contents'         => trim($html),
                'displayNumbering' => $clause->display_numbering,
                'sequenceNumber'   => $clause->sequence_number,
                'parentId'         => $clause->parent_id,
                'children'         => $this->getChildrenOfNode($letterOfAward, $clause->id),
            ];

            array_push($clausesArray, $data);
        }

        return $clausesArray;
    }

    private function getChildrenOfNode($letterOfAward, $parentId)
    {
        $childrenArray = [];

        $children = LetterOfAwardClause::where('letter_of_award_id', $letterOfAward->id)
            ->where('parent_id', $parentId)
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $children->isEmpty() ) return $childrenArray;

        foreach($children as $child)
        {
            $data = [
                'id'               => $child->id,
                'contents'         => $child->contents,
                'displayNumbering' => $child->display_numbering,
                'sequenceNumber'   => $child->sequence_number,
                'parentId'         => $child->parent_id,
                'children'         => $this->getChildrenOfNode($letterOfAward, $child->id),
            ];

            array_push($childrenArray, $data);
        }

        return $childrenArray;
    }

    public function saveClauses(LetterOfAward $letterOfAward, $inputs)
    {
        $inactiveClauses = isset( $inputs['inactiveClauses'] ) ? $inputs['inactiveClauses'] : array();
        $clauses         = isset( $inputs['clauses'] ) ? $inputs['clauses'] : array();
        $sequenceNumber  = 1;

        foreach($inactiveClauses as $inactiveClause)
        {
            $isExistingClause = array_key_exists('id', $inactiveClause);

            if( $isExistingClause )
            {
                $this->deleteClauses($inactiveClause);
            }
        }

        foreach($clauses as $clause)
        {
            $this->updateOrCreateClauses($letterOfAward, $clause, $sequenceNumber++);
        }

        $this->saveLog($letterOfAward, LetterOfAwardLog::CLAUSES);

        return true;
    }

    private function updateOrCreateClauses($letterOfAward, $clause, $sequenceNumber, $parentKey = null)
    {
        $isExistingClause = array_key_exists('id', $clause);
        $hasChildren      = array_key_exists('children', $clause);

        if( $isExistingClause )
        {
            $letterOfAwardClause = LetterOfAwardClause::find($clause['id']);
        }
        else
        {
            $letterOfAwardClause                     = new LetterOfAwardClause();
            $letterOfAwardClause->letter_of_award_id = $letterOfAward->id;
        }

        $letterOfAwardClause->contents          = isset( $clause['content'] ) ? $clause['content'] : '';
        $letterOfAwardClause->display_numbering = ( $clause['displayNumbering'] === 'true' );
        $letterOfAwardClause->sequence_number   = $sequenceNumber;
        $letterOfAwardClause->parent_id         = $parentKey;

        $letterOfAwardClause->save();

        if( $hasChildren )
        {
            $sequenceNumber = 1;

            foreach($clause['children'] as $childClause)
            {
                $this->updateOrCreateClauses($letterOfAward, $childClause, $sequenceNumber++, $letterOfAwardClause->id);
            }
        }

    }

    private function deleteClauses($clause)
    {
        $letterOfAwardClause = LetterOfAwardClause::find($clause['id']);

        $children = LetterOfAwardClause::getChildrenOf($letterOfAwardClause);

        foreach($children as $child)
        {
            $this->deleteClauses($child);
        }

        $letterOfAwardClause->delete();
    }

    public function savePrintSettings(LetterOfAward $letterOfAward, $inputs)
    {
        $printSettings                   = $letterOfAward->printSetting;
        $printSettings->header_font_size = $inputs['headerFontSize'];
        $printSettings->clause_font_size = $inputs['clauseFontSize'];
        $printSettings->margin_top       = $inputs['marginTop'];
        $printSettings->margin_bottom    = $inputs['marginBottom'];
        $printSettings->margin_left      = $inputs['marginLeft'];
        $printSettings->margin_right     = $inputs['marginRight'];
        $printSettings->header_spacing   = $inputs['headerSpacing'];
        $printSettings->save();
    }

    public static function getUsersInModule(Project $project, $moduleIdentifier, $withEditorOption = false)
    {
        $query = LetterOfAwardUserPermission::where('project_id', $project->id)
            ->where('module_identifier', $moduleIdentifier);

        if( $withEditorOption )
        {
            $query = $query->where('is_editor', true);
        }

        $records = $query->get();

        $users = [];

        foreach($records as $record)
        {
            $user = User::find($record->user_id);
            array_push($users, $user);
        }

        return $users;
    }

    public function submitForApproval(LetterOfAward $letterOfAward, $inputs)
    {
        $verifiers = array_filter($inputs['verifiers'], function($value)
        {
            return $value != "";
        });

        if( empty( $verifiers ) )
        {
            $letterOfAward->status = LetterOfAward::APPROVED;
            $letterOfAward->save();
        }
        else
        {
            Verifier::setVerifiers($verifiers, $letterOfAward);

            $letterOfAward->submitted_for_approval_by = \Confide::user()->id;
            $letterOfAward->status                    = LetterOfAward::PENDING_FOR_VERIFICATION;
            $letterOfAward->save();

            Verifier::sendPendingNotification($letterOfAward);
        }
    }

    public function notifyReviewer(Project $project)
    {
        $reviewers     = $this->getAllUsersInModule($project, LetterOfAwardUserPermission::REVIEWER);
        $letterOfAward = $project->letterOfAward;
        $user          = \Confide::user();
        $emailView     = 'notifications.email.letterOfAward.review_request';
        $view          = 'letterOfAward.review_request';
        $route         = $project->letterOfAward->getRoute();

        foreach($reviewers as $reviewer)
        {
            $subject       = trans('letterOfAward.letterOfAwardNotification', [], 'messages', $reviewer->settings->language->code);

            $this->emailNotifier->sendLetterOfAwardNotification($letterOfAward, $user, $reviewer, $emailView, $subject);
            $this->systemNotifier->sendLetterOfAwardNotification($reviewer, $route, $view, $user);
        }

        return true;
    }

    public function sendCommentNotification(Project $project)
    {
        $editors       = $this->getAllUsersInModule($project, LetterOfAwardUserPermission::EDITOR);
        $letterOfAward = $project->letterOfAward;
        $user          = \Confide::user();
        $emailView     = 'notifications.email.letterOfAward.commented';
        $view          = 'letterOfAward.commented';
        $route         = $project->letterOfAward->getRoute();

        foreach($editors as $editor)
        {
            $subject       = trans('letterOfAward.letterOfAwardNotification', [], 'messages', $editor->settings->language->code);
        
            $this->emailNotifier->sendLetterOfAwardNotification($letterOfAward, $user, $editor, $emailView, $subject);
            $this->systemNotifier->sendLetterOfAwardNotification($editor, $route, $view, $user);
        }

        return true;
    }

    private function getAllUsersInModule(Project $project, $moduleIdentifier)
    {
        $users = [];

        $records = $project->letterOfAwardUserPermissions->filter(function($object) use ($moduleIdentifier)
        {
            return $object->module_identifier == $moduleIdentifier;
        });

        foreach($records as $record)
        {
            array_push($users, $record->user);
        }

        return $users;
    }

    public function getPendingApprovalLetterOfAward(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingLetterOfAwards = [];
        $proceed = false;

        if( $project )
        {
            $letterOfAward = $project->letterOfAward;

            if( ! $letterOfAward ) return [];

            $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $letterOfAward) : Verifier::isCurrentVerifier($user, $letterOfAward);

            if($proceed)
            {
                $tender = $project->latestTender;

                $now    = Carbon::now();
                $then   = Carbon::parse($letterOfAward->updated_at);

                array_push($pendingLetterOfAwards, [
                    'project_reference'        => $project->reference,
                    'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                    'project_id'               => $project->id,
                    'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                    'company_id'               => $project->business_unit_id,
                    'project_title'            => $project->title,
                    'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                    'module'                   => LetterOfAward::LETTER_OF_AWARD_MODULE_NAME,
                    'days_pending'             => $then->diffInDays($now),
                    'tender_id'                => $tender->id,
                    'is_future_task'           => Verifier::isCurrentVerifier($user, $letterOfAward),
                    'route'                    => route('letterOfAward.index', [ $project->id ]),
                ]);
            }
        }
        else
        {
            $records = Verifier::where('verifier_id', $user->id)->where('object_type', LetterOfAward::class)->get();

            foreach($records as $record)
            {
                $letterOfAward = LetterOfAward::find($record->object_id);
                $proceed       = $includeFutureTasks ? Verifier::isAVerifierInline($user, $letterOfAward) : Verifier::isCurrentVerifier($user, $letterOfAward);

                if($proceed)
                {
                    if(is_null($letterOfAward->project)) continue;
                    
                    $now     = Carbon::now();
                    $then    = Carbon::parse($letterOfAward->updated_at);
                    $project = $letterOfAward->project;
                    $tender  = $project->latestTender;

                    array_push($pendingLetterOfAwards, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => LetterOfAward::LETTER_OF_AWARD_MODULE_NAME,
                        'days_pending'             => $then->diffInDays($now),
                        'tender_id'                => $tender->id,
                        'is_future_task'           => Verifier::isCurrentVerifier($user, $letterOfAward),
                        'route'                    => route('letterOfAward.index', [ $letterOfAward->project->id ]),
                    ]);
                }
            }
        }

        return $pendingLetterOfAwards;
    }

    public function getLogs(LetterOfAward $letterOfAward)
    {
        $logs = $letterOfAward->logs;
        $formattedLogs = [];

        foreach($logs as $log)
        {
            $user          = $log->user;
            $type          = $log->getLogTypeByIdentifier($log->type_identifier);

            array_push($formattedLogs, [
                'user' => $user->name,
                'type' => $type,
                'date' => $log->updated_at,
            ]);
        }

        return $formattedLogs;
    }

    private function saveLog(LetterOfAward $letterOfAward, $identifier)
    {
        $log                     = new LetterOfAwardLog();
        $log->letter_of_award_id = $letterOfAward->id;
        $log->type_identifier    = $identifier;
        $log->user_id            = \Confide::user()->id;
        $log->save();
    }

    public function getHeaderHtml($contractDetails, $printSettings)
    {
        $headerStyle = file_get_contents('../app/views/letter_of_award/letterOfAward/print/header_layout_style.html');

        return str_replace(array( '<!--headerText-->', '<!--headerTextFontSize-->' ), [ trim(strip_tags($contractDetails)), $printSettings->header_font_size ], $headerStyle);
    }

    public function generatePdfOptions(LetterOfAwardPrintSetting $printSettings, $headerHeightInPixels)
    {
        $headerHeightInPixels = intval($headerHeightInPixels);
        $marginTop            = $printSettings->margin_top + $headerHeightInPixels / 4;
        $marginBottom         = $printSettings->margin_bottom;
        $marginLeft           = $printSettings->margin_left;
        $marginRight          = $printSettings->margin_right;

        $marginTopOption    = ' --margin-top ' . $marginTop;
        $marginBottomOption = ' --margin-bottom ' . $marginBottom;
        $marginLeftOption   = ' --margin-left ' . $marginLeft;
        $marginRightOption  = ' --margin-right ' . $marginRight;

        $headerSpacing = $printSettings->header_spacing;
        $headerOptions = ' --header-spacing ' . $headerSpacing;

        $footerOptions = ' --footer-font-size 10 --footer-right "Page [page] of [topage]"';

        return ' --encoding utf-8  --disable-smart-shrinking ' . $marginTopOption . $marginBottomOption . $marginRightOption . $marginLeftOption . $headerOptions . $footerOptions;
    }

    /**
     * checks if a user is a verifier for a particular round of approval
     * will return true if a verifier has verified, it's currently user's turn to verifier, or next in line
     */
    public function getUserHasPendingApprovalLetterOfAward(LetterOfAward $letterOfAward, User $user)
    {
        if(!Verifier::isBeingVerified($letterOfAward)) return false;

        if($letterOfAward->submitter->id == $user->id) return true;

        return Verifier::isAVerifier($user, $letterOfAward);
    }
}

