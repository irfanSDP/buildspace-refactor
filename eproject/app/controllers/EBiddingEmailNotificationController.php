<?php

use PCK\EBiddings\EBiddingNotificationRepository;

class EBiddingEmailNotificationController extends \BaseController {

    protected $ebiddingNotificationRepository;

	public function __construct(
        EBiddingNotificationRepository $ebiddingNotificationRepository
    ) {
        $this->ebiddingNotificationRepository = $ebiddingNotificationRepository;
    }

	public function notify($eBiddingId) {
        $result = $this->ebiddingNotificationRepository->notify($eBiddingId);
        return \Response::json($result);
    }
}