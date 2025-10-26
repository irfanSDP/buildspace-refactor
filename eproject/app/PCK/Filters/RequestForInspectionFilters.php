<?php namespace PCK\Filters;

use App;
use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\RequestForInspection\RequestForInspection;
use PCK\RequestForInspection\RequestForInspectionInspection;
use PCK\RequestForInspection\RequestForInspectionReply;
use PCK\RequestForInspection\RequestForInspectionRepository;

class RequestForInspectionFilters {

    private $currentUser;
    private $repository;

    public function __construct(RequestForInspectionRepository $repository)
    {
        $this->repository  = $repository;
        $this->currentUser = \Confide::user();
    }

    public function requestStore(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! RequestForInspection::canPost($this->currentUser, $project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function requestView(Route $route)
    {
        $project = $route->getParameter('projectId');
        $request = $this->repository->find($route->getParameter('requestForInspectionId'));

        if( ! $request ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

        if( $request->project_id != $project->id ) App::abort(404);

        if( ! $request->isVisible($this->currentUser) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function requestUpdate(Route $route)
    {
        $request = $this->repository->find($route->getParameter('requestForInspectionId'));

        if( ! $request ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

        if( ! $request->canUpdate($this->currentUser) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function inspectionStore(Route $route)
    {
        $request = $this->repository->find($route->getParameter('requestForInspectionId'));

        if( ! RequestForInspectionInspection::canPost($this->currentUser, $request) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function inspectionUpdate(Route $route)
    {
        $inspection = $this->repository->findInspection($route->getParameter('inspectionId'));

        if( ! $inspection ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

        if( ! $inspection->canUpdate($this->currentUser) ) new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function replyStore(Route $route)
    {
        $inspection = $this->repository->findInspection($route->getParameter('inspectionId'));

        if( ! RequestForInspectionReply::canPost($this->currentUser, $inspection) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function replyUpdate(Route $route)
    {
        $reply = $this->repository->findReply($route->getParameter('replyId'));

        if( ! $reply ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

        if( ! $reply->canUpdate($this->currentUser) ) new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}