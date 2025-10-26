<?php namespace PCK\Filters;

use PCK\Tenders\Tender;
use PCK\Projects\Project;
use Illuminate\Routing\Route;
use PCK\Tenders\TenderRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Users\User;
use PCK\Verifier\Verifier;
use PCK\RequestForVariation\RequestForVariation;

class TenderFilters {

    private $tenderRepo;

    private $user;

    public function __construct(TenderRepository $tenderRepo)
    {
        $this->tenderRepo = $tenderRepo;
        $this->user       = \Confide::user();
    }

    public function allowBusinessUnitOrGCDToAccess(Route $route)
    {
        $project = $route->getParameter('projectId');

        $this->checkRole($project, self::getListOfTendererFormRole($project));
    }

    public function checkTenderAccessLevelPermission(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! self::checkTenderAccessLevelPermissionAllowed($project, $this->user) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkTenderQuestionnaireAccessLevelPermission(Route $route)
    {
        $project = $route->getParameter('projectId');

        if(!self::checkTenderQuestionnaireAccessLevelPermissionAllowed($project, $this->user))
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public static function checkTenderQuestionnaireAccessLevelPermissionAllowed(Project $project, User $user)
    {
        return (self::checkTenderAccessLevelPermissionAllowed($project, $user) && $project->latestTender && $project->latestTender->callingTenderInformation);
    }

    public static function checkTenderAccessLevelPermissionAllowed(Project $project, User $user)
    {
        if( ! $user->getAssignedCompany($project) )
        {
            return false;
        }

        foreach(Tender::rolesAllowedToUseModule($project) as $role)
        {
            if( $user->hasCompanyProjectRole($project, $role) )
            {
                return true;
            }
        }

        if($user->isTopManagementVerifier())
        {
            return true;
        }

        return false;
    }

    public function checkROTSubmissionStatus(Route $route)
    {
        $tender = $this->getTenderDetails($route);

        if( $tender->recommendationOfTendererInformation && $tender->recommendationOfTendererInformation->isSubmitted() )
        {
            \Flash::error(trans('filter.recommendationOfTendererAlreadySubmitted'));

            return \Redirect::back();
        }

        if( ! $this->user->isTopManagementVerifier() )
        {
            $this->checkRole($tender->project, Role::PROJECT_OWNER);
        }
    }

    public function checkLOTSubmissionStatus(Route $route)
    {
        $tender = $this->getTenderDetails($route);

        if( $tender->listOfTendererInformation->isSubmitted() )
        {
            \Flash::error(trans('filter.listOfTendererAlreadySubmitted'));

            return \Redirect::back();
        }

        if( ! $this->user->isTopManagementVerifier() )
        {
            $allowedRole = self::getListOfTendererFormRole($tender->project);
    
            $this->checkRole($tender->project, $allowedRole) ;
        }
    }

    public function latestTenderOpenStatus(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->latestTender->isTenderOpen() )
        {
            throw new InvalidAccessLevelException(trans('filter.latestTenderNotYetOpen'));
        }
    }

    public function checkCallingTenderSubmissionStatus(Route $route)
    {
        $tender = $this->getTenderDetails($route);

        if( ! $this->user->isTopManagementVerifier() )
        {
            $this->checkRole($tender->project, $tender->project->getCallingTenderRole());
        }
    }

    public function allowReTender(Route $route)
    {
        $this->getTenderDetails($route);

        $tender = $this->tenderRepo->find($route->getParameter('projectId'), $route->getParameter('tenderId'));

        if( $tender->openTenderAwardRecommendtion && \PCK\Verifier\Verifier::isBeingVerified($tender->openTenderAwardRecommendtion))
        {
            throw new InvalidAccessLevelException(trans('filter.cannotResubmitTender:awardRecommendation'));
        }

        if( ! $tender->allowedReTender() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    // will check for Project's current status, if Post Contract already then stop all the request
    private function getTenderDetails(Route $route)
    {
        $tender = $this->tenderRepo->find($route->getParameter('projectId'), $route->getParameter('tenderId'));

        if( $tender->project->onPostContractStages() )
        {
            throw new InvalidAccessLevelException(trans('filter.projectAlreadyPostContract'));
        }

        return $tender;
    }

    private function checkRole($project, $role)
    {
        if( ! $this->user->hasCompanyProjectRole($project, $role) )
        {
            throw new InvalidAccessLevelException(trans('filter.invalidRole'));
        }
    }

    public static function getListOfTendererFormRole(Project $project)
    {
        $compRepo = \App::make('PCK\Companies\CompanyRepository');

        $companies = $compRepo->getCompaniesWithRoles($project, array(
            Role::GROUP_CONTRACT
        ));

        if( ! empty( $companies ) )
        {
            return Role::GROUP_CONTRACT;
        }

        return Role::PROJECT_OWNER;
    }

    public static function hasTechnicalEvaluation(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->hasTechnicalEvaluation() )
        {
            throw new InvalidAccessLevelException(trans('filter.noTechnicalEvaluation'));
        }
    }

    public static function checkTechnicalEvaluationVerifierStatus(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = Tender::find($route->getParameter('tenderId'));

        if( ! $project->showTechnicalEvaluationDetails($tender) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public static function technicalAssessmentApprovalCheck(Route $route)
    {
        $project = $route->getParameter('projectId');
        $tender  = Tender::find($route->getParameter('tenderId'));

        if( ! ($tender->technicalEvaluation && Verifier::isApproved($tender->technicalEvaluation)) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function callingTenderIsOpen(Route $route)
    {
        $tender = Tender::find($route->getParameter('tenderId'));

        if( $tender->isTenderClosed() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function topManagementVerifierTenderingStageApprovalCheck(Route $route)
    {
        $tender     = Tender::find($route->getParameter('tenderId'));
        $canApprove = ($tender->getTenderStageInformation() && $tender->getTenderStageInformation()->isBeingValidated() && in_array($this->user->id, $tender->getTenderStageInformation()->latestVerifier->lists('id'))) ? true : false;

        if( ! $canApprove )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function topManagementVerifierRequestForVariationApprovalCheck(Route $route)
    {
        $user    = \Confide::user();
        $project = $route->getParameter('projectId');

        $inputs                = \Input::all();
        $requestForVariationId = is_null($route->getParameter('requestForVariationId')) ? $inputs['requestForVariationId'] : $route->getParameter('requestForVariationId');

        $requestForVariation = RequestForVariation::find($requestForVariationId);

        if( ! $requestForVariation->canUserVerifyPendingApproval($user) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }

        $isVerifierWithoutProjectAccess = is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier();

        if( ! $isVerifierWithoutProjectAccess )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function topManagementVerifierOpenTenderAwardRecommendationApprovalCheck(Route $route)
    {
        $user    = \Confide::user();
        $project = $route->getParameter('projectId');
        $tender  = Tender::find($route->getParameter('tenderId'));

        $isVerifierWithoutProjectAccess = is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier();

        if( ! $isVerifierWithoutProjectAccess )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }

        if(!Verifier::isCurrentVerifier($user, $tender->openTenderAwardRecommendtion))
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }
}