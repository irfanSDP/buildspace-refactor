<?php namespace PCK\EBiddings;

use Carbon\Carbon;
use PCK\Helpers\NumberHelper;
use PCK\EBiddingCommittees\EBiddingCommittee;
use PCK\Tenders\TenderRepository;

class EBiddingConsoleRepository
{
    protected $tenderRepository;

    public function __construct(TenderRepository $tenderRepository)
    {
        $this->tenderRepository = $tenderRepository;
    }

    public function isCommitteeMember($data)
    {
        return EBiddingCommittee::where('project_id', $data['projectId'])->where('user_id', $data['userId'])->where('is_committee', true)->exists();
    }

    public function isBidder($data)
    {
        return EBiddingRanking::where('e_bidding_id', $data['eBiddingId'])->where('company_id', $data['companyId'])->exists();
    }

    public function extendBiddingEndTime($ebidding)
    {
        $data = [
            'minutes' => 0,
            'seconds' => 0,
            'extended' => false,
        ];
        $biddingEnd = $ebidding->biddingEndTime();
        if (empty($biddingEnd)) {
            return $data;
        }

        $now = Carbon::now();
        $startOvertime = ($ebidding->start_overtime * 60) + $ebidding->start_overtime_seconds;  // Start overtime (In seconds)
        $overtime = ($ebidding->overtime_period * 60) + $ebidding->overtime_seconds;            // Overtime (In seconds)

        if ($startOvertime > 0 && $overtime > 0 && $now->diffInSeconds($biddingEnd) <= $startOvertime) {
            // Extend bidding end time
            $data['minutes'] = $ebidding->overtime_period;  // Minutes
            $data['seconds'] = $ebidding->overtime_seconds; // Seconds

            $ebidding->duration_extended = $ebidding->duration_extended + $data['minutes'];   // Minutes
            $ebidding->extended_seconds = $ebidding->extended_seconds + $data['seconds'];     // Seconds
            $ebidding->save();

            $data['extended'] = true; // Extended
        }

        return $data;
    }

