<?php namespace PCK\EBiddings;

use Carbon\Carbon;
use PCK\EBiddingCommittees\EBiddingCommittee;
use PCK\Users\User;

class EBiddingNotificationRepository
{
    protected $ebiddingRepository;
    protected $ebiddingConsoleRepository;

    public function __construct(
        EBiddingRepository $ebiddingRepository,
        EBiddingConsoleRepository $ebiddingConsoleRepository
    ) {
        $this->ebiddingRepository = $ebiddingRepository;
        $this->ebiddingConsoleRepository = $ebiddingConsoleRepository;
    }

    private function send($recipients, $linkHtml, $data, $queue, $view, $subject) {
        foreach ($recipients as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            $viewData = $data;
            $viewData['linkHtml'] = $linkHtml;
            $email = $user->email;

            \Mail::queueOn($queue, $view, $viewData, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });
        }
    }

    public function notify($eBiddingId) {
        $result = [
            'success' => false,
        ];

        $eBidding = $this->ebiddingRepository->getById($eBiddingId);

        if (! $eBidding) {
            $result['message'] = trans('errors.recordNotFound');
            return $result;
        }

        $project = $eBidding->project;
        if (! $project) {
            $result['message'] = trans('errors.recordNotFound');
            return $result;
        }

        $currencySymbol = $project->modified_currency_code; // Currency symbol

        $recipients = ['committees' => [], 'bidders' => []];

        // Fetch eBidding committees for the project
        $eBiddingCommittees = EBiddingCommittee::where('project_id', $eBidding->project_id)->where('is_committee', true)->get();

        // Recipients: Committee members
        foreach ($eBiddingCommittees as $eBiddingCommittee) {
            if (! in_array($eBiddingCommittee->user_id, $recipients)) {
                // Add committee member as recipient if not already added
                $recipients['committees'][] = $eBiddingCommittee->user_id;
            }
        }

        // Recipients: Contractors (admins)
        $contractors = $this->ebiddingConsoleRepository->getRankings($eBidding->id);
        foreach ($contractors as $contractor) {
            // Get admins of the contractor's company
            $contractorAdmins = User::where('company_id', $contractor->company_id)->where('is_admin', true)->select('id')->get();

            foreach ($contractorAdmins as $contractorAdmin) {
                if (! in_array($contractorAdmin->id, $recipients)) {
                    // Add contractor admin as recipient if not already added
                    $recipients['bidders'][] = $contractorAdmin->id;
                }
            }
        }

        // Check if there are no recipients
        if (empty($recipients['committees']) && empty($recipients['bidders'])) {
            $result['message'] = trans('eBiddingNotify.noRecipients');
            return $result;
        }

        // Now send the email
        $queue = 'default';
        $view = 'e_bidding.notification.email.notify';
        $subject = trans('eBiddingNotify.subject');

        $bidMode = $eBidding->eBiddingMode;

        $bidDecrementLabel = ($bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_DECREMENT) ? trans('eBidding.bidDecrement') : trans('eBidding.bidIncrement');
        $bidDecrementPercLabel = $bidDecrementLabel . ' (%)';
        $bidDecrementAmountLabel = $bidDecrementLabel . ' (' . $currencySymbol . ')';

        $data = [
            'bidMode' => $bidMode->slug,
            'projectName' => $project->title,
            'previewStartTimeFormat' => $eBidding->biddingPreviewStartTimeText(),
            'biddingStartTimeFormat' => $eBidding->biddingStartTimeText(),
            'biddingEndTimeFormat' => $eBidding->biddingEndTimeText(),
            'biddingDuration' => $eBidding->biddingDurationText(true),
            'biddingStartOvertime' => $eBidding->biddingStartOvertimeText(true),
            'biddingOvertimePeriod' => $eBidding->biddingOvertimePeriodText(true),
            'budgetAmount' => $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($eBidding->budget),
            'bidDecrementPercLabel' => $bidDecrementPercLabel,
            'bidDecrementAmountLabel' => $bidDecrementAmountLabel,
            'bidDecrementPerc' => (! $eBidding->bid_decrement_percent) ? $eBidding->decrement_percent . '%' : trans('eBidding.not_applicable'),
            'bidDecrementAmount' => (! $eBidding->bid_decrement_value) ? $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($eBidding->decrement_value) : trans('eBidding.not_applicable'),
        ];

        $bidderLinkHtml = link_to_route('e-bidding.console.show', trans('eBiddingNotify.linkDescriptionBidder'), ['eBiddingId' => $eBidding->id]);
        $committeeLinkHtml = link_to_route('projects.e_bidding.index', trans('eBiddingNotify.linkDescriptionCommittee'), ['project_id' => $eBidding->project_id]);

        // Send to committees
        if (count($recipients['committees']) > 0) {
            $data['budgetEnabled'] = $eBidding->set_budget && $eBidding->budget > 0;
            $this->send($recipients['committees'], $committeeLinkHtml, $data, $queue, $view, $subject);
        }

        // Send to bidders
        if (count($recipients['bidders']) > 0) {
            $data['budgetEnabled'] = $eBidding->set_budget && $eBidding->budget > 0 && $eBidding->show_budget_to_bidders;
            $this->send($recipients['bidders'], $bidderLinkHtml, $data, $queue, $view, $subject);
        }

        $result['success'] = true;
        $result['message'] = trans('eBiddingNotify.notifySent');
        return $result;
    }
}