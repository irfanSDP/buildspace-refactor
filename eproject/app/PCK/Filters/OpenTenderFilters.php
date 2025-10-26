<?php namespace PCK\Filters;

use Confide;
use Illuminate\Routing\Route;
use PCK\Projects\Project;
use PCK\Tenders\TenderRepository;
use PCK\Exceptions\InvalidAccessLevelException;

class OpenTenderFilters {

    private $tenderRepo;

    public function __construct(TenderRepository $tenderRepo)
    {
        $this->tenderRepo = $tenderRepo;
    }

    public function openTenderNotYetOpen(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = $this->tenderRepo->find($project, $route->getParameter('tenderId'));

        if( $tender->isTenderOpen() )
        {
            throw new InvalidAccessLevelException(trans('filter.tenderAlreadyOpen'));
        }
    }

    public function openTenderStillInValidation(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = $this->tenderRepo->find($project, $route->getParameter('tenderId'));

        if( ! $tender->openTenderIsBeingValidated() )
        {
            throw new InvalidAccessLevelException(trans('filter.requestNoLongerValid'));
        }
    }

    public function openTenderIsOpen(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = $this->tenderRepo->find($project, $route->getParameter('tenderId'));

        if( ! $tender->isTenderOpen() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function checkTechnicalEvaluationStatus(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = $this->tenderRepo->find($project, $route->getParameter('tenderId'));

        if( $tender->technicalEvaluationIsSubmitted() )
        {
            throw new InvalidAccessLevelException(trans('filter.technicalEvaluationVerificationOver'));
        }
    }

    public function technicalEvaluationStillInValidation(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = $this->tenderRepo->find($project, $route->getParameter('tenderId'));

        if( ! $tender->technicalEvaluationIsBeingValidated() )
        {
            throw new InvalidAccessLevelException(trans('filter.requestNoLongerValid'));
        }
    }

    public static function editorRoles(Project $project)
    {
        return array( TenderFilters::getListOfTendererFormRole($project) );
    }

    public static function accessRoles(Project $project)
    {
        return array(
            TenderFilters::getListOfTendererFormRole($project),
            $project->getCallingTenderRole(),
        );
    }

    public function hasAccess(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = Confide::user();

        if( ! $user->hasCompanyProjectRole($project, self::accessRoles($project)) && ! $user->isTopManagementVerifier())
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

}