    /**
     * Place a bid for the eBidding
     *
     * Note:
     * Originally, bidding was only allowed to decrement the amount.
     * However, now there are two types of bid modes where bid amount can be incremented or decremented.
     * The old columns with 'decrement' in the name are also re-used for incrementing values.
     *
     * @param array $data
     * @return array
     */
    public function bid($data)
    {
        $result = [
            'success' => false,
            'message' => '',
            'stopBidding' => false,
        ];
        $ebidding = EBidding::find($data['eBiddingId']);
        if (! $ebidding) {
            $result['message'] = trans('errors.recordNotFound');
            return $result; // Record not found
        }

        if (! $this->isBidder([
            'eBiddingId' => $data['eBiddingId'],
            'companyId' => $data['companyId'],
        ])) {
            $result['message'] = trans('errors.operationIsNotAllowed');
            return $result;  // Not a bidder
        }

        if ($ebidding->biddingStartTime() > Carbon::now()) {
            $result['message'] = trans('eBiddingConsole.errorBiddingNotStarted');
            return $result;   // Bidding has not started
        }
        if ($ebidding->biddingEndTime() < Carbon::now()) {
            $result['stopBidding'] = true;
            $result['message'] = trans('eBiddingConsole.errorBiddingEnded');
            return $result;   // Bidding has not started
        }

        $bidderPreviousBidAmount = 0;   // Default previous bid amount to zero
        $bidderPreviousBid = $this->getLastBid($ebidding->id, $data['companyId']);
        if ($bidderPreviousBid) {   // Has previous bid
            // Bidder's current bid was placed within cooldown period
            if ($bidderPreviousBid->created_at->diffInSeconds(Carbon::now()) <= EBiddingBid::BID_COOLDOWN) {
                $result['message'] = trans('eBiddingConsole.bidOnCooldown');
                return $result;
            }
            // Update bidder's previous bid amount
            $bidderPreviousBidAmount = $bidderPreviousBid->bid_amount;
        }

        $lowestBid = $this->getLowestBid($ebidding->id);
        if (! $lowestBid['success']) {
            $result['message'] = $lowestBid['message'];
            return $result;   // No bid amount
        }
        $lastBidAmount = $lowestBid['data']->bid_amount;    // This is the highest/lowest bid amount depending on the eBidding mode

        if ($lastBidAmount <= 0) {
            $result['message'] = trans('errors.anErrorHasOccurred');
            return $result;   // No bid amount
        }

        // Bid type
        switch ($data['bidType']) {
            case EBiddingBid::BID_TYPE_PERCENTAGE:  // Percentage
                if ($ebidding->bid_decrement_percent) {
                    $result['message'] = trans('errors.anErrorHasOccurred');
                    return $result;   // Percentage is disabled
                }
                $changeAmount = $lastBidAmount * ($ebidding->decrement_percent / 100);
                break;

            case EBiddingBid::BID_TYPE_AMOUNT:  // Amount
                if ($ebidding->bid_decrement_value) {
                    $result['message'] = trans('errors.anErrorHasOccurred');
                    return $result;   // Amount is disabled
                }
                $changeAmount = $ebidding->decrement_value;
                break;

            case EBiddingBid::BID_TYPE_CUSTOM:  // Custom amount
                if (! $ebidding->enable_custom_bid_value) {
                    $result['message'] = trans('errors.anErrorHasOccurred');
                    return $result;   // Custom amount is disabled
                }
                $changeAmount = $data['customBidAmount'];
                break;

            default:
                $result['message'] = trans('errors.anErrorHasOccurred');
                return $result;   // Invalid bid type
        }

        $bidMode = $ebidding->eBiddingMode;

        switch ($bidMode->slug) {
            case EBiddingMode::BID_MODE_INCREMENT:  // Increment
                $bidAmount = $lastBidAmount + $changeAmount;
                $direction = EBiddingBid::BID_DIRECTION_INCREASE; // Increment
                break;

            case EBiddingMode::BID_MODE_ONCE:  // Custom amount mode
                $bidAmount = $changeAmount;
                if ($ebidding->enable_zones) {  // Zones enabled -> Bids must be less than their previous bid
                    // Prevent bidder from bidding with higher amount than their previous bid
                    if ($bidderPreviousBidAmount > 0 && $bidAmount > $bidderPreviousBidAmount) {
                        $result['message'] = trans('eBiddingConsole.errorNotAllowedToIncreaseBidAmount');
                        return $result;
                    }
                }
                if ($bidAmount > $lastBidAmount) {   // Increment
                    $changeAmount = $bidAmount - $lastBidAmount;
                    $direction = EBiddingBid::BID_DIRECTION_INCREASE;
                } else if ($bidAmount < $lastBidAmount) {    // Decrement
                    $changeAmount = $lastBidAmount - $bidAmount;
                    $direction = EBiddingBid::BID_DIRECTION_DECREASE;
                } else {    // No change
                    $bidAmount = $lastBidAmount;
                    $changeAmount = 0;
                    $direction = EBiddingBid::BID_DIRECTION_NONE;
                }
                break;

            default:    // Decrement
                // Prevent from going negative
                if (($lastBidAmount - $changeAmount) < 0) {
                    $result['message'] = trans('eBiddingConsole.errorBiddingNotAllowed');
                    return $result;   // Prevent negative bid amount
                }
                $bidAmount = max($lastBidAmount - $changeAmount, 0);
                $direction = EBiddingBid::BID_DIRECTION_DECREASE; // Decrement
        }

        // If is custom bid amount or custom amount mode
        if ($data['bidType'] === EBiddingBid::BID_TYPE_CUSTOM || $bidMode->slug === EBiddingMode::BID_MODE_ONCE) {
            // Compared to last bid amount
            if ($bidMode->slug === EBiddingMode::BID_MODE_ONCE) {
                $bidAmountDiff = NumberHelper::amountDifference($bidAmount, $bidderPreviousBidAmount);    // Own bid only
            } else {
                $bidAmountDiff = NumberHelper::amountDifference($bidAmount, $lastBidAmount);    // Includes other bidders' bid
            }
            $minBidAmountDiff = $ebidding->min_bid_amount_diff; // Minimum bid amount difference threshold

            if ($minBidAmountDiff > 0 && $bidAmountDiff < $minBidAmountDiff) {   // Amount difference must exceed the threshold
                $result['message'] = trans('eBiddingConsole.errorBidAmountDifference');
                return $result;
            }
        }

        // No tie bid?
        if (in_array($bidMode->slug, [EBiddingMode::BID_MODE_DECREMENT, EBiddingMode::BID_MODE_INCREMENT])) {
            $noTieBid = true;
        } else {
            $noTieBid = $ebidding->enable_no_tie_bid;
        }
        if ($noTieBid) { // "No tie bid" enabled
            // Prevent same bid amount as other bidders (if any)
            if (EBiddingRanking::where('e_bidding_id', $ebidding->id)
                ->where('bid_amount', $bidAmount)
                ->exists())
            {
                $result['message'] = trans('eBiddingConsole.errorSameBidAmount');
                return $result;   // Bid with same amount already exists
            }
        }

        // Insert bid
        $bid = EBiddingBid::create([
            'e_bidding_id' => $data['eBiddingId'],
            'company_id' => $data['companyId'],
            'decrement_percent' => $ebidding->decrement_percent,
            'decrement_value' => $ebidding->decrement_value,
            'decrement_amount' => $changeAmount,
            'bid_amount' => $bidAmount,
            'bid_type' => $data['bidType'],
            'direction' => $direction,
        ]);
        if (! $bid) {
            $result['message'] = trans('errors.anErrorHasOccurred');
            return $result;   // Failed to insert bid
        }

        // Update ranking (increase/decrease bid amount)
        $this->updateRanking([
            'eBiddingId' => $bid->e_bidding_id,
            'companyId' => $bid->company_id,
            'bidAmount' => $bid->bid_amount,
        ]);

        $firstRank = $this->getLowestBid($ebidding->id);
        if ($firstRank['data']->company_id === $bid->company_id) {  // Bidder is now 1st rank
            // Extend bidding time?
            $extendedDuration = $this->extendBiddingEndTime($ebidding); // Checks and extends bidding session (if bid is placed within set time)
            if ($extendedDuration['extended']) {
                $bid->duration_extended = $extendedDuration['minutes']; // Minutes
                $bid->extended_seconds = $extendedDuration['seconds'];  // Seconds
                $bid->save();
            }
        }

        // Stop bidding?
        /*if ($bidMode->slug === EBiddingMode::BID_MODE_ONCE) {  // Once mode -> Only one bid allowed per company
            $result['stopBidding'] = true;
        }*/

        // Result
        $result['success'] = true;
        $result['message'] = trans('eBiddingConsole.bidSuccess');
        return $result;
    }

