<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\EBiddings\EBidding;
use PCK\EBiddings\EBiddingRepository;
use PCK\EBiddings\EBiddingConsoleRepository;
use PCK\Projects\Project;

class EBiddingFilters {

    private $user;
    private $ebiddingRepository;
    private $ebiddingConsoleRepository;

    public function __construct(
        EBiddingRepository $ebiddingRepository,
        EBiddingConsoleRepository $ebiddingConsoleRepository
    ) {
        $this->ebiddingRepository = $ebiddingRepository;
        $this->ebiddingConsoleRepository = $ebiddingConsoleRepository;
        $this->user = \Confide::user();
    }

    public function checkProjectEBiddingAccess(Route $route)
    {
        $project = $route->getParameter('projectId');
        if ( ! $project->e_bidding ) {
            throw new InvalidAccessLevelException(trans('eBidding.errorEBiddingNotEnabledForProject'));
        }
    }

    public function checkEBiddingPermission($eBiddingId)
    {
        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (!$ebidding) {
            return false;
        }
    }

    public function checkSessionListAccess(Route $route)
    {
        if ( ! self::checkSessionListPermission() )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkSessionListPermission()
    {
        return EBidding::selectedContractorsQuery($this->user->company->id)->exists();
    }

    public function checkConsoleAccess(Route $route)
    {
        if ( ! self::checkConsolePermission($route->getParameter('eBiddingId')) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkConsolePermission($eBiddingId)
    {
        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            return false;
        }
        $project = $ebidding->project;
        if (! $project) {
            return false;
        }
        $user = $this->user;
        $company = $user->company;

        // Check if the user is a bidder or a committee member
        $isBidder = $this->ebiddingConsoleRepository->isBidder([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
        ]);
        if (! $isBidder) {   // Not a bidder
            if (! $this->ebiddingConsoleRepository->isCommitteeMember([
                'projectId' => $project->id,
                'userId' => $user->id,
            ])) {   // Not a committee member
                return false;
            }
        }
        return true;
    }

    public function checkRankingListAccess(Route $route)
    {
        if ( ! self::checkRankingListPermission($route->getParameter('eBiddingId')) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkRankingListPermission($eBiddingId)
    {
        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            return false;
        }
        $project = $ebidding->project;
        if (! $project) {
            return false;
        }
        $user = $this->user;
        $company = $user->company;

        // Check if the user is a bidder or a committee member
        $isBidder = $this->ebiddingConsoleRepository->isBidder([
            'eBiddingId' => $eBiddingId,
            'companyId' => $company->id,
        ]);
        if (! $isBidder) {   // Not a bidder
            if (! $this->ebiddingConsoleRepository->isCommitteeMember([
                'projectId' => $project->id,
                'userId' => $user->id,
            ])) {   // Not a committee member
                return $this->checkProjectPermission($project); // Has access to the project ?
            }
        }
        return true;
    }

    public function checkBiddingHistoryAccess(Route $route)
    {
        if ( ! self::checkBiddingHistoryPermission($route->getParameter('eBiddingId')) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkBiddingHistoryPermission($eBiddingId)
    {
        return $this->checkRankingListPermission($eBiddingId);  // Same permission as ranking list
    }

    public function checkProjectPermission($project)
    {
        $user = $this->user;
        $repo = \App::make('PCK\Projects\ProjectRepository');

        if( $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR) && ( ! $project->contractor_access_enabled ) )
        {
            return false;
        }

        if( ! in_array($project->id, $repo->getVisibleProjectIds($user)) )
        {
            return false;
        }

        return true;
    }

    public function checkNotificationAccess(Route $route)
    {
        if (! self::checkNotificationPermission($route->getParameter('eBiddingId'))) {
            throw new InvalidAccessLevelException(trans(('filter.userPermissionDenied')));
        }
    }

    public function checkNotificationPermission($eBiddingId)
    {
        $ebidding = $this->ebiddingRepository->getById($eBiddingId);
        if (! $ebidding) {
            return false;
        }
        $project = $ebidding->project;
        if (! $project) {
            return false;
        }
        $user = $this->user;

        if (! $this->ebiddingConsoleRepository->isCommitteeMember([
            'projectId' => $project->id,
            'userId' => $user->id,
        ])) {   // Not a committee member
            return false;
        }

        return true;
    }

}