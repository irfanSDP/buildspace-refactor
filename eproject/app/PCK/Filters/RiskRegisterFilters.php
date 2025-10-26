<?php namespace PCK\Filters;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\RiskRegister\RiskRegister;
use PCK\RiskRegister\RiskRegisterMessage;
use PCK\RiskRegister\RiskRegisterRepository;

class RiskRegisterFilters {

    private $request;
    private $repository;

    public function __construct(Request $request, RiskRegisterRepository $repository)
    {
        $this->request    = $request;
        $this->repository = $repository;
    }

    public function canPostRisk(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! RiskRegister::canPostRisk(\Confide::user(), $project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canReviseRejectedRiskMessage(Route $route)
    {
        $message = RiskRegisterMessage::find($route->getParameter('riskRegisterMessageId'));

        if( ! $message->canReviseRejected(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canUpdatePublishedRisk(Route $route)
    {
        $risk = $this->repository->find($route->getParameter('riskRegisterId'));

        if( ! $risk->canUpdatePublishedRisk(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canComment(Route $route)
    {
        $risk = $this->repository->find($route->getParameter('riskRegisterId'));

        if( ! $risk->canPostComment(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canUpdateCommentMessage(Route $route)
    {
        $message = RiskRegisterMessage::find($route->getParameter('riskRegisterMessageId'));

        if( ! $message->canUpdateComment(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}