    public function getBidHistory($eBiddingId, $companyId=null, $limit=null)
    {
        $query = EBiddingBid::where('e_bidding_id', $eBiddingId);
        if (! empty($companyId)) {
            $query->where('company_id', $companyId);
        }
        $query->orderBy('created_at', 'desc');

        if (! empty($limit)) {
            $query->limit($limit);
            if ($limit === 1) {
                return $query->first();
            }
        }
        return $query->get();
    }

    public function getLastBid($eBiddingId, $companyId)
    {
        return $this->getBidHistory($eBiddingId, $companyId, 1);
    }

    // This should only be called on ebidding store/update
    public function initRankings($ebidding, $selectedTenderRates=null)
    {
        $result = ['success' => false, 'message' => ''];

        if (! $ebidding) {
            $result['message'] = trans('errors.recordNotFound');
            return $result;
        }
        $project = $ebidding->project;

        if (empty($selectedTenderRates)) {
            $selectedTenderRates = $this->tenderRepository->getSelectedSubmittedTenderRates($project);
            if (! $selectedTenderRates['success']) {
                $result['message'] = $selectedTenderRates['message'];
                return $result;
            }
        }

        foreach ($selectedTenderRates['data'] as $tenderRate) {
            $this->updateRanking([
                'eBiddingId' => $ebidding->id,
                'companyId' => $tenderRate['companyId'],
                'bidAmount' => $tenderRate['tenderAmount'],
            ]);
        }
        $result['success'] = true;
        return $result;
    }

    public function updateRanking($data)
    {
        $ranking = EBiddingRanking::where('e_bidding_id', $data['eBiddingId'])->where('company_id', $data['companyId'])->first();
        if (! $ranking) {   // Insert new ranking if not exists
            EBiddingRanking::create([
                'e_bidding_id' => $data['eBiddingId'],
                'company_id' => $data['companyId'],
                'bid_amount' => $data['bidAmount'],
            ]);
        } else {    // Update existing ranking
            $ranking->bid_amount = $data['bidAmount'];
            $ranking->save();

            /*if (! empty($data['decrementAmount'])) {    // Decrease bid amount
                $decrementAmount = $data['decrementAmount'];

                $ranking->bid_amount = \DB::raw("GREATEST(bid_amount - $decrementAmount, 0)");
                $ranking->save();
            } else {    // Update bid amount
                $ranking->bid_amount = $data['bidAmount'];
                $ranking->save();
            }*/
        }
        return true;
    }

    // This will return the rankings for the eBidding
    // Note: Sort order depends on the eBidding mode
    public function getRankings($eBiddingId, $first = false)
    {
        $ebidding = EBidding::find($eBiddingId);
        if (! $ebidding) {
            return null; // EBidding not found
        }

        switch ($ebidding->eBiddingMode->slug) {
            case EBiddingMode::BID_MODE_INCREMENT:
                $sortOrder = 'desc'; // Increment mode -> Rank: Highest to lowest
                break;

            default:
                $sortOrder = 'asc'; // Decrement mode -> Rank: Lowest to highest
        }

        $rankings = EBiddingRanking::where('e_bidding_id', $eBiddingId)->orderBy('bid_amount', $sortOrder);
        if ($first) {   // Get lowest/highest
            return $rankings->first();
        } else {    // Get all
            return $rankings->get();
        }
    }

    // Get the lowest/highest bid for the eBidding
    public function getLowestBid($ebiddingId)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => null,
        ];

        $firstRank = $this->getRankings($ebiddingId, true);
        if (! $firstRank) {
            $initRankings = $this->initRankings(EBidding::find($ebiddingId));
            if (! $initRankings['success']) {
                $result['message'] = $initRankings['message'];
                return $result;
            }
            $firstRank = $this->getRankings($ebiddingId, true);
        }

        $result['success'] = true;
        $result['data'] = $firstRank;
        return $result;
    }

    // Update the lowest/highest tender amount for the eBidding
    public function updateLowestTenderAmount($ebidding)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => null,
        ];

        $lowestBid = $this->getLowestBid($ebidding->id);
        if (! $lowestBid['success']) {
            $result['message'] = $lowestBid['message'];
            return $result;
        }
        $ebidding->lowest_tender_amount = $lowestBid['data']->bid_amount;
        $ebidding->save();

        $result['success'] = true;
        $result['data'] = $lowestBid['data']->bid_amount;
        return $result;
    }
}