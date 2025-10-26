<?php namespace PCK\SystemEvents;

use PCK\Tenders\Tender;
use PCK\Tenders\TenderRepository;

class TenderEvents {

	private $tenderRepo;

	public function __construct(TenderRepository $tenderRepo)
	{
		$this->tenderRepo = $tenderRepo;
	}

	public function updateTenderStatus(Tender $tender, $status)
	{
		$this->tenderRepo->updatedFormTypeStatus($tender, $status);
	}

	public function updateTechnicalEvaluationStatus(Tender $tender)
	{
		$this->tenderRepo->updateTechnicalEvaluationStatus($tender);
	}

}