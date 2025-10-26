<?php namespace PCK\EBiddings;


class EBiddingSessionRepository
{
    public function getListForContractors($companyId)
    {
        return EBidding::selectedContractorsQuery($companyId)
            ->with('project.latestTender')
            ->get();
    }

}