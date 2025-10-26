<?php

use Carbon\Carbon;
use PCK\EBiddings\EBidding;
use PCK\EBiddings\EBiddingBid;
use PCK\EBiddings\EBiddingMode;
use PCK\EBiddings\EBiddingRepository;
use PCK\EBiddings\EBiddingConsoleRepository;
use PCK\EBiddings\EBiddingSessionRepository;
use PCK\EBiddings\EBiddingZoneRepository;

use PCK\Helpers\NumberHelper;

class EBiddingSessionController extends \BaseController {

    protected $ebiddingRepository;
    protected $ebiddingConsoleRepository;
    protected $ebiddingSessionRepository;
    protected $eBiddingZoneRepository;

	public function __construct(
        EBiddingRepository $ebiddingRepository,
        EBiddingConsoleRepository $ebiddingConsoleRepository,
        EBiddingSessionRepository $ebiddingSessionRepository,
        EBiddingZoneRepository $ebiddingZoneRepository
    ) {
        $this->ebiddingRepository = $ebiddingRepository;
        $this->ebiddingConsoleRepository = $ebiddingConsoleRepository;
        $this->ebiddingSessionRepository = $ebiddingSessionRepository;
        $this->eBiddingZoneRepository = $ebiddingZoneRepository;
    }

	public function index()
	{
		return View::make('e_bidding.session.index');
	}

    // Get the list of eBidding sessions
    public function getBidSessions()
    {
        $user = Confide::user();
        $company = $user->company;

        $records = $this->ebiddingSessionRepository->getListForContractors($company->id);   // Contractors only

        $data = [];

        foreach($records as $record) {
            $project = $record->project;

            // Create DateTime objects only if the values are not empty
            $previewStart = $record->biddingPreviewTime();
            $biddingStart = $record->biddingStartTime();

            // Calculate bidding end time if biddingStart is available
            $biddingEnd = $record->biddingEndTime();

            // Format the duration text
            $duration = $record->biddingDurationText();

            // Session status
            $statusText = $record->biddingSessionStatusText();

            $row = [
                'id' => $record->id,
                'reference' => $project->reference,
                'projectTitle' => $project->title,
                'previewDateTime' => ! empty($previewStart) ? $previewStart->format('d/m/Y g:i A') : null,
                'startDateTime' => ! empty($biddingStart) ? $biddingStart->format('d/m/Y g:i A') : null,
                'endDateTime' => ! empty($biddingEnd) ? $biddingEnd->format('d/m/Y g:i A') : null,
                'duration' => $duration,
                'status' => $statusText,
                'route:show' => route('e-bidding.console.show', [$record->id])
            ];

            $data[] = $row;
        }

        return \Response::json($data);
    }

    // Show the eBidding console
    public function show($eBiddingId)
    {
        $user = Confide::user();
        $company = $user->company;

        if (! $company) {   // Company not found
            \Flash::error(trans('errors.operationIsNotAllowed'));
            return Redirect::route('home.index');
        }

        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            \Flash::error(trans('errors.recordNotFound'));
            return Redirect::route('projects.index');
        }

        $project = $ebidding->project;
        if (! $project) {
            \Flash::error(trans('errors.recordNotFound'));
            return Redirect::route('projects.index');
        }

        // Check if the user is a bidder
        $isBidder = $this->ebiddingConsoleRepository->isBidder([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
        ]);
        if ($isBidder) {    // Is bidder
            $redirectOnError = route('e-bidding.sessions.index');    // On error -> Redirect to the eBidding session list
        } else {    // Not a bidder
            // Check if the user is a committee member
            $isCommitteeMember = $this->ebiddingConsoleRepository->isCommitteeMember([
                'projectId' => $project->id,
                'userId' => $user->id,
            ]);
            if ($isCommitteeMember) {   // Is committee member
                $redirectOnError = route('projects.e_bidding.index', ['project_id' => $project->id]);    // On error -> Redirect to eBidding summary
            } else {    // Not a bidder or committee member
                $redirectOnError = route('home.index');    // On error -> Redirect to home
            }
        }

        if ($ebidding->status !== EBidding::STATUS_APPROVED) {  // eBidding session is not approved
            \Flash::error(trans('errors.operationIsNotAllowed'));
            return Redirect::to($redirectOnError);
        }

