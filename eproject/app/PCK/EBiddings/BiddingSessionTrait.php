<?php namespace PCK\EBiddings;

use PCK\Projects\Project;

trait BiddingSessionTrait {

    public static function selectedContractorsQuery($companyId)
    {
        return EBidding::whereHas('project', function($query) use ($companyId) {
            $query->where('status_id', Project::STATUS_TYPE_E_BIDDING)
                ->where('e_bidding', true)
                ->where('status', EBidding::STATUS_APPROVED)
                ->whereHas('latestTender', function($query) use ($companyId) {
                    $query->whereHas('selectedFinalContractors', function($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    });
                });
        });
    }
}