        $now = Carbon::now();
        $previewStart = $ebidding->biddingPreviewTime();
        $biddingStart = $ebidding->biddingStartTime();
        $biddingEnd = $ebidding->biddingEndTime();
        $startOvertime = $ebidding->biddingStartOvertime();
        $overtimeStart = ! empty($startOvertime['total']) ? $biddingEnd->copy()->subSeconds($startOvertime['total']) : null;
        $startOvertimeText = $ebidding->biddingStartOvertimeText();
        $overtimePeriodText = $ebidding->biddingOvertimePeriodText();

        // Check if the bidding session start / end times are set
        if (empty($biddingStart || empty($biddingEnd))) {
            \Flash::error(trans('errors.anErrorHasOccurred'));
            return Redirect::to($redirectOnError);
        }
        // Check if the bidding session preview time has started
        if ($previewStart > $now) {
            \Flash::error(trans('eBiddingConsole.errorPreviewNotStarted'));
            return Redirect::to($redirectOnError);
        }

        // Determine the bidding session status
        $sessionStatus = $ebidding->biddingSessionStatus();

        // Currency symbol
        $currencySymbol = $project->modified_currency_code;

        // Bid parameters
        $bidMode = $ebidding->eBiddingMode;
        $bidParams = [];

        if ($isBidder) {    // Is bidder
            switch ($bidMode->slug) {
                case EBiddingMode::BID_MODE_ONCE:  // Zones -> Only allowed to bid once
                    //$lastBid = $this->ebiddingConsoleRepository->getLastBid($ebidding->id, $company->id);
                    //if (! $lastBid) {   // No last bid found -> Allowed to bid
                        $bidParams[] = [
                            'type' => 'C',
                            'amount' => null, // Custom amount
                            'url' => route('e-bidding.console.bid', [$ebidding->id, 'bid' => 'C']),
                            'confirmationMsg' => trans('eBiddingConsole.confirmationBidAmount'),
                        ];
                    //}
                    break;

                default:    // Others
                    if (! $ebidding->bid_decrement_percent) {
                        $bidParams[] = [
                            'type' => 'P',
                            'amount' => $ebidding->decrement_percent . '%',
                            'url' => route('e-bidding.console.bid', [$ebidding->id, 'bid' => 'P']),
                        ];
                    }
                    if (! $ebidding->bid_decrement_value) {
                        $bidParams[] = [
                            'type' => 'A',
                            'amount' => $currencySymbol . ' ' . NumberHelper::formatNumber($ebidding->decrement_value),
                            'url' => route('e-bidding.console.bid', [$ebidding->id, 'bid' => 'A']),
                        ];
                    }
                    if ($ebidding->enable_custom_bid_value) {
                        $bidParams[] = [
                            'type' => 'C',
                            'amount' => null, // Custom amount
                            'url' => route('e-bidding.console.bid', [$ebidding->id, 'bid' => 'C']),
                        ];
                    }
            }

            $showBudget = $ebidding->set_budget && !empty($ebidding->budget) && $ebidding->budget > 0 && $ebidding->show_budget_to_bidder;
        } else {    // Not bidder (is committee member or other)}
            $showBudget = $ebidding->set_budget && !empty($ebidding->budget) && $ebidding->budget > 0;
        }

        // Bid history title
        $bidHistoryTitle = trans('eBiddingConsole.history') .' ('. trans('eBiddingConsole.latestBids') .')';

        return \View::make('e_bidding.console.show', compact(
                'ebidding',
                'currencySymbol',
                'bidParams',
                'isBidder',
                'bidHistoryTitle',
                'bidMode',
                'showBudget',
                'now',
                'previewStart',
                'biddingStart',
                'biddingEnd',
                'startOvertimeText',
                'overtimeStart',
                'overtimePeriodText',
                'sessionStatus'
            )
        );
    }

    public function getCountdown($eBiddingId)
    {
        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            return \Response::json([
                'biddingEndDisplay' => null,
                'biddingEndIso'     => null,
                'ended'             => true,   // no record → treat as ended
            ]);
        }
        $biddingEnd = $ebidding->biddingEndTime(); // Carbon|null
        $now        = \Carbon\Carbon::now();

        $settleSec  = 2; // Server-side settle window (seconds)

        $endIso     = null;
        $endDisplay = null;

        if ($biddingEnd) {  // If bidding end time is available...
            // Use 'c' (ISO 8601) for max compatibility on old Carbon/PHP
            $endIso     = $biddingEnd->format('c');               // e.g. 2025-09-24T17:30:00+08:00
            $endDisplay = $biddingEnd->format('d/m/Y g:i:s A');   // pretty label
            $ended      = $now->gt($biddingEnd->copy()->addSeconds($settleSec));
        } else {
            $ended = true;  // no end time -> treat as ended
        }

        return \Response::json([
            'biddingEndDisplay' => $endDisplay,
            'biddingEndIso'     => $endIso,
            'ended'             => $ended,
        ]);
    }

    public function getBidRankings($eBiddingId)
    {
        $data = [];

        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            return \Response::json($data);
        }
        $project = $ebidding->project;
        if (! $project) {
            return \Response::json($data);
        }
        $user = Confide::user();
        $company = $user->company;

        if (! $company) {   // Company not found
            return \Response::json($data);
        }

        $isBidder = $this->ebiddingConsoleRepository->isBidder([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
        ]); // Check if the user is a bidder

        /*$isCommitteeMember = $this->ebiddingConsoleRepository->isCommitteeMember([
            'projectId' => $project->id,
            'userId' => $user->id,
        ]); // Check if the user is a committee member
        */

        $currencySymbol = $project->modified_currency_code;   // Currency symbol
        $budget = ($ebidding->set_budget && !empty($ebidding->budget) && $ebidding->budget > 0) ? $ebidding->budget : null;    // Budget

        if (empty($ebidding->lowest_tender_amount)) { // Get and update the lowest tender amount if it's empty
            $updateLowestTenderAmount = $this->ebiddingConsoleRepository->updateLowestTenderAmount($ebidding);
            if (! $updateLowestTenderAmount['success']) {
                return \Response::json($data);
            }
            $lowestTenderAmount = $updateLowestTenderAmount['data'];
        } else {    // Get the lowest tender amount
            $lowestTenderAmount = $ebidding->lowest_tender_amount;
        }

        $bidMode = $ebidding->eBiddingMode; // Bid mode

        $rankings = $this->ebiddingConsoleRepository->getRankings($ebidding->id);

        foreach($rankings as $key => $ranking) {
            $row = [];
            $rank = $key + 1;    // Rank (Top = 1)

            if ($ebidding->enable_zones) { // Zones
                if ($isBidder && $ebidding->hide_other_bidder_info && $company->id !== $ranking->company_id) {
                    continue;   // Skip if the user is not the bidder and 'hide_other_bidder_info' is true
                }

                $zone = $this->eBiddingZoneRepository->getBidZone($ebidding->id, $ranking->bid_amount);
                $row['zone'] = $zone ? $this->eBiddingZoneRepository->validHexColor($zone->colour) : null; // Get the zone colour
            }

            $row['bidAmount'] = $currencySymbol . ' ' . NumberHelper::formatNumber($ranking->bid_amount);

            if (! $isBidder) {  // Not bidder (is committee member or other)
                $row['companyName'] = $ranking->company->name;
            } else {    // Is bidder
                if ($company->id === $ranking->company_id) {    // User is the bidder
                    $row['companyName'] = trans('eBiddingConsole.myCompany');    // Show "Me" instead of the company name
                } else {    // User is not the bidder
                    $row['companyName'] = trans('eBiddingConsole.anonymousBidder');
                }

                switch ($bidMode->slug) {
                    case EBiddingMode::BID_MODE_DECREMENT: // Decrease
                    case EBiddingMode::BID_MODE_INCREMENT: // Increase
                        if ($rank > 1) {     // Not 1st place
                            $row['bidAmount'] = ''; // Hide the bid amount
                        }
                        break;

                    default:    // Other modes / Zones
                        if ($ebidding->enable_zones && $company->id !== $ranking->company_id) { // Zones and user is not the bidder
                            $row['bidAmount'] = ''; // Hide the bid amount
                        }
                }
            }

            //if (! $ebidding->enable_zones) { // Not Zones
                if (! empty($budget) && (! $isBidder || $ebidding->show_budget_to_bidder)) {
                    // Calculate the difference between the budget and the bid amount
                    if ($ranking->bid_amount < $budget) {
                        // Savings: Bid amount is lower than the budget
                        $diffWithBudget = $budget - $ranking->bid_amount;
                        $row['diffWithBudget'] = '- ' . $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithBudget);

                        // Percentage calculation (commented out)
                        // $percentWithBudget = ($diffWithBudget / $budget) * 100;
                        // $row['diffWithBudget'] = '- ' . $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithBudget) . ' (' . NumberHelper::formatNumber($percentWithBudget) . '%)';
                    } else {
                        $diffWithBudget = $ranking->bid_amount - $budget;
                        if ($ranking->bid_amount > $budget) {   // Over budget: Bid amount is higher than the budget
                            $row['diffWithBudget'] = '+ ';
                        } else {                                // Equal to budget
                            $row['diffWithBudget'] = '';
                        }
                        $row['diffWithBudget'] .= $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithBudget);

                        // Percentage calculation (commented out)
                        // $percentWithBudget = ($diffWithBudget / $budget) * 100;
                        // $row['diffWithBudget'] = '+ ' . $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithBudget) . ' (' . NumberHelper::formatNumber($percentWithBudget) . '%)';
                    }
                }

                if (!$isBidder && !empty($lowestTenderAmount)) {
                    // Calculate the difference between the lowest tender amount and the bid amount
                    if ($ranking->bid_amount < $lowestTenderAmount) {
                        // Savings: Bid amount is lower than the lowest tender amount
                        $diffWithLowestTender = $lowestTenderAmount - $ranking->bid_amount;
                        $row['diffWithLowestTender'] = '- ' . $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithLowestTender);

                        // Percentage calculation (commented out)
                        // $percentWithLowestTender = ($diffWithLowestTender / $lowestTenderAmount) * 100;
                        // $row['diffWithLowestTender'] = '- ' . $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithLowestTender) . ' (' . NumberHelper::formatNumber($percentWithLowestTender) . '%)';
                    } else {
                        // Over the lowest tender amount
                        $diffWithLowestTender = $ranking->bid_amount - $lowestTenderAmount;
                        if ($ranking->bid_amount > $lowestTenderAmount) {   // Over the lowest tender amount
                            $row['diffWithLowestTender'] = '+ ';
                        } else {                                // Equal to the lowest tender amount
                            $row['diffWithLowestTender'] = '';
                        }
                        $row['diffWithLowestTender'] .= $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithLowestTender);

                        // Percentage calculation (commented out)
                        // $percentWithLowestTender = ($diffWithLowestTender / $lowestTenderAmount) * 100;
                        // $row['diffWithLowestTender'] = '+ ' . $currencySymbol . ' ' . NumberHelper::formatNumber($diffWithLowestTender) . ' (' . NumberHelper::formatNumber($percentWithLowestTender) . '%)';
                    }
                }
            //}

            $data[] = $row;
        }

        return \Response::json($data);
    }

    public function getBidHistory($eBiddingId)
    {
        $data = [];

        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            return \Response::json($data);
        }
        $project = $ebidding->project;
        if (! $project) {
            return \Response::json($data);
        }
        $user = Confide::user();
        $company = $user->company;

        if (! $company) {   // Company not found
            return \Response::json($data);
        }

        $isBidder = $this->ebiddingConsoleRepository->isBidder([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
        ]); // Check if the user is a bidder

        /*$isCommitteeMember = $this->ebiddingConsoleRepository->isCommitteeMember([
            'projectId' => $project->id,
            'userId' => $user->id,
        ]); // Check if the user is a committee member
        */

        $currencySymbol = $project->modified_currency_code;   // Currency symbol

        $input = Input::all();
        if (! empty($input['limit'])) {
            $limit = $input['limit'];
        } else {
            $limit = null;
        }

        $bidMode = $ebidding->eBiddingMode; // Bid mode
        $companyId = null;  // Default -> Show bids from all companies

        if ($isBidder) {    // Is bidder (not committee member)
            // Currently, 'hide_other_bidder_info' option is only applicable if Zones is enabled
            if ($ebidding->hide_other_bidder_info && $ebidding->enable_zones) {
                $companyId = $company->id;
            }
        }

        $history = $this->ebiddingConsoleRepository->getBidHistory($ebidding->id, $companyId, $limit);

        foreach($history as $bid) {
            $row = [];
            $row['dateTime'] = $bid->created_at->format('d/m/Y g:i:s A');
            //$row['bidType'] = $bid->bid_type;
            //$row['bidTypeLabel'] = EBiddingBid::getTypeLabel($bid->bid_type);
            //$row['durationExtended'] = $bid->duration_extended > 0 ? $bid->duration_extended . ' ' . trans('time.minutes') : 'N/A';
            $row['bidAmount'] = $currencySymbol . ' ' . NumberHelper::formatNumber($bid->bid_amount);

            // Lowest tender amount difference
            if ($ebidding->lowest_tender_amount > $bid->bid_amount) { // Bid amount is lower than the lowest tender amount
                $lowestTenderDiffAmount = $ebidding->lowest_tender_amount - $bid->bid_amount;
                $lowestTenderDiffSymbol = '- ';
            } elseif ($ebidding->lowest_tender_amount === $bid->bid_amount) { // Bid amount is equal to the lowest tender amount
                $lowestTenderDiffAmount = 0; // No difference
                $lowestTenderDiffSymbol = ''; // No symbol
            } else { // Bid amount is higher than the lowest tender amount
                $lowestTenderDiffAmount = $bid->bid_amount - $ebidding->lowest_tender_amount;
                $lowestTenderDiffSymbol = '+ ';
            }
            $row['lowestTenderDiff'] = $lowestTenderDiffSymbol . $currencySymbol . ' ' . NumberHelper::formatNumber($lowestTenderDiffAmount);

            // Lowest/highest bid amount difference
            $row['lowestBidDiff'] = '';
            if ($bidMode->slug === EBiddingMode::BID_MODE_ONCE) { // Zones -> Only allowed to bid once
                switch ($bid->direction) {
                    case EBiddingBid::BID_DIRECTION_INCREASE:
                        $directionSymbol = '+ ';
                        break;

                    case EBiddingBid::BID_DIRECTION_DECREASE:
                        $directionSymbol = '- ';
                        break;

                    default:
                        $directionSymbol = ''; // None
                }
                $row['lowestBidDiff'] .= $directionSymbol;
            }

            switch ($bid->bid_type) {
                case EBiddingBid::BID_TYPE_PERCENTAGE:
                    $row['lowestBidDiff'] .= $bid->decrement_percent . '%';
                    break;

                case EBiddingBid::BID_TYPE_AMOUNT:
                    $row['lowestBidDiff'] .= $currencySymbol . ' ' . NumberHelper::formatNumber($bid->decrement_value);
                    break;

                default: // Other types / Custom
                    $row['lowestBidDiff'] .= $currencySymbol . ' ' . NumberHelper::formatNumber($bid->decrement_amount);
            }

            if (! $isBidder) {  // Not bidder (is committee member or other)
                $row['companyName'] = $bid->company->name;
            } else {    // Is bidder
                if ($company->id === $bid->company_id) {    // User is the bidder
                    $row['companyName'] = trans('eBiddingConsole.myCompany');    // Show "Me" instead of the company name
                } else {    // User is not the bidder
                    $row['companyName'] = trans('eBiddingConsole.anonymousBidder');    // Hide the company name

                    if ($ebidding->enable_zones) { // Zones
                        // Hide amounts
                        $row['lowestTenderDiff'] = '';  // Lowest tender amount difference
                        $row['lowestBidDiff'] = '';     // Lowest bid amount difference
                        $row['bidAmount'] = '';         // Bid amount
                    }
                }
            }

            $data[] = $row;
        }

        return \Response::json($data);
    }

    public function bid($eBiddingId)
    {
        $input = Input::all();
        $user = Confide::user();
        $company = $user->company;
        if (! $company) {
            return Response::json([
                'success' => false,
                'message' => trans('errors.operationIsNotAllowed'),
            ]);
        }
        if (empty($input['bid'])) {
            return Response::json([
                'success' => false,
                'message' => trans('errors.anErrorHasOccurred'),
            ]);
        }

        $customBidAmount = null;

        switch ($input['bid']) {
            case 'P':   // Percentage (%)
                $bidType = EBiddingBid::BID_TYPE_PERCENTAGE;
                break;
            case 'A':   // Fixed amount ($)
                $bidType = EBiddingBid::BID_TYPE_AMOUNT;
                break;
            case 'C':   // Custom input amount ($)
                if (empty($input['bid_amount']) || ! is_numeric($input['bid_amount'])) {
                    return Response::json([
                        'success' => false,
                        'message' => trans('errors.anErrorHasOccurred'),
                    ]);
                }
                $bidType = EBiddingBid::BID_TYPE_CUSTOM;
                $customBidAmount = (float) $input['bid_amount'];
                break;
            default:
                return Response::json([
                    'success' => false,
                    'message' => trans('errors.anErrorHasOccurred'),
                ]);
        }

        $result = $this->ebiddingConsoleRepository->bid([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
            'bidType' => $bidType,
            'customBidAmount' => $customBidAmount,
        ]);

        return Response::json($result);
    }

    public function getBidLegend($eBiddingId) {
        $user = Confide::user();
        $company = $user->company;

        $isBidder = $this->ebiddingConsoleRepository->isBidder([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
        ]); // Check if the user is a bidder

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $data = $this->eBiddingZoneRepository->getBidZones($eBiddingId, [
            'page' => $page,
            'limit' => $limit,
            'isBidder' => $isBidder,
        ]);

        return Response::json($data);
    }
}