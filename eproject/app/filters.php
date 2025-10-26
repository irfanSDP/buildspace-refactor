<?php

use PCK\Exceptions\InvalidAccessLevelException;
use Illuminate\Routing\Route as IlluminateRoute;
use Symfony\Component\Security\Core\Util\StringUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

use PCK\Helpers\ContentMinifier\Minify;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
    $request->setTrustedProxies([$request->getClientIp()]);

    \PCK\AccessLog\AccessLog::log($request);
});


App::after(function($request, $response)
{
    if(!Config::get('app.debug', false))
    {
        if (is_object($response) && $response instanceof Response && strtolower(strtok($response->headers->get('Content-Type'), ';')) === 'text/html')
        {
            $response->setContent(
                Minify::html($response->getContent(), [
                    'jsCleanComments' => true
                ])
            );
        }
    }
});


// Middleware logic for maintenance mode
Route::filter('maintenance', function($request, $next)
{
    $currentTime = Carbon::now();
    $maintenance = DB::table('scheduled_maintenance')
    ->where('is_under_maintenance', true)
    ->where('start_time', '<=', $currentTime)
    ->where('end_time', '>=', $currentTime)
    ->first();

    if ($maintenance) {
        $user = Confide::user();
        if ($user && $user->isSuperAdmin()) {
            return;
        }

        // Calculate the difference
        $end = Carbon::parse($maintenance->end_time);
        $diff = $end->diff($currentTime);
        
        // Accessing the difference
        $days = $diff->days;
        $hours = $diff->h;
        $minutes = $diff->i;
        $seconds = $diff->s;

        return View::make('errors.maintenance', array(
            'id' =>  $maintenance->id,
            'message' =>  $maintenance->message,
            'image' =>  $maintenance->image,
            'days' =>  $days,
            'hours' =>  $hours,
            'minutes' =>  $minutes,
            'seconds' =>  $seconds,
        ));
    }

});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
    if( Auth::guest() )
    {
        if( Request::ajax() )
        {
            return Response::make('Unauthorized', 401);
        }

        return Redirect::guest('login');
    }
});


Route::filter('auth.basic', function()
{
    return Auth::basic();
});

Route::filter('auth.api.token', 'PCK\Filters\ApiTokenFilter@auth');

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
    $user = Confide::user();

    if( ! Saml::isAuthenticated() OR ! $user )
    {
        // use to logout default laravel authentication if available
        Confide::logout();

        \SimpleSAML_Session::getSessionFromRequest()->cleanup();

        return Redirect::guest(route('users.login'));
    }

    $data     = Saml::getAttributes();
    $id       = $data[ Config::get('laravel-saml::saml.saml_id_property', 'email') ][0];
    $property = Config::get('laravel-saml::saml.internal_id_property', 'email');

    $userFromSaml = call_user_func(array( Config::get('laravel-saml::saml.sp_user_model_class'), 'where' ), $property, "=", $id)->first();

    /*
     * We have to logout from laravel to clear laravel user session because both eproject and buildspace is a SP.
     * This is to solve issue when user logged out from buildspace but the laravel session is still valid even though
     * the saml session is already terminated.
     */
    if( $userFromSaml && $userFromSaml->id != $user->id )
    {
        $user = $userFromSaml;

        Confide::logout();

        Auth::login($user);
    }

    if( $user ) App::setLocale($user->settings->language->code);
});

Route::filter('passwordUpdated', function()
{
    $user = Confide::user();

    if( ! getenv('FORCE_PASSWORD_RENEW') ) return;

    // Password has expired.
    if( ( \Carbon\Carbon::now()->diffInDays($user->password_updated_at) ) > intval(getenv('PASSWORD_VALID_DURATION')) )
    {
        return Redirect::route('passwordUpdateForm');
    }

    // Password has not been changed before.
    if( empty( $user->password_updated_at ) )
    {
        return Redirect::route('passwordUpdateForm');
    }
});

Route::filter('temporaryLogin', function()
{
    $user = Confide::user();

    if( $user->isTemporaryAccount() && $user->purge_date->isPast())
    {
        Confide::logout();

        \SimpleSAML_Session::getSessionFromRequest()->cleanup();

        return Redirect::route('users.logout');
    }
});
Route::filter('authenticated', function()
{
    if( Confide::user() )
    {
        return Redirect::route('home.index');
    }
});

Route::filter('appLicenseValid', function()
{
    $enableLogout      = true;
    $user              = Confide::user();
    $licenseRepository = App::make('PCK\Licenses\LicenseRepository');
    $isLicenseValid    = $licenseRepository->checkLicenseValidity();

    if( $isLicenseValid ) return;

    if( is_null($user) )
    {
        $enableLogout = false;
    }
    else
    {
        if( $user->isSuperAdmin() )
        {
            return Redirect::route('license.index');
        }
    }

    return View::make('errors.license_error', array(
        'enableLogout' => $enableLogout,
    ));
});

Route::filter('checkpoint', function()
{
    Checkpoint::process();
});

/*
|--------------------------------------------------------------------------
| Project Filter
|--------------------------------------------------------------------------
|
|
*/

Route::filter('checkProjectPermission', function(IlluminateRoute $route)
{
    $user    = Confide::user();
    $repo    = App::make('PCK\Projects\ProjectRepository');
    $project = $route->getParameter('projectId');

    if( $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) && ( ! $project->contractor_access_enabled ) )
    {
        Flash::error(trans('filter.submitTenderPermissionDenied'));

        return Redirect::route('projects.index');
    }

    if( ! in_array($project->id, $repo->getVisibleProjectIds($user)) )
    {
        Flash::error(trans('filter.submitTenderPermissionDenied'));

        return Redirect::route('projects.index');
    }
});

Route::filter('checkSubmitTenderPermission', function(IlluminateRoute $route)
{
    $repo = App::make('PCK\Projects\ProjectRepository');

    $hasPermission = $repo->getSubmitTenderByUserPermission(Confide::user(), $route->getParameter('projectId'));

    if( ! $hasPermission )
    {
        Flash::error(trans('filter.submitTenderPermissionDenied'));

        return Redirect::route('projects.index');
    }

});

Route::filter('checkProjectPermissionByTender', function(IlluminateRoute $route)
{
    $tender = \PCK\Tenders\Tender::find($route->getParameter('tenderId'));
    $repo   = App::make('PCK\Projects\ProjectRepository');

    $repo->getProjectByUserPermission(Confide::user(), $tender->project);
});

Route::filter('checkForTenderDocumentsAllowedRole', function(IlluminateRoute $route)
{
    $user         = \Confide::user();
    $project      = $route->getParameter('projectId');
    $allowedGroup = $project->getCallingTenderRole();

    if( ! $user->hasCompanyProjectRole($project, $allowedGroup) )
    {
        $groupName = $project->getRoleName($allowedGroup);

        throw new InvalidAccessLevelException(trans('filter.groupPermissionDenied') . "({$groupName})");
    }
});

Route::filter('hasDocumentControlAccess', function(IlluminateRoute $route)
{
    $user    = \Confide::user();
    $project = $route->getParameter('projectId');

    $allowed = ( ! $user->isSuperAdmin() ) && ( ! $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) || $project->isPostContract() );

    if( ! $allowed ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('hasContractualClaimAccess', function(IlluminateRoute $route)
{
    $user    = \Confide::user();
    $project = $route->getParameter('projectId');

    $allowed = true;

    if( $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) )
    {
        $allowed = $project->contractor_contractual_claim_access_enabled;
    }

    if( ! $allowed ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));

});

Route::filter('contractType', function(IlluminateRoute $route, $request, $contractType)
{
    $project = $route->getParameter('projectId');

    if( $project->contract->type != $contractType ) App::abort(404);
});

Route::filter('resourceExists', function(IlluminateRoute $route, $request, $resourceClass, $resourceIdParam)
{
    $resourceId = $route->getParameter($resourceIdParam);

    if( ( ! empty( $resourceId ) ) && ( ! $resourceClass::find($resourceId) ) ) App::abort(404);
});

Route::filter('canEditFormOfTender', function(IlluminateRoute $route)
{
    $user    = \Confide::user();
    $project = $route->getParameter('projectId');
    $fotRepo = App::make('PCK\FormOfTender\FormOfTenderRepository');

    if( ! $fotRepo->canEditFormOfTender($project, $user) )
    {
        $groupName = $project->getRoleName($project->getCallingTenderRole());

        throw new InvalidAccessLevelException(trans('filter.groupPermissionDenied') . "({$groupName})");
    }
});

Route::filter('canViewBlankFormOfTender', function(IlluminateRoute $route)
{
    $user   = \Confide::user();
    $tender = \PCK\Tenders\Tender::find($route->getParameter('tenderId'));

    $fotRepo = App::make('PCK\FormOfTender\FormOfTenderRepository');

    if( ! $fotRepo->canViewBlankFormOfTender($tender, $user) )
    {
        throw new InvalidAccessLevelException(trans('filter.groupPermissionDenied'));
    }
});

Route::filter('checkForTenderDocumentsAllowedRoleViewCompleteFormOfTender', function(IlluminateRoute $route)
{
    $user     = \Confide::user();
    $project  = $route->getParameter('projectId');
    $tenderId = $route->getParameter('tenderId');
    $fotRepo  = App::make('PCK\FormOfTender\FormOfTenderRepository');

    if( $user->isSuperAdmin() || ! $fotRepo->hasPermissionToView($user->getAssignedCompany($project)->id, $tenderId) ) App::abort(404);
});

Route::filter('checkForLetterOfAwardPermission', function(IlluminateRoute $route)
{
    $user     = \Confide::user();
    $project  = $route->getParameter('projectId');

    if(!PCK\LetterOfAward\LetterOfAwardUserPermissionRepository::getIsUserAssignedToLetterOfAwardByProject($project, $user))
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('bimLevelEditable', function(IlluminateRoute $route)
{
    $bimLevel = \PCK\BuildingInformationModelling\BuildingInformationModellingLevel::find($route->getParameter('bimLevelId'));

    if( ! $bimLevel->canBeEdited() )
    {
        throw new InvalidAccessLevelException(trans('filter.bimLevelInUse'));
    }
});

Route::filter('checkValidStatusForPostContract', 'PCK\Filters\ProjectFilters@checkValidStatusForPostContract');
Route::filter('checkValidStatusForCompletion', 'PCK\Filters\ProjectFilters@checkValidStatusForCompletion');
Route::filter('canManuallySkipToPostContract', 'PCK\Filters\ProjectFilters@canManuallySkipToPostContract');
Route::filter('canAddSubProject', 'PCK\Filters\ProjectFilters@canAddSubProject');
Route::filter('project.stage.callingTender', 'PCK\Filters\ProjectFilters@isInCallingTenderStage');
Route::filter('project.stage.callingTender.isOpen', 'PCK\Filters\TenderFilters@callingTenderIsOpen');
Route::filter('project.stage.technicalEvaluation', 'PCK\Filters\ProjectFilters@technicalEvaluationIsOpen');
Route::filter('project.currentTenderStatus.closedTender', 'PCK\Filters\ProjectFilters@isCurrentTenderStatusClosed');
Route::filter('project.buildspace.contractorRates.canSync', 'PCK\Filters\ProjectFilters@canSyncBuildspaceContractorRates');

Route::filter('roles', 'PCK\Filters\RolesFilter@checkCurrentRoles');
Route::filter('projectRoles', 'PCK\Filters\RolesFilter@checkCurrentProjectRoles');
Route::filter('notProjectRoles', 'PCK\Filters\RolesFilter@doesNotHaveProjectRoles');
Route::filter('isEditor', 'PCK\Filters\RolesFilter@checkIsEditor');
Route::filter('canAddUser', 'PCK\Filters\RolesFilter@canAddUser');

Route::filter('latestTenderOpenStatus', 'PCK\Filters\TenderFilters@latestTenderOpenStatus');
Route::filter('allowBusinessUnitOrGCDToAccess', 'PCK\Filters\TenderFilters@allowBusinessUnitOrGCDToAccess');
Route::filter('checkTenderAccessLevelPermission', 'PCK\Filters\TenderFilters@checkTenderAccessLevelPermission');
Route::filter('checkTenderQuestionnaireAccessLevelPermission', 'PCK\Filters\TenderFilters@checkTenderQuestionnaireAccessLevelPermission');
Route::filter('hasTechnicalEvaluation', 'PCK\Filters\TenderFilters@hasTechnicalEvaluation');
Route::filter('checkTechnicalEvaluationVerifierStatus', 'PCK\Filters\TenderFilters@checkTechnicalEvaluationVerifierStatus');
Route::filter('technicalAssessmentApprovalCheck', 'PCK\Filters\TenderFilters@technicalAssessmentApprovalCheck');
Route::filter('checkROTSubmissionStatus', 'PCK\Filters\TenderFilters@checkROTSubmissionStatus');
Route::filter('checkLOTSubmissionStatus', 'PCK\Filters\TenderFilters@checkLOTSubmissionStatus');
Route::filter('checkCallingTenderSubmissionStatus', 'PCK\Filters\TenderFilters@checkCallingTenderSubmissionStatus');
Route::filter('allowReTender', 'PCK\Filters\TenderFilters@allowReTender');

Route::filter('topManagementVerifierTenderingStageApprovalCheck', 'PCK\Filters\TenderFilters@topManagementVerifierTenderingStageApprovalCheck');
Route::filter('topManagementVerifierRequestForVariationApprovalCheck', 'PCK\Filters\TenderFilters@topManagementVerifierRequestForVariationApprovalCheck');
Route::filter('topManagementVerifierOpenTenderAwardRecommendationApprovalCheck', 'PCK\Filters\TenderFilters@topManagementVerifierOpenTenderAwardRecommendationApprovalCheck');

Route::filter('checkOpenTenderStatus', 'PCK\Filters\OpenTenderFilters@openTenderNotYetOpen');
Route::filter('checkOpenTenderStillInValidation', 'PCK\Filters\OpenTenderFilters@openTenderStillInValidation');
Route::filter('openTender.isOpen', 'PCK\Filters\OpenTenderFilters@openTenderIsOpen');
Route::filter('checkTechnicalEvaluationStatus', 'PCK\Filters\OpenTenderFilters@checkTechnicalEvaluationStatus');
Route::filter('technicalEvaluationStillInValidation', 'PCK\Filters\OpenTenderFilters@technicalEvaluationStillInValidation');
Route::filter('openTenderAccess', 'PCK\Filters\OpenTenderFilters@hasAccess');
Route::filter('technicalEvaluationAccess', 'PCK\Filters\TechnicalEvaluationFilters@hasAccess');
Route::filter('technicalEvaluation.canUpdateResults', 'PCK\Filters\TechnicalEvaluationFilters@canUpdateEvaluationResults');

Route::filter('checkPreviousAIFirstLevelMessage', 'PCK\Filters\ArchitectInstructionFilters@checkPreviousAIFirstLevelMessage');
Route::filter('checkPreviousAIThirdLevelMessage', 'PCK\Filters\ArchitectInstructionFilters@checkPreviousAIThirdLevelMessage');
Route::filter('checkPreviousAIInterimClaim', 'PCK\Filters\ArchitectInstructionFilters@checkPreviousAIInterimClaim');

Route::filter('checkPreviousEOTFirstLevelMessage', 'PCK\Filters\ExtensionOfTimeFilters@checkPreviousFirstLevelMessage');
Route::filter('checkPreviousEOTSecondLevelMessage', 'PCK\Filters\ExtensionOfTimeFilters@checkPreviousSecondLevelMessage');
Route::filter('checkPreviousEOTThirdLevelMessage', 'PCK\Filters\ExtensionOfTimeFilters@checkPreviousThirdLevelMessage');
Route::filter('checkPreviousEOTFourthLevelMessage', 'PCK\Filters\ExtensionOfTimeFilters@checkPreviousFourthLevelMessage');
Route::filter('checkPreviousEOTContractorConfirmDelay', 'PCK\Filters\ExtensionOfTimeFilters@checkPreviousContractorConfirmDelay');
Route::filter('checkPreviousEOTClaim', 'PCK\Filters\ExtensionOfTimeFilters@checkPreviousEOTClaim');

Route::filter('checkPreviousLOEFirstLevelMessage', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousFirstLevelMessage');
Route::filter('checkPreviousLOESecondLevelMessage', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousSecondLevelMessage');
Route::filter('checkPreviousLOEThirdLevelMessage', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousThirdLevelMessage');
Route::filter('checkPreviousLOEFourthLevelMessage', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousFourthLevelMessage');
Route::filter('checkPreviousLOEContractorConfirmDelay', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousContractorConfirmDelay');
Route::filter('checkPreviousLOEClaim', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousClaim');
Route::filter('checkPreviousLOEInterimClaim', 'PCK\Filters\LossAndOrExpenseFilters@checkPreviousLOEInterimClaim');

Route::filter('checkPreviousAEFirstLevelMessage', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousFirstLevelMessage');
Route::filter('checkPreviousAESecondLevelMessage', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousSecondLevelMessage');
Route::filter('checkPreviousAEThirdLevelMessage', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousThirdLevelMessage');
Route::filter('checkPreviousAEFourthLevelMessage', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousFourthLevelMessage');
Route::filter('checkPreviousAEContractorConfirmDelay', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousContractorConfirmDelay');
Route::filter('checkPreviousAEClaim', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousClaim');
Route::filter('checkPreviousAEInterimClaim', 'PCK\Filters\AdditionalExpenseFilters@checkPreviousAEInterimClaim');

Route::filter('canCreateRfiMessage', 'PCK\Filters\RequestForInformationFilters@canCreateRfiMessage');
Route::filter('canPushRfiMessage', 'PCK\Filters\RequestForInformationFilters@canPushMessage');

Route::filter('canPostRisk', 'PCK\Filters\RiskRegisterFilters@canPostRisk');
Route::filter('canReviseRejectedRiskMessage', 'PCK\Filters\RiskRegisterFilters@canReviseRejectedRiskMessage');
Route::filter('canUpdatePublishedRisk', 'PCK\Filters\RiskRegisterFilters@canUpdatePublishedRisk');
Route::filter('canComment', 'PCK\Filters\RiskRegisterFilters@canComment');
Route::filter('canUpdateCommentMessage', 'PCK\Filters\RiskRegisterFilters@canUpdateCommentMessage');

Route::filter('inspectionRequestStore', 'PCK\Filters\RequestForInspectionFilters@requestStore');
Route::filter('inspectionRequestView', 'PCK\Filters\RequestForInspectionFilters@requestView');
Route::filter('inspectionRequestUpdate', 'PCK\Filters\RequestForInspectionFilters@requestUpdate');
Route::filter('inspectionRequestInspectionStore', 'PCK\Filters\RequestForInspectionFilters@inspectionStore');
Route::filter('inspectionRequestInspectionUpdate', 'PCK\Filters\RequestForInspectionFilters@inspectionUpdate');
Route::filter('inspectionReplyStore', 'PCK\Filters\RequestForInspectionFilters@replyStore');
Route::filter('inspectionReplyUpdate', 'PCK\Filters\RequestForInspectionFilters@replyUpdate');

Route::filter('tenderDocument.folder.access', 'PCK\Filters\TenderDocumentFilters@folderAccess');
Route::filter('tenderDocument.folder.modify', 'PCK\Filters\TenderDocumentFilters@folderModify');
Route::filter('tenderDocument.folder.download', 'PCK\Filters\TenderDocumentFilters@folderDownload');
Route::filter('tenderDocument.file.access', 'PCK\Filters\TenderDocumentFilters@fileAccess');
Route::filter('tenderDocument.file.modify', 'PCK\Filters\TenderDocumentFilters@fileModify');
Route::filter('tenderDocument.file.download', 'PCK\Filters\TenderDocumentFilters@fileDownload');

Route::filter('projectDocument.folder.access', 'PCK\Filters\ProjectDocumentFilters@folderAccess');
Route::filter('projectDocument.folder.modify', 'PCK\Filters\ProjectDocumentFilters@folderModify');
Route::filter('projectDocument.file.access', 'PCK\Filters\ProjectDocumentFilters@fileAccess');
Route::filter('projectDocument.file.modify', 'PCK\Filters\ProjectDocumentFilters@fileModify');
Route::filter('projectDocument.upload.modify', 'PCK\Filters\ProjectDocumentFilters@uploadModify');

Route::filter('contractManagement.isUserManager', 'PCK\Filters\ContractManagementFilters@isUserManager');

Route::filter('letterOfAward.isValidSubstitute', 'PCK\Filters\PostContractLetterOfAwardFilters@isValidSubstitute');
Route::filter('claim.advancedPayment.isValidSubstitute', 'PCK\Filters\ClaimAdvancedPaymentFilters@isValidSubstitute');
Route::filter('claim.claimCertificate.isValidSubstitute', 'PCK\Filters\ClaimCertificateFilters@isValidSubstitute');
Route::filter('claim.variationOrder.isValidSubstitute', 'PCK\Filters\ClaimVariationOrderFilters@isValidSubstitute');
Route::filter('claim.materialOnSite.isValidSubstitute', 'PCK\Filters\ClaimMaterialOnSiteFilters@isValidSubstitute');
Route::filter('claim.deposit.isValidSubstitute', 'PCK\Filters\ClaimDepositFilters@isValidSubstitute');
Route::filter('claim.outOfContractItem.isValidSubstitute', 'PCK\Filters\ClaimOutOfContractItemFilters@isValidSubstitute');
Route::filter('claim.purchaseOnBehalf.isValidSubstitute', 'PCK\Filters\ClaimPurchaseOnBehalfFilters@isValidSubstitute');
Route::filter('claim.workOnBehalf.isValidSubstitute', 'PCK\Filters\ClaimWorkOnBehalfFilters@isValidSubstitute');
Route::filter('claim.workOnBehalfBackCharge.isValidSubstitute', 'PCK\Filters\ClaimWorkOnBehalfBackChargeFilters@isValidSubstitute');
Route::filter('claim.penalty.isValidSubstitute', 'PCK\Filters\ClaimPenaltyFilters@isValidSubstitute');
Route::filter('claim.permit.isValidSubstitute', 'PCK\Filters\ClaimPermitFilters@isValidSubstitute');
Route::filter('claim.waterDeposit.isValidSubstitute', 'PCK\Filters\ClaimWaterDepositFilters@isValidSubstitute');

Route::filter('indonesiaCivilContract.userPermissionManager', 'PCK\Filters\IndonesiaCivilContract\UserPermissionFilters@isUserPermissionManager');
Route::filter('indonesiaCivilContract.architectInstructions.isEditor', 'PCK\Filters\IndonesiaCivilContract\ArchitectInstructionFilters@isEditor');
Route::filter('indonesiaCivilContract.architectInstructions.isVisible', 'PCK\Filters\IndonesiaCivilContract\ArchitectInstructionFilters@isVisible');
Route::filter('indonesiaCivilContract.architectInstructions.canRespond', 'PCK\Filters\IndonesiaCivilContract\ArchitectInstructionFilters@canRespond');
Route::filter('indonesiaCivilContract.earlyWarning.isEditor', 'PCK\Filters\IndonesiaCivilContract\EarlyWarningFilters@isEditor');
Route::filter('indonesiaCivilContract.extensionOfTime.isEditor', 'PCK\Filters\IndonesiaCivilContract\ExtensionOfTimeFilters@isEditor');
Route::filter('indonesiaCivilContract.extensionOfTime.canRespond', 'PCK\Filters\IndonesiaCivilContract\ExtensionOfTimeFilters@canRespond');
Route::filter('indonesiaCivilContract.lossAndExpenses.isEditor', 'PCK\Filters\IndonesiaCivilContract\LossAndExpenseFilters@isEditor');
Route::filter('indonesiaCivilContract.lossAndExpenses.canRespond', 'PCK\Filters\IndonesiaCivilContract\LossAndExpenseFilters@canRespond');

Route::filter('siteManagement.hasSiteManagementUserManagementPermission', 'PCK\Filters\SiteManagementFilters@hasSiteManagementUserManagementPermission');
Route::filter('siteManagement.hasDefectPermission', 'PCK\Filters\SiteManagementFilters@hasDefectPermission');
Route::filter('siteManagement.hasViewDefectFormPermission', 'PCK\Filters\SiteManagementFilters@hasViewDefectFormPermission');
Route::filter('siteManagement.hasViewMCARFormPermission', 'PCK\Filters\SiteManagementFilters@hasViewMCARFormPermission');
Route::filter('siteManagement.hasCreateMCARFormPermission', 'PCK\Filters\SiteManagementFilters@hasCreateMCARFormPermission');
Route::filter('siteManagement.hasDefectProjectManagerPermission', 'PCK\Filters\SiteManagementFilters@hasDefectProjectManagerPermission');

Route::filter('siteManagement.hasDailyLabourReportsPermission', 'PCK\Filters\SiteManagementFilters@hasDailyLabourReportsPermission');
Route::filter('siteManagement.hasSiteDiaryPermission', 'PCK\Filters\SiteManagementFilters@hasSiteDiaryPermission');
Route::filter('siteManagement.hasInstructionToContractorPermission', 'PCK\Filters\SiteManagementFilters@hasInstructionToContractorPermission');

Route::filter('inspection.hasModuleAccess', 'PCK\Filters\InspectionFilters@hasModuleAccessRoute');
Route::filter('inspection.canRequestInspection', 'PCK\Filters\InspectionFilters@canRequestInspectionRoute');
Route::filter('inspection.requestForInspectionInDraft', 'PCK\Filters\InspectionFilters@requestForInspectionIsDraft');
Route::filter('inspection.isDraft', 'PCK\Filters\InspectionFilters@isDraft');
Route::filter('inspection.isNotDraft', 'PCK\Filters\InspectionFilters@isNotDraft');
Route::filter('inspection.hasInspectorRole', 'PCK\Filters\InspectionFilters@hasInspectorRole');
Route::filter('inspection.readyForSubmission', 'PCK\Filters\InspectionFilters@readyForSubmissionRoute');
Route::filter('inspection.isSubmitter', 'PCK\Filters\InspectionFilters@isSubmitterRoute');

Route::filter('form.readyForSubmissionByVendor', 'PCK\Filters\FormBuilderFilters@formReadyForSubmissionByVendor');
Route::filter('form.canBeEdited', 'PCK\Filters\FormBuilderFilters@formCanBeEdited');
Route::filter('form.canCreateRevision', 'PCK\Filters\FormBuilderFilters@formCanCreateRevision');
Route::filter('form.canBeEditedAjax', 'PCK\Filters\FormBuilderFilters@formCanBeEditedAjax');
Route::filter('form.column.canBeEdited', 'PCK\Filters\FormBuilderFilters@columnCanBeEdited');
Route::filter('form.section.canBeEdited', 'PCK\Filters\FormBuilderFilters@sectionCanBeEdited');
Route::filter('form.section.canBeEditedAjax', 'PCK\Filters\FormBuilderFilters@sectionCanBeEditedAjax');
Route::filter('form.element.canBeEdited', 'PCK\Filters\FormBuilderFilters@elementCanBeEdited');
Route::filter('form.canSubmitForApproval', 'PCK\Filters\FormBuilderFilters@formCanSubmitForApproval');
Route::filter('form.isBeingApproved', 'PCK\Filters\FormBuilderFilters@formIsBeingApproved');

Route::filter('requestForVariation.status.approved.check', 'PCK\Filters\RequestForVariationFilters@requestForVariationStatusApprovedCheck');

Route::filter('validateConsultantManagementRoles', 'PCK\Filters\ConsultantManagementFilters@validateConsultantManagementRoles');
Route::filter('validateConsultantPaymentsUserPermission', 'PCK\Filters\ConsultantManagementFilters@validateConsultantPaymentsUserPermission');

Route::filter('projectReport.isTemplateCheck', 'PCK\Filters\ProjectReportFilters@isTemplateCheck');
Route::filter('projectReport.isDraftCheck', 'PCK\Filters\ProjectReportFilters@isDraftCheck');
Route::filter('projectReport.isCompletedCheck', 'PCK\Filters\ProjectReportFilters@isCompletedCheck');
Route::filter('projectReport.templatePermissionCheck', 'PCK\Filters\ProjectReportFilters@templatePermissionCheck');
Route::filter('projectReport.userPermissionAccessCheck', 'PCK\Filters\ProjectReportFilters@userPermissionAccessCheck');
Route::filter('projectReport.canCreateNewRevisionCheck', 'PCK\Filters\ProjectReportFilters@canCreateNewRevisionCheck');
Route::filter('projectReport.hasProjectReportPermission', 'PCK\Filters\ProjectReportFilters@hasProjectReportPermission');
Route::filter('projectReport.hasProjectTypePermission', 'PCK\Filters\ProjectReportFilters@hasProjectTypePermission');
Route::filter('projectReport.isCurrentVerifier', 'PCK\Filters\ProjectReportFilters@isCurrentVerifier');
Route::filter('projectReport.dashboard.permissionCheck', 'PCK\Filters\ProjectReportFilters@dashboardPermissionCheck');

Route::filter('ProjectChart.templatePermission', 'PCK\Filters\ProjectChartFilters@templatePermission');
Route::filter('ProjectChart.chartPermission', 'PCK\Filters\ProjectChartFilters@chartPermission');

Route::filter('tenderQuestionnaire.canCreate', 'PCK\Filters\TenderQuestionnaireFilters@canCreateQuestionnaire');
Route::filter('tenderQuestionnaire.question.canEdit', 'PCK\Filters\TenderQuestionnaireFilters@canEditQuestion');

Route::filter('eBidding.checkSessionListAccess', 'PCK\Filters\EBiddingFilters@checkSessionListAccess');
Route::filter('eBidding.checkConsoleAccess', 'PCK\Filters\EBiddingFilters@checkConsoleAccess');
Route::filter('eBidding.checkRankingListAccess', 'PCK\Filters\EBiddingFilters@checkRankingListAccess');
Route::filter('eBidding.checkBiddingHistoryAccess', 'PCK\Filters\EBiddingFilters@checkBiddingHistoryAccess');
Route::filter('eBidding.checkProjectEBiddingAccess', 'PCK\Filters\EBiddingFilters@checkProjectEBiddingAccess');

/*
|--------------------------------------------------------------------------
| User Level Filter
|--------------------------------------------------------------------------
|
|
*/

Route::filter('verifier.isCurrentVerifier', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('objectId');

    $input  = Input::all();
    $object = $input['class']::find($objectId);

    if( ! \PCK\Verifier\Verifier::isCurrentVerifier($user, $object) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('systemModule.inspection.enabled', function()
{
    if( ! \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_INPSECTION) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('systemModule.vendorManagement.enabled', function()
{
    if( ! \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('systemModule.digitalStar.enabled', function()
{
    if( ! \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('digitalStar.isCompanyEvaluator', function($route, $request)
{
    $user = Confide::user();

    $objectId = $route->getParameter('formId');

    $evaluationForm = \PCK\DigitalStar\Evaluation\DsEvaluationForm::find($objectId);
    if (! $evaluationForm)
    {
        throw new ModelNotFoundException;
    }

    $evaluation = $evaluationForm->evaluation;
    if (! $evaluation)
    {
        throw new ModelNotFoundException;
    }

    $isCompanyEvaluator = (($evaluation->company_id === $user->company_id) && $user->isGroupAdmin());

    if (! $isCompanyEvaluator)
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('digitalStar.isProjectEvaluator', function($route, $request)
{
    $user = Confide::user();

    $objectId = $route->getParameter('formId');

    $evaluationForm = \PCK\DigitalStar\Evaluation\DsEvaluationForm::find($objectId);
    if (! $evaluationForm)
    {
        throw new ModelNotFoundException;
    }

    $evaluation = $evaluationForm->evaluation;
    if (! $evaluation)
    {
        throw new ModelNotFoundException;
    }
    $project = $evaluationForm->project;
    if (! $project)
    {
        throw new ModelNotFoundException;
    }

    $repo = App::make('PCK\Projects\ProjectRepository');

    if ( $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) && ( ! $project->contractor_access_enabled ) )
    {
        throw new InvalidAccessLevelException(trans('digitalStar/digitalStar.errorNoAccessToProject'));
    }

    if(! in_array($project->id, $repo->getVisibleProjectIds($user)) )
    {
        throw new InvalidAccessLevelException(trans('digitalStar/digitalStar.errorNoAccessToProject'));
    }

    $role = \PCK\DigitalStar\Evaluation\DsRole::where('slug', '=', 'project-evaluator')->first();
    if ($role) {
        $isProjectEvaluator = \PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole::where('ds_evaluation_form_id', $evaluationForm->id)
            ->where('ds_role_id', $role->id)
            ->where('user_id', $user->id)
            ->exists();
    } else {
        $isProjectEvaluator = false;
    }

    if (! $isProjectEvaluator)
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('digitalStar.isCompanyEvaluatorOrProcessor', function($route, $request)
{
    $user = Confide::user();

    $objectId = $route->getParameter('formId');

    $evaluationForm = \PCK\DigitalStar\Evaluation\DsEvaluationForm::find($objectId);
    if (! $evaluationForm)
    {
        throw new ModelNotFoundException;
    }

    $evaluation = $evaluationForm->evaluation;
    if (! $evaluation)
    {
        throw new ModelNotFoundException;
    }

    $isCompanyEvaluator = (($evaluation->company_id === $user->company_id) && $user->isGroupAdmin());

    $processorRole = \PCK\DigitalStar\Evaluation\DsRole::where('slug', '=', 'company-processor')->first();
    if ($processorRole) {
        $isCompanyProcessor = \PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole::where('ds_evaluation_form_id', $evaluationForm->id)
            ->where('ds_role_id', $processorRole->id)
            ->where('user_id', $user->id)
            ->exists();
    } else {
        $isCompanyProcessor = false;
    }

    if (! $isCompanyEvaluator && ! $isCompanyProcessor)
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('digitalStar.isVerifierSelector', function($route, $request)
{
    // Currently, these roles are allowed to select verifiers (after the evaluation form is submitted by evaluator):
    // 1. Company processors
    // 2. Project evaluators

    $user = Confide::user();

    $objectId = $route->getParameter('formId');

    $evaluationForm = \PCK\DigitalStar\Evaluation\DsEvaluationForm::find($objectId);
    if (! $evaluationForm)
    {
        throw new ModelNotFoundException;
    }

    $evaluation = $evaluationForm->evaluation;
    if (! $evaluation)
    {
        throw new ModelNotFoundException;
    }

    // Company processor
    $role = \PCK\DigitalStar\Evaluation\DsRole::where('slug', '=', 'company-processor')->first();
    if ($role) {
        $isCompanyProcessor = \PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole::where('ds_evaluation_form_id', $evaluationForm->id)
            ->where('ds_role_id', $role->id)
            ->where('user_id', $user->id)
            ->exists();
    } else {
        $isCompanyProcessor = false;
    }

    // Project evaluator
    $role = \PCK\DigitalStar\Evaluation\DsRole::where('slug', '=', 'project-evaluator')->first();
    if ($role) {
        $isProjectEvaluator = \PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole::where('ds_evaluation_form_id', $evaluationForm->id)
            ->where('ds_role_id', $role->id)
            ->where('user_id', $user->id)
            ->exists();
    } else {
        $isProjectEvaluator = false;
    }

    if (! $isCompanyProcessor && ! $isProjectEvaluator)
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('digitalStar.isVerifier', function($route, $request)
{
    $user = Confide::user();

    $objectId = $route->getParameter('formId');

    $evaluationForm = \PCK\DigitalStar\Evaluation\DsEvaluationForm::find($objectId);
    if (! $evaluationForm)
    {
        throw new ModelNotFoundException;
    }

    $evaluation = $evaluationForm->evaluation;
    if (! $evaluation)
    {
        throw new ModelNotFoundException;
    }

    if(! \PCK\Verifier\Verifier::isAVerifier($user, $evaluationForm) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorManagement.isVendor', function()
{
    $user = \Confide::user();

    if(isset($user->company->contractGroupCategory))
    {
        if($user->company->contractGroupCategory->type != PCK\ContractGroupCategory\ContractGroupCategory::TYPE_EXTERNAL)
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }
});

Route::filter('vendorManagement.canChangeVendorGroup', function()
{
    $user = \Confide::user();

    $canChangeVendorGroup = ($user->company->vendorRegistration->isFirst() && $user->company->vendorRegistration->isDraft());

    if( ! $canChangeVendorGroup )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied')); 
    }
});

Route::filter('vendorManagement.vendorRegistration.isDraft', function()
{
    $user = \Confide::user();

    if(!$user->company->vendorRegistration->isDraft())
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorRegistration.canUpdateOrRenewCheck', function()
{
    $user = \Confide::user();

    if(!$user->company->vendorRegistration->isCompleted())
    {
        throw new InvalidAccessLevelException(trans('vendorManagement.invalidOperation'));
    }
});

Route::filter('vendorProfile.vendorRegistration.isViewerOrOwner', function($route)
{
    $user = \Confide::user();

    if( $user->isSuperAdmin() || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW) ) return;

    $objectId = $route->getParameter('vendorRegistrationId');

    $vendorRegistration = \PCK\VendorRegistration\VendorRegistration::find($objectId);

    if(!$vendorRegistration || $vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.projectTrackRecord.isOwner', function($route)
{
    $user = \Confide::user();

    $objectId = $route->getParameter('trackRecordProjectId');

    $trackRecordProject = PCK\TrackRecordProject\TrackRecordProject::find($objectId);

    if(!$trackRecordProject || $trackRecordProject->vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.projectTrackRecord.isVendorManagementUserOrOwner', function($route)
{
    $user = \Confide::user();

    if( $user->isSuperAdmin() || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION) ) return;

    $objectId = $route->getParameter('trackRecordProjectId');

    $trackRecordProject = PCK\TrackRecordProject\TrackRecordProject::find($objectId);

    if(!$trackRecordProject || $trackRecordProject->vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.supplierCreditFacility.isOwner', function($route)
{
    $user = \Confide::user();

    $objectId = $route->getParameter('supplierCreditFacilitiesId');

    $supplierCreditFacility = PCK\SupplierCreditFacility\SupplierCreditFacility::find($objectId);

    if(!$supplierCreditFacility || $supplierCreditFacility->vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.supplierCreditFacility.isVendorManagementUserOrOwner', function($route)
{
    $user = \Confide::user();

    if( $user->isSuperAdmin() || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION) || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW)) return;

    $objectId = $route->getParameter('supplierCreditFacilitiesId');

    $supplierCreditFacility = PCK\SupplierCreditFacility\SupplierCreditFacility::find($objectId);

    if(!$supplierCreditFacility || $supplierCreditFacility->vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.companyPersonnel.isOwner', function($route)
{
    $user = \Confide::user();

    $objectId = $route->getParameter('companyPersonnelId');

    $companyPersonnel = PCK\CompanyPersonnel\CompanyPersonnel::find($objectId);

    if(!$companyPersonnel || $companyPersonnel->vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.preQualification.isOwner', function($route)
{
    $user = \Confide::user();

    $objectId = $route->getParameter('formId');

    $vendorPreQualification = PCK\VendorPreQualification\VendorPreQualification::where('weighted_node_id', $objectId)->first();

    if(!$vendorPreQualification || $vendorPreQualification->vendorRegistration->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.payment.isOwner', function($route)
{
    $user = \Confide::user();

    $objectId = $route->getParameter('paymentId');

    $payment = PCK\VendorRegistration\Payment\VendorRegistrationPayment::find($objectId);

    if(!$payment || $payment->company_id != $user->company_id)
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagment.vendorProfile.canView', function($route){
    $companyId = $route->getParameter('companyId');

    $user = Confide::user();

    if( ! $user->canViewVendorProfile($companyId) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagment.vendorProfile.canEdit', function($route){
    $user = Confide::user();

    if( ( ! $user->isSuperAdmin() ) && (!\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT)) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagment.vendorRegistration.canView', function($route){
    $companyId = $route->getParameter('companyId');

    $user = Confide::user();

    if( ( ! $user->isSuperAdmin() ) && ( ! in_array($companyId, $user->getAllCompanyIds()) ) && (!\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW)) && (!\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION)) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.hasPermission', function($route){
    $args = func_get_args();

    // unset unused additional parameters
    array_shift($args);
    array_shift($args);

    $permissionType = $args[0];

    $user = Confide::user();

    if( ( ! $user->isSuperAdmin() ) && (!\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, $permissionType)) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.isRegistrationProcessor', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('vendorRegistrationId');

    $vendorRegistration = \PCK\VendorRegistration\VendorRegistration::find($objectId);

    if( !$vendorRegistration || !$vendorRegistration->processor || $vendorRegistration->processor->user_id != $user->id )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorManagement.isRegistrationApprover', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('vendorRegistrationId');

    $vendorRegistration = \PCK\VendorRegistration\VendorRegistration::find($objectId);

    if(!$vendorRegistration) throw new ModelNotFoundException;

    if(!PCK\Verifier\Verifier::isCurrentVerifier($user, $vendorRegistration))
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorManagement.isCompanyDeletable', function($route)
{
    $vendorRegistrationId = $route->getParameter('vendorRegistrationId');
    $vendorRegistration   = \PCK\VendorRegistration\VendorRegistration::find($vendorRegistrationId);
    $user                 = \Confide::user();

    if(is_null($vendorRegistration))
    {
        throw new ModelNotFoundException;
    }

    $canDeleteCompany = $vendorRegistration->isFirst() && (! $vendorRegistration->isCompleted()) && $vendorRegistration->processor && ($vendorRegistration->processor->user_id == $user->id) && $vendorRegistration->isProcessing();

    if( ! $canDeleteCompany )
    {
        throw new InvalidAccessLevelException(trans('filter.companyCannotBeDeleted')); 
    }
});

Route::filter('vendorManagement.vendorProfile.isOwnerOrEditor', function($route)
{
    $vendorProfileId = $route->getParameter('vendorProfileId');

    if(!$vendorProfile = PCK\VendorRegistration\VendorProfile::find($vendorProfileId))
    {
        throw new ModelNotFoundException;
    }

    $user = Confide::user();

    if( ( ! $user->isSuperAdmin() ) && ( ! in_array($vendorProfile->company_id, $user->getAllCompanyIds()) ) && (!\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT)) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.preQualification.node.isProcessorOrOwner', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('nodeId');

    $rootNodeId = \PCK\WeightedNode\WeightedNode::find($objectId)->root_id;

    $preQualification = \PCK\VendorPreQualification\VendorPreQualification::where('weighted_node_id', '=', $rootNodeId)->first();

    $hasPermission = \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION) || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW);

    if( ( ! in_array($preQualification->vendorRegistration->company_id, $user->getAllCompanyIds()) ) && (!$hasPermission) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('vendorManagement.vendorRegistration.canAssign', function($route)
{
    $vendorRegistrationId = $route->getParameter('vendorRegistrationId');

    $vendorRegistration = \PCK\VendorRegistration\VendorRegistration::find($vendorRegistrationId);

    if( ! $vendorRegistration->isSubmitted() && ! $vendorRegistration->isProcessing() )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorPerformanceEvaluation.belongsToFormEvaluatorCompany', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('evaluationId');

    $isEvaluator = PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator::where('user_id', '=', $user->id)->where('vendor_performance_evaluation_id', '=', $objectId)->exists();

    if( ! $isEvaluator )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorPerformanceEvaluation.isEvaluator', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('evaluationId');

    $isEvaluator = PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator::where('user_id', '=', $user->id)->where('vendor_performance_evaluation_id', '=', $objectId)->exists();

    if( ! $isEvaluator )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorPerformanceEvaluation.isFormApprover', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('companyFormId');

    $companyForm = \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm::find($objectId);

    if( ! $companyForm )
    {
        throw new ModelNotFoundException;
    }

    $isProjectUser = \PCK\ContractGroupProjectUsers\ContractGroupProjectUser::where('project_id', '=', $companyForm->vendorPerformanceEvaluation->project_id)->where('user_id', '=', $user->id)->exists();

    if( !$user->isSuperAdmin() && (!in_array($companyForm->evaluator_company_id, $user->getAllCompanyIds()) || !$isProjectUser) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorPerformanceEvaluation.isProjectEditor', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('evaluationId');

    $evaluation = \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation::find($objectId);

    $isProjectEditor = false;

    if($company = $user->getAssignedCompany($evaluation->project))
    {
        $isProjectEditor = $company->isProjectEditor($evaluation->project, $user);
    }

    if( ! $isProjectEditor )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorPerformanceEvaluation.companyForm.isProjectUser', function($route)
{
    $user = Confide::user();

    $objectId = $route->getParameter('companyFormId');

    $form = \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm::find($objectId);

    $isProjectUser = $form->evaluatorCompany->isProjectUser($form->vendorPerformanceEvaluation->project, $user);

    if( ! $isProjectUser )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('companyRegistrationVerifierAccessLevel', function()
{
    $user = Confide::user();

    if( ! $user->canVerifyCompanies() )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('project.isPostContract', function($route, $request)
{
    $user    = \Confide::user();
    $project = $route->getParameter('projectId');

    if( ! $project->isPostContract() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('canPreviewFormOfTenderTemplate', function()
{
    $isProjectCreator = Confide::user()->isProjectCreator();
    $hasModuleAccess  = \PCK\ModulePermission\ModulePermission::hasPermission(Confide::user(), \PCK\ModulePermission\ModulePermission::MODULE_ID_FORM_OF_TENDER_TEMPLATE);

    if( ! ( $isProjectCreator || $hasModuleAccess ) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('moduleAccess', function($route, $request, $moduleId)
{
    if( ! \PCK\ModulePermission\ModulePermission::hasPermission(Confide::user(), $moduleId) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('moduleEditorAccess', function($route, $request, $moduleId)
{
    if( ! \PCK\ModulePermission\ModulePermission::isEditor(Confide::user(), $moduleId) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('moduleOrObjectAccess', function($route, $request, $moduleId, $objectClass)
{
    if( ( ! \PCK\ModulePermission\ModulePermission::hasPermission(Confide::user(), $moduleId) ) && \PCK\General\ObjectPermission::getRecordsByObjectType(Confide::user(), new $objectClass)->isEmpty() )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('forum.thread.isViewable', function($route, $request)
{
    $thread = \PCK\Forum\Thread::find($route->getParameter('threadId'));
    if( ! $thread ) throw new ModelNotFoundException();
    if( ! \PCK\Forum\Thread::find($route->getParameter('threadId'))->isViewable(Confide::user()) )
    {
        throw new ModelNotFoundException();
    }
});

Route::filter('forum.post.canTogglePrivacy', function($route, $request)
{
    $user   = Confide::user();
    $thread = \PCK\Forum\Thread::find($route->getParameter('threadId'));
    if( ( ! $thread->isViewable($user) ) || $user->hasCompanyProjectRole($route->getParameter('projectId'), \PCK\ContractGroups\Types\Role::CONTRACTOR) || ( ! ( $thread->isTypePublic() || $thread->isTypePrivate() ) ) )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('forum.post.isViewable', function($route, $request)
{
    $thread = \PCK\Forum\Thread::find(\PCK\Forum\Post::find($route->getParameter('postId'))->thread_id);
    if( ! $thread ) throw new ModelNotFoundException();
    if( ! \PCK\Forum\Thread::find(\PCK\Forum\Post::find($route->getParameter('postId'))->thread_id)->isViewable(Confide::user()) )
    {
        throw new ModelNotFoundException();
    }
});

Route::filter('forum.isContentCreator', function($route, $request)
{
    $post = \PCK\Forum\Post::find($route->getParameter('postId'));
    if( ! $post ) throw new ModelNotFoundException();
    if( \PCK\Forum\Post::find($route->getParameter('postId'))->created_by != Confide::user()->id )
    {
        throw new ModelNotFoundException();
    }
});

Route::filter('users.companySwitch.switchableCompany', function($route, $request)
{
    $user = \PCK\Users\User::find($route->getParameter('userId'));

    if( ! $user || ! $user->company ) throw new ModelNotFoundException();

    if( ! $user->company->usersCanBeTransferred() )
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('users.companySwitch.transferable', function($route, $request)
{
    $user = \PCK\Users\User::find($route->getParameter('userId'));

    if( ! $user ) throw new ModelNotFoundException();

    if( ! $user->isTransferable() )
    {
        throw new InvalidAccessLevelException(trans('filter.unableToFollowThroughWithRequest'));
    }
});

Route::filter('contractorClaims.canAccess', function($route, $request)
{
    $user = Confide::user();

    $project = $route->getParameter('projectId');

    $allowed = ( ! $user->isSuperAdmin() ) && $user->hasCompanyProjectRole($project, $project->contractorClaimAccessGroups()) && ( $project->isPostContract() );

    if( ! $allowed ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('contractorClaims.canUnlockSubmission', function($route, $request)
{
    $user = Confide::user();

    $project = $route->getParameter('projectId');

    $allowed = ( ! $user->isSuperAdmin() ) && ( ! $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) ) && ( $project->isPostContract() );

    if( ! $allowed ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('contractorClaims.canSubmitClaim', function($route, $request)
{
    $user = Confide::user();

    $project = $route->getParameter('projectId');

    if( ! $user->canSubmitClaim($project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('claimRevision.certApproved', function($route, $request)
{
    $user = Confide::user();

    $project = $route->getParameter('projectId');

    $claimRevision = $route->getParameter('claimRevisionId');
    $claimRevision = PCK\Buildspace\PostContractClaimRevision::find($route->getParameter('claimRevisionId'));

    if( $claimRevision->claimCertificate->status != PCK\Buildspace\ClaimCertificate::STATUS_TYPE_APPROVED ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('projectSectionalCompletionDate.maintain.permissionCheck', function($route)
{
    $user    = Confide::user();
    $project = $route->getParameter('projectId');

    $buCompany  = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::PROJECT_OWNER))->first();
    $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::GROUP_CONTRACT))->first();
    
    $editorIds = $buCompany->getProjectEditors($project)->lists('id');

    if($gcdCompany)
    {
        $editorIds = array_merge($editorIds, $gcdCompany->getProjectEditors($project)->lists('id'));
    }

    $isBuOrGcdEditor = in_array($user->id, $editorIds);

    if(!$isBuOrGcdEditor)
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

/*
|--------------------------------------------------------------------------
| Company Level Filter
|--------------------------------------------------------------------------
|
|
*/

Route::filter('superAdminAccessLevel', function()
{
    $user = Confide::user();

    if( ! $user->isSuperAdmin() )
    {
        throw new InvalidAccessLevelException(trans('filter.notSuperAdminPermissionDenied'));
    }
});

Route::filter('companyAdminAccessLevel', function()
{
    $user = Confide::user();

    if( ! $user->isGroupAdmin() )
    {
        throw new InvalidAccessLevelException(trans('filter.notCompanyAdminPermissionDenied'));
    }
});

Route::filter('superAdminCompanyAdminAccessLevel', function()
{
    $user = Confide::user();

    if( ! $user->isGroupAdmin() and ! $user->isSuperAdmin() )
    {
        throw new InvalidAccessLevelException(trans('filter.notSuperAdminOrCompanyAdminPermissionDenied'));
    }
});

Route::filter('moduleAccessOrSuperAdminCompanyAdmin', function($route, $request, $moduleIds)
{
    if( \PCK\Filters\ModulePermissionFilter::isPermittedInAny(Confide::user(), $moduleIds) ) return;

    Route::callRouteFilter('superAdminCompanyAdminAccessLevel', array(), $route, $request);
});

Route::filter('companyOwnerChecking', function(IlluminateRoute $route)
{
    $companyId = $route->getParameter('companyId');

    $user = Confide::user();

    // if current user is not super admin, we will check the requested company id
    // with the assigned company id is the same or not, if not then throw
    // exception
    if( ( ! $user->isSuperAdmin() ) && ( ! in_array($companyId, $user->getAllCompanyIds()) ) )
    {
        throw new ModelNotFoundException;
    }
});

Route::filter('moduleAccessOrCompanyOwner', function(IlluminateRoute $route, $request, $moduleIds)
{
    if( \PCK\Filters\ModulePermissionFilter::isPermittedInAny(Confide::user(), $moduleIds) ) return;

    Route::callRouteFilter('companyOwnerChecking', array(), $route, $request);
});

Route::filter('companyConfirmed', function(IlluminateRoute $route)
{
    $companyId = $route->getParameter('companyId');

    $company = \PCK\Companies\Company::find($companyId);

    if( ! $company->confirmed )
    {
        throw new InvalidAccessLevelException(trans('filter.companyNotVerified'));
    }
});

Route::filter('canEditCompanyDetails', function(IlluminateRoute $route)
{
    if( ! \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT) ) return;

    $user = \Confide::user();

    if( $user->isSuperAdmin() ) return;

    $companyId = $route->getParameter('companyId');

    $company = \PCK\Companies\Company::find($companyId);

    if($company->contractGroupCategory->type == \PCK\ContractGroupCategory\ContractGroupCategory::TYPE_EXTERNAL)
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('companyNotConfirmed', function(IlluminateRoute $route)
{
    $companyId = $route->getParameter('companyId');

    $company = \PCK\Companies\Company::find($companyId);

    if( $company->confirmed )
    {
        throw new InvalidAccessLevelException(trans('filter.companyAlreadyVerified'));
    }
});

Route::filter('isCurrentUser', function(IlluminateRoute $route)
{
    $userId = $route->getParameter('currentUserId');

    if( $userId != Confide::user()->id ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
});

Route::filter('folders.canView', function(IlluminateRoute $route)
{
    $fileNodeId = $route->getParameter('folderId');

    if($fileNodeId == 0) return;

    if(!in_array(\Confide::user()->id, \PCK\Folder\FileNodePermission::getViewerIds($fileNodeId)))
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('folders.canEdit', function(IlluminateRoute $route)
{
    $fileNodeId = $route->getParameter('folderId');

    if($fileNodeId == 0) return;

    if(!in_array(\Confide::user()->id, \PCK\Folder\FileNodePermission::getEditorIds($fileNodeId)))
    {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

Route::filter('vendorManagementMigrationModeAccess', function()
{
    $vmMigrationMode = getenv('VENDOR_MANAGEMENT_MIGRATION_MODE');
    
    if( ! $vmMigrationMode ) {
        throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function(IlluminateRoute $route)
{
    // Todo: Verify that the requests are coming from BuildSpace.

    $byPassRoutes = array(
        'buildspace.api.to_tendering',
        'buildspace.api.addendum',
        'buildspace.api.costData.master.access',
        'buildspace.api.costData.access',
        'buildspace.api.get_default_tendering_stage_users',
        'buildspace.api.get_default_post_contract_stage_users',
        'buildspace.api.get_contract_management_verifiers',
        'buildspace.api.notifications.contractManagement.claim.review',
        'buildspace.api.notifications.contractManagement.review',
        'buildspace.api.project.stage.push.postContract',
        'buildspace.api.project.bq.editor.access',
        'buildspace.api.tendererRates.submit',
        'buildspace.api.subsidiary.getFullName',
        'buildspace.api.license.validity.check',
        'buildspace.api.rounded.amount.get',
        'buildspace.api.proportion.groupedby.id.get',
        'buildspace.api.subsidiary.hierarchical.collection.get',
        'buildspace.api.notifications.claimRevision.new',
        'buildspace.api.notifications.claimSubmitted',
        'buildspace.api.notifications.claimApproved',
        'buildspace.api.postContractClaim.topManagement.verifiers.get',
        'api.acknowledged.records',
        'api.sync.records',
        'api.upload',
        'api.defects.create',
        'api.defects.update',
        'api.defects.delete',
        'api.defects.categories.create',
        'api.defects.categories.update',
        'api.defects.categories.delete',
        'api.v2.create',
        'api.v2.update',
        'api.v2.delete',
        'api.insert-lms-user',
        'api.account-code-settings.create',
        'api.account-code-settings.update',
        'api.account-code-settings.delete',
        'api.additional-element-values.create',
        'api.additional-element-values.update',
        'api.additional-element-values.delete',
        'api.additional-expenses.create',
        'api.additional-expenses.update',
        'api.additional-expenses.delete',
        'api.additional-expense-interim-claims.create',
        'api.additional-expense-interim-claims.update',
        'api.additional-expense-interim-claims.delete',
        'api.ae-contractor-confirm-delays.create',
        'api.ae-contractor-confirm-delays.update',
        'api.ae-contractor-confirm-delays.delete',
        'api.ae-first-level-messages.create',
        'api.ae-first-level-messages.update',
        'api.ae-first-level-messages.delete',
        'api.ae-fourth-level-messages.create',
        'api.ae-fourth-level-messages.update',
        'api.ae-fourth-level-messages.delete',
        'api.accounting-report-export-logs.create',
        'api.accounting-report-export-logs.update',
        'api.accounting-report-export-logs.delete',
        'api.accounting-report-export-log-item-codes.create',
        'api.accounting-report-export-log-item-codes.update',
        'api.accounting-report-export-log-item-codes.delete',
        'api.acknowledgement-letters.create',
        'api.acknowledgement-letters.update',
        'api.acknowledgement-letters.delete',
        'api.additional-expense-claims.create',
        'api.additional-expense-claims.update',
        'api.additional-expense-claims.delete',
        'api.ae-third-level-messages.create',
        'api.ae-third-level-messages.update',
        'api.ae-third-level-messages.delete',
        'api.ai-third-level-messages.create',
        'api.ai-third-level-messages.update',
        'api.ai-third-level-messages.delete',
        'api.architect-instruction-engineer-instruction.create',
        'api.architect-instruction-engineer-instruction.update',
        'api.architect-instruction-engineer-instruction.delete',
        'api.apportionment-types.create',
        'api.apportionment-types.update',
        'api.apportionment-types.delete',
        'api.architect-instruction-interim-claims.create',
        'api.architect-instruction-interim-claims.update',
        'api.architect-instruction-interim-claims.delete',
        'api.architect-instruction-messages.create',
        'api.architect-instruction-messages.update',
        'api.architect-instruction-messages.delete',
        'api.attached-clause-items.create',
        'api.attached-clause-items.update',
        'api.attached-clause-items.delete',
        'api.calendar-settings.create',
        'api.calendar-settings.update',
        'api.calendar-settings.delete',
        'api.claim-certificate-email-logs.create',
        'api.claim-certificate-email-logs.update',
        'api.claim-certificate-email-logs.delete',
        'api.cidb-grades.create',
        'api.cidb-grades.update',
        'api.cidb-grades.delete',
        'api.assign-companies-logs.create',
        'api.assign-companies-logs.update',
        'api.assign-companies-logs.delete',
        'api.assign-company-in-detail-logs.create',
        'api.assign-company-in-detail-logs.update',
        'api.assign-company-in-detail-logs.delete',
        'api.authentication-logs.create',
        'api.authentication-logs.update',
        'api.authentication-logs.delete',
        'api.calendars.create',
        'api.calendars.update',
        'api.calendars.delete',
        'api.claim-certificate-invoice-information-update-logs.create',
        'api.claim-certificate-invoice-information-update-logs.update',
        'api.claim-certificate-invoice-information-update-logs.delete',
        'api.building-information-modelling-levels.create',
        'api.building-information-modelling-levels.update',
        'api.building-information-modelling-levels.delete',
        'api.business-entity-types.create',
        'api.business-entity-types.update',
        'api.business-entity-types.delete',
        'api.cidb-codes.create',
        'api.cidb-codes.update',
        'api.cidb-codes.delete',
        'api.company-cidb-code.create',
        'api.company-cidb-code.update',
        'api.company-cidb-code.delete',
        'api.claim-certificate-payment-notification-logs.create',
        'api.claim-certificate-payment-notification-logs.update',
        'api.claim-certificate-payment-notification-logs.delete',
        'api.company-detail-attachment-settings.create',
        'api.company-detail-attachment-settings.update',
        'api.company-detail-attachment-settings.delete',
        'api.claim-certificate-payments.create',
        'api.claim-certificate-payments.update',
        'api.claim-certificate-payments.delete',
        'api.claim-certificate-print-logs.create',
        'api.claim-certificate-print-logs.update',
        'api.claim-certificate-print-logs.delete',
        'api.clauses.create',
        'api.clauses.update',
        'api.clauses.delete',
        'api.company-imported-users.create',
        'api.company-imported-users.update',
        'api.company-imported-users.delete',
        'api.company-imported-users-log.create',
        'api.company-imported-users-log.update',
        'api.company-imported-users-log.delete',
        'api.company-personnel-settings.create',
        'api.company-personnel-settings.update',
        'api.company-personnel-settings.delete',
        'api.company-tender-calling-tender-information.create',
        'api.company-tender-calling-tender-information.update',
        'api.company-tender-calling-tender-information.delete',
        'api.company-tender-lot-information.create',
        'api.company-tender-lot-information.update',
        'api.company-tender-lot-information.delete',
        'api.company-tender-rot-information.create',
        'api.company-tender-rot-information.update',
        'api.company-tender-rot-information.delete',
        'api.company-tender-tender-alternatives.create',
        'api.company-tender-tender-alternatives.update',
        'api.company-tender-tender-alternatives.delete',
        'api.company-project.create',
        'api.company-project.update',
        'api.company-project.delete',
        'api.company-property-developers.create',
        'api.company-property-developers.update',
        'api.company-property-developers.delete',
        'api.company-temporary-details.create',
        'api.company-temporary-details.update',
        'api.company-temporary-details.delete',
        'api.company-tender.create',
        'api.company-tender.update',
        'api.company-tender.delete',
        'api.company-vendor-category.create',
        'api.company-vendor-category.update',
        'api.company-vendor-category.delete',
        'api.consultant-management-approval-document-section-e.create',
        'api.consultant-management-approval-document-section-e.update',
        'api.consultant-management-approval-document-section-e.delete',
        'api.consultant-management-consultant-attachments.create',
        'api.consultant-management-consultant-attachments.update',
        'api.consultant-management-consultant-attachments.delete',
        'api.consultant-management-consultant-questionnaires.create',
        'api.consultant-management-consultant-questionnaires.update',
        'api.consultant-management-consultant-questionnaires.delete',
        'api.consultant-management-calling-rfp.create',
        'api.consultant-management-calling-rfp.update',
        'api.consultant-management-calling-rfp.delete',
        'api.consultant-management-company-role-logs.create',
        'api.consultant-management-company-role-logs.update',
        'api.consultant-management-company-role-logs.delete',
        'api.consultant-management-approval-documents.create',
        'api.consultant-management-approval-documents.update',
        'api.consultant-management-approval-documents.delete',
        'api.consultant-management-approval-document-section-appendix.create',
        'api.consultant-management-approval-document-section-appendix.update',
        'api.consultant-management-approval-document-section-appendix.delete',
        'api.consultant-management-approval-document-section-c.create',
        'api.consultant-management-approval-document-section-c.update',
        'api.consultant-management-approval-document-section-c.delete',
        'api.consultant-management-approval-document-section-d.create',
        'api.consultant-management-approval-document-section-d.update',
        'api.consultant-management-approval-document-section-d.delete',
        'api.consultant-management-approval-document-section-b.create',
        'api.consultant-management-approval-document-section-b.update',
        'api.consultant-management-approval-document-section-b.delete',
        'api.consultant-management-approval-document-verifiers.create',
        'api.consultant-management-approval-document-verifiers.update',
        'api.consultant-management-approval-document-verifiers.delete',
        'api.consultant-management-approval-document-verifier-versions.create',
        'api.consultant-management-approval-document-verifier-versions.update',
        'api.consultant-management-approval-document-verifier-versions.delete',
        'api.consultant-management-consultant-questionnaire-replies.create',
        'api.consultant-management-consultant-questionnaire-replies.update',
        'api.consultant-management-consultant-questionnaire-replies.delete',
        'api.consultant-management-calling-rfp-verifiers.create',
        'api.consultant-management-calling-rfp-verifiers.update',
        'api.consultant-management-calling-rfp-verifiers.delete',
        'api.consultant-management-calling-rfp-companies.create',
        'api.consultant-management-calling-rfp-companies.update',
        'api.consultant-management-calling-rfp-companies.delete',
        'api.consultant-management-call-rfp-verifier-versions.create',
        'api.consultant-management-call-rfp-verifier-versions.update',
        'api.consultant-management-call-rfp-verifier-versions.delete',
        'api.consultant-management-consultant-rfp-questionnaire-replies.create',
        'api.consultant-management-consultant-rfp-questionnaire-replies.update',
        'api.consultant-management-consultant-rfp-questionnaire-replies.delete',
        'api.consultant-management-consultant-rfp.create',
        'api.consultant-management-consultant-rfp.update',
        'api.consultant-management-consultant-rfp.delete',
        'api.consultant-management-consultant-rfp-reply-attachments.create',
        'api.consultant-management-consultant-rfp-reply-attachments.update',
        'api.consultant-management-consultant-rfp-reply-attachments.delete',
        'api.consultant-management-consultant-rfp-common-information.create',
        'api.consultant-management-consultant-rfp-common-information.update',
        'api.consultant-management-consultant-rfp-common-information.delete',
        'api.consultant-management-letter-of-award-clauses.create',
        'api.consultant-management-letter-of-award-clauses.update',
        'api.consultant-management-letter-of-award-clauses.delete',
        'api.consultant-management-letter-of-award-template-clauses.create',
        'api.consultant-management-letter-of-award-template-clauses.update',
        'api.consultant-management-letter-of-award-template-clauses.delete',
        'api.consultant-management-contracts.create',
        'api.consultant-management-contracts.update',
        'api.consultant-management-contracts.delete',
        'api.consultant-management-consultant-rfp-proposed-fees.create',
        'api.consultant-management-consultant-rfp-proposed-fees.update',
        'api.consultant-management-consultant-rfp-proposed-fees.delete',
        'api.consultant-management-consultant-users.create',
        'api.consultant-management-consultant-users.update',
        'api.consultant-management-consultant-users.delete',
        'api.consultant-management-exclude-attachment-settings.create',
        'api.consultant-management-exclude-attachment-settings.update',
        'api.consultant-management-exclude-attachment-settings.delete',
        'api.consultant-management-consultant-reply-attachments.create',
        'api.consultant-management-consultant-reply-attachments.update',
        'api.consultant-management-consultant-reply-attachments.delete',
        'api.consultant-management-consultant-rfp-attachments.create',
        'api.consultant-management-consultant-rfp-attachments.update',
        'api.consultant-management-consultant-rfp-attachments.delete',
        'api.consultant-management-exclude-questionnaires.create',
        'api.consultant-management-exclude-questionnaires.update',
        'api.consultant-management-exclude-questionnaires.delete',
        'api.consultant-management-letter-of-award-attachments.create',
        'api.consultant-management-letter-of-award-attachments.update',
        'api.consultant-management-letter-of-award-attachments.delete',
        'api.consultant-management-list-of-consultants.create',
        'api.consultant-management-list-of-consultants.update',
        'api.consultant-management-list-of-consultants.delete',
        'api.consultant-management-letter-of-award-templates.create',
        'api.consultant-management-letter-of-award-templates.update',
        'api.consultant-management-letter-of-award-templates.delete',
        'api.consultant-management-list-of-consultant-verifiers.create',
        'api.consultant-management-list-of-consultant-verifiers.update',
        'api.consultant-management-list-of-consultant-verifiers.delete',
        'api.consultant-management-product-types.create',
        'api.consultant-management-product-types.update',
        'api.consultant-management-product-types.delete',
        'api.consultant-management-loa-subsidiary-running-numbers.create',
        'api.consultant-management-loa-subsidiary-running-numbers.update',
        'api.consultant-management-loa-subsidiary-running-numbers.delete',
        'api.consultant-management-questionnaires.create',
        'api.consultant-management-questionnaires.update',
        'api.consultant-management-questionnaires.delete',
        'api.consultant-management-letter-of-awards.create',
        'api.consultant-management-letter-of-awards.update',
        'api.consultant-management-letter-of-awards.delete',
        'api.consultant-management-open-rfp-verifiers.create',
        'api.consultant-management-open-rfp-verifiers.update',
        'api.consultant-management-open-rfp-verifiers.delete',
        'api.consultant-management-open-rfp-verifier-versions.create',
        'api.consultant-management-open-rfp-verifier-versions.update',
        'api.consultant-management-open-rfp-verifier-versions.delete',
        'api.consultant-management-questionnaire-options.create',
        'api.consultant-management-questionnaire-options.update',
        'api.consultant-management-questionnaire-options.delete',
        'api.consultant-management-letter-of-award-verifiers.create',
        'api.consultant-management-letter-of-award-verifiers.update',
        'api.consultant-management-letter-of-award-verifiers.delete',
        'api.consultant-management-letter-of-award-verifier-versions.create',
        'api.consultant-management-letter-of-award-verifier-versions.update',
        'api.consultant-management-letter-of-award-verifier-versions.delete',
        'api.consultant-management-list-of-consultant-companies.create',
        'api.consultant-management-list-of-consultant-companies.update',
        'api.consultant-management-list-of-consultant-companies.delete',
        'api.consultant-management-loc-verifier-versions.create',
        'api.consultant-management-loc-verifier-versions.update',
        'api.consultant-management-loc-verifier-versions.delete',
        'api.consultant-management-recommendation-of-consultant-companies.create',
        'api.consultant-management-recommendation-of-consultant-companies.update',
        'api.consultant-management-recommendation-of-consultant-companies.delete',
        'api.consultant-management-recommendation-of-consultant-verifiers.create',
        'api.consultant-management-recommendation-of-consultant-verifiers.update',
        'api.consultant-management-recommendation-of-consultant-verifiers.delete',
        'api.consultant-management-rfp-resubmission-verifiers.create',
        'api.consultant-management-rfp-resubmission-verifiers.update',
        'api.consultant-management-rfp-resubmission-verifiers.delete',
        'api.consultant-management-rfp-interview-tokens.create',
        'api.consultant-management-rfp-interview-tokens.update',
        'api.consultant-management-rfp-interview-tokens.delete',
        'api.consultant-management-rfp-interview-consultants.create',
        'api.consultant-management-rfp-interview-consultants.update',
        'api.consultant-management-rfp-interview-consultants.delete',
        'api.consultant-management-rfp-questionnaires.create',
        'api.consultant-management-rfp-questionnaires.update',
        'api.consultant-management-rfp-questionnaires.delete',
        'api.consultant-management-rfp-interviews.create',
        'api.consultant-management-rfp-interviews.update',
        'api.consultant-management-rfp-interviews.delete',
        'api.consultant-management-rfp-questionnaire-options.create',
        'api.consultant-management-rfp-questionnaire-options.update',
        'api.consultant-management-rfp-questionnaire-options.delete',
        'api.consultant-management-roles-contract-group-categories.create',
        'api.consultant-management-roles-contract-group-categories.update',
        'api.consultant-management-roles-contract-group-categories.delete',
        'api.consultant-management-rfp-revisions.create',
        'api.consultant-management-rfp-revisions.update',
        'api.consultant-management-rfp-revisions.delete',
        'api.consultant-management-rfp-attachment-settings.create',
        'api.consultant-management-rfp-attachment-settings.update',
        'api.consultant-management-rfp-attachment-settings.delete',
        'api.consultant-management-rfp-documents.create',
        'api.consultant-management-rfp-documents.update',
        'api.consultant-management-rfp-documents.delete',
        'api.consultant-management-rfp-resubmission-verifier-versions.create',
        'api.consultant-management-rfp-resubmission-verifier-versions.update',
        'api.consultant-management-rfp-resubmission-verifier-versions.delete',
        'api.consultant-management-recommendation-of-consultants.create',
        'api.consultant-management-recommendation-of-consultants.update',
        'api.consultant-management-recommendation-of-consultants.delete',
        'api.consultant-management-roc-verifier-versions.create',
        'api.consultant-management-roc-verifier-versions.update',
        'api.consultant-management-roc-verifier-versions.delete',
        'api.consultant-management-section-d-details.create',
        'api.consultant-management-section-d-details.update',
        'api.consultant-management-section-d-details.delete',
        'api.consultant-management-section-d-service-fees.create',
        'api.consultant-management-section-d-service-fees.update',
        'api.consultant-management-section-d-service-fees.delete',
        'api.contract-group-categories.create',
        'api.contract-group-categories.update',
        'api.contract-group-categories.delete',
        'api.consultant-management-vendor-categories-rfp.create',
        'api.consultant-management-vendor-categories-rfp.update',
        'api.consultant-management-vendor-categories-rfp.delete',
        'api.contract-group-category-privileges.create',
        'api.contract-group-category-privileges.update',
        'api.contract-group-category-privileges.delete',
        'api.contract-groups.create',
        'api.contract-groups.update',
        'api.contract-groups.delete',
        'api.consultant-management-subsidiaries.create',
        'api.consultant-management-subsidiaries.update',
        'api.consultant-management-subsidiaries.delete',
        'api.consultant-management-section-appendix-details.create',
        'api.consultant-management-section-appendix-details.update',
        'api.consultant-management-section-appendix-details.delete',
        'api.consultant-management-section-c-details.create',
        'api.consultant-management-section-c-details.update',
        'api.consultant-management-section-c-details.delete',
        'api.consultant-management-user-roles.create',
        'api.consultant-management-user-roles.update',
        'api.consultant-management-user-roles.delete',
        'api.consultant-management-vendor-categories-rfp-account-code.create',
        'api.consultant-management-vendor-categories-rfp-account-code.update',
        'api.consultant-management-vendor-categories-rfp-account-code.delete',
        'api.contract-group-contract-group-category.create',
        'api.contract-group-contract-group-category.update',
        'api.contract-group-contract-group-category.delete',
        'api.contract-group-conversation.create',
        'api.contract-group-conversation.update',
        'api.contract-group-conversation.delete',
        'api.contract-group-document-management-folder.create',
        'api.contract-group-document-management-folder.update',
        'api.contract-group-document-management-folder.delete',
        'api.contract-group-project-users.create',
        'api.contract-group-project-users.update',
        'api.contract-group-project-users.delete',
        'api.contract-group-tender-document-permission-logs.create',
        'api.contract-group-tender-document-permission-logs.update',
        'api.contract-group-tender-document-permission-logs.delete',
        'api.contract-management-user-permissions.create',
        'api.contract-management-user-permissions.update',
        'api.contract-management-user-permissions.delete',
        'api.contract-limits.create',
        'api.contract-limits.update',
        'api.contract-limits.delete',
        'api.contractor-questionnaire-replies.create',
        'api.contractor-questionnaire-replies.update',
        'api.contractor-questionnaire-replies.delete',
        'api.contractor-questionnaire-reply-attachments.create',
        'api.contractor-questionnaire-reply-attachments.update',
        'api.contractor-questionnaire-reply-attachments.delete',
        'api.contractor-work-subcategory.create',
        'api.contractor-work-subcategory.update',
        'api.contractor-work-subcategory.delete',
        'api.contractor-registration-statuses.create',
        'api.contractor-registration-statuses.update',
        'api.contractor-registration-statuses.delete',
        'api.contractor-questionnaires.create',
        'api.contractor-questionnaires.update',
        'api.contractor-questionnaires.delete',
        'api.contractors.create',
        'api.contractors.update',
        'api.contractors.delete',
        'api.cost-data.create',
        'api.cost-data.update',
        'api.cost-data.delete',
        'api.project-statuses.create',
        'api.project-statuses.update',
        'api.project-statuses.delete',
        'api.contracts.create',
        'api.contracts.update',
        'api.contracts.delete',
        'api.conversations.create',
        'api.conversations.update',
        'api.conversations.delete',
        'api.contractors-commitment-status-logs.create',
        'api.contractors-commitment-status-logs.update',
        'api.contractors-commitment-status-logs.delete',
        'api.conversation-reply-messages.create',
        'api.conversation-reply-messages.update',
        'api.conversation-reply-messages.delete',
        'api.contractor-questionnaire-questions.create',
        'api.contractor-questionnaire-questions.update',
        'api.contractor-questionnaire-questions.delete',
        'api.contractor-questionnaire-options.create',
        'api.contractor-questionnaire-options.update',
        'api.contractor-questionnaire-options.delete',
        'api.daily-report.create',
        'api.daily-report.update',
        'api.daily-report.delete',
        'api.dashboard-groups.create',
        'api.dashboard-groups.update',
        'api.dashboard-groups.delete',
        'api.directed-to.create',
        'api.directed-to.update',
        'api.directed-to.delete',
        'api.dynamic-forms.create',
        'api.dynamic-forms.update',
        'api.dynamic-forms.delete',
        'api.e-bidding-email-reminders.create',
        'api.e-bidding-email-reminders.update',
        'api.e-bidding-email-reminders.delete',
        'api.document-management-folders.create',
        'api.document-management-folders.update',
        'api.document-management-folders.delete',
        'api.current-cpe-grades.create',
        'api.current-cpe-grades.update',
        'api.current-cpe-grades.delete',
        'api.currency-settings.create',
        'api.currency-settings.update',
        'api.currency-settings.delete',
        'api.daily-labour-reports.create',
        'api.daily-labour-reports.update',
        'api.daily-labour-reports.delete',
        'api.dashboard-groups-excluded-projects.create',
        'api.dashboard-groups-excluded-projects.update',
        'api.dashboard-groups-excluded-projects.delete',
        'api.dashboard-groups-users.create',
        'api.dashboard-groups-users.update',
        'api.dashboard-groups-users.delete',
        'api.defect-categories.create',
        'api.defect-categories.update',
        'api.defect-categories.delete',
        'api.defect-category-pre-defined-location-code.create',
        'api.defect-category-pre-defined-location-code.update',
        'api.defect-category-pre-defined-location-code.delete',
        'api.defects.create',
        'api.defects.update',
        'api.defects.delete',
        'api.development-types-product-types.create',
        'api.development-types-product-types.update',
        'api.development-types-product-types.delete',
        'api.document-control-objects.create',
        'api.document-control-objects.update',
        'api.document-control-objects.delete',
        'api.e-bidding-committees.create',
        'api.e-bidding-committees.update',
        'api.e-bidding-committees.delete',
        'api.element-attributes.create',
        'api.element-attributes.update',
        'api.element-attributes.delete',
        'api.email-announcement-recipients.create',
        'api.email-announcement-recipients.update',
        'api.email-announcement-recipients.delete',
        'api.engineer-instructions.create',
        'api.engineer-instructions.update',
        'api.engineer-instructions.delete',
        'api.elements.create',
        'api.elements.update',
        'api.elements.delete',
        'api.email-notifications.create',
        'api.email-notifications.update',
        'api.email-notifications.delete',
        'api.email-notification-settings.create',
        'api.email-notification-settings.update',
        'api.email-notification-settings.delete',
        'api.element-definitions.create',
        'api.element-definitions.update',
        'api.element-definitions.delete',
        'api.email-reminder-settings.create',
        'api.email-reminder-settings.update',
        'api.email-reminder-settings.delete',
        'api.email-settings.create',
        'api.email-settings.update',
        'api.email-settings.delete',
        'api.element-values.create',
        'api.element-values.update',
        'api.element-values.delete',
        'api.element-rejections.create',
        'api.element-rejections.update',
        'api.element-rejections.delete',
        'api.email-announcements.create',
        'api.email-announcements.update',
        'api.email-announcements.delete',
        'api.e-biddings.create',
        'api.e-biddings.update',
        'api.e-biddings.delete',
        'api.extension-of-times.create',
        'api.extension-of-times.update',
        'api.extension-of-times.delete',
        'api.eot-fourth-level-messages.create',
        'api.eot-fourth-level-messages.update',
        'api.eot-fourth-level-messages.delete',
        'api.eot-second-level-messages.create',
        'api.eot-second-level-messages.update',
        'api.eot-second-level-messages.delete',
        'api.eot-third-level-messages.create',
        'api.eot-third-level-messages.update',
        'api.eot-third-level-messages.delete',
        'api.external-application-attributes.create',
        'api.external-application-attributes.update',
        'api.external-application-attributes.delete',
        'api.expression-of-interest-tokens.create',
        'api.expression-of-interest-tokens.update',
        'api.expression-of-interest-tokens.delete',
        'api.eot-first-level-messages.create',
        'api.eot-first-level-messages.update',
        'api.eot-first-level-messages.delete',
        'api.external-app-attachments.create',
        'api.external-app-attachments.update',
        'api.external-app-attachments.delete',
        'api.external-app-company-attachments.create',
        'api.external-app-company-attachments.update',
        'api.external-app-company-attachments.delete',
        'api.eot-contractor-confirm-delays.create',
        'api.eot-contractor-confirm-delays.update',
        'api.eot-contractor-confirm-delays.delete',
        'api.extension-of-time-claims.create',
        'api.extension-of-time-claims.update',
        'api.extension-of-time-claims.delete',
        'api.external-application-client-outbound-logs.create',
        'api.external-application-client-outbound-logs.update',
        'api.external-application-client-outbound-logs.delete',
        'api.external-application-identifiers.create',
        'api.external-application-identifiers.update',
        'api.external-application-identifiers.delete',
        'api.external-application-client-outbound-authorizations.create',
        'api.external-application-client-outbound-authorizations.update',
        'api.external-application-client-outbound-authorizations.delete',
        'api.file-node-permissions.create',
        'api.file-node-permissions.update',
        'api.file-node-permissions.delete',
        'api.failed-jobs.create',
        'api.failed-jobs.update',
        'api.failed-jobs.delete',
        'api.finance-user-subsidiaries.create',
        'api.finance-user-subsidiaries.update',
        'api.finance-user-subsidiaries.delete',
        'api.form-of-tender-clauses.create',
        'api.form-of-tender-clauses.update',
        'api.form-of-tender-clauses.delete',
        'api.external-application-clients.create',
        'api.external-application-clients.update',
        'api.external-application-clients.delete',
        'api.file-nodes.create',
        'api.file-nodes.update',
        'api.file-nodes.delete',
        'api.form-columns.create',
        'api.form-columns.update',
        'api.form-columns.delete',
        'api.form-element-mappings.create',
        'api.form-element-mappings.update',
        'api.form-element-mappings.delete',
        'api.form-object-mappings.create',
        'api.form-object-mappings.update',
        'api.form-object-mappings.delete',
        'api.form-of-tenders.create',
        'api.form-of-tenders.update',
        'api.form-of-tenders.delete',
        'api.form-of-tender-addresses.create',
        'api.form-of-tender-addresses.update',
        'api.form-of-tender-addresses.delete',
        'api.form-of-tender-logs.create',
        'api.form-of-tender-logs.update',
        'api.form-of-tender-logs.delete',
        'api.form-of-tender-tender-alternatives.create',
        'api.form-of-tender-tender-alternatives.update',
        'api.form-of-tender-tender-alternatives.delete',
        'api.forum-posts.create',
        'api.forum-posts.update',
        'api.forum-posts.delete',
        'api.forum-posts-read-log.create',
        'api.forum-posts-read-log.update',
        'api.forum-posts-read-log.delete',
        'api.general-settings.create',
        'api.general-settings.update',
        'api.general-settings.delete',
        'api.indonesia-civil-contract-contractual-claim-responses.create',
        'api.indonesia-civil-contract-contractual-claim-responses.update',
        'api.indonesia-civil-contract-contractual-claim-responses.delete',
        'api.form-of-tender-headers.create',
        'api.form-of-tender-headers.update',
        'api.form-of-tender-headers.delete',
        'api.form-of-tender-print-settings.create',
        'api.form-of-tender-print-settings.update',
        'api.form-of-tender-print-settings.delete',
        'api.forum-threads.create',
        'api.forum-threads.update',
        'api.forum-threads.delete',
        'api.forum-thread-privacy-log.create',
        'api.forum-thread-privacy-log.update',
        'api.forum-thread-privacy-log.delete',
        'api.forum-thread-user.create',
        'api.forum-thread-user.update',
        'api.forum-thread-user.delete',
        'api.ic-info-gross-values-attachments.create',
        'api.ic-info-gross-values-attachments.update',
        'api.ic-info-gross-values-attachments.delete',
        'api.ic-info-nett-addition-omission-attachments.create',
        'api.ic-info-nett-addition-omission-attachments.update',
        'api.ic-info-nett-addition-omission-attachments.delete',
        'api.indonesia-civil-contract-architect-instructions.create',
        'api.indonesia-civil-contract-architect-instructions.update',
        'api.indonesia-civil-contract-architect-instructions.delete',
        'api.indonesia-civil-contract-ai-rfi.create',
        'api.indonesia-civil-contract-ai-rfi.update',
        'api.indonesia-civil-contract-ai-rfi.delete',
        'api.e-bidding-rankings.create',
        'api.e-bidding-rankings.update',
        'api.e-bidding-rankings.delete',
        'api.indonesia-civil-contract-ew-le.create',
        'api.indonesia-civil-contract-ew-le.update',
        'api.indonesia-civil-contract-ew-le.delete',
        'api.indonesia-civil-contract-information.create',
        'api.indonesia-civil-contract-information.update',
        'api.indonesia-civil-contract-information.delete',
        'api.inspection-results.create',
        'api.inspection-results.update',
        'api.inspection-results.delete',
        'api.inspection-submitters.create',
        'api.inspection-submitters.update',
        'api.inspection-submitters.delete',
        'api.inspection-lists.create',
        'api.inspection-lists.update',
        'api.inspection-lists.delete',
        'api.indonesia-civil-contract-extensions-of-time.create',
        'api.indonesia-civil-contract-extensions-of-time.update',
        'api.indonesia-civil-contract-extensions-of-time.delete',
        'api.indonesia-civil-contract-loss-and-expenses.create',
        'api.indonesia-civil-contract-loss-and-expenses.update',
        'api.indonesia-civil-contract-loss-and-expenses.delete',
        'api.inspection-groups.create',
        'api.inspection-groups.update',
        'api.inspection-groups.delete',
        'api.inspection-group-inspection-list-category.create',
        'api.inspection-group-inspection-list-category.update',
        'api.inspection-group-inspection-list-category.delete',
        'api.inspection-list-categories.create',
        'api.inspection-list-categories.update',
        'api.inspection-list-categories.delete',
        'api.inspection-group-users.create',
        'api.inspection-group-users.update',
        'api.inspection-group-users.delete',
        'api.inspection-roles.create',
        'api.inspection-roles.update',
        'api.inspection-roles.delete',
        'api.inspection-list-items.create',
        'api.inspection-list-items.update',
        'api.inspection-list-items.delete',
        'api.inspection-item-results.create',
        'api.inspection-item-results.update',
        'api.inspection-item-results.delete',
        'api.inspection-list-category-additional-fields.create',
        'api.inspection-list-category-additional-fields.update',
        'api.inspection-list-category-additional-fields.delete',
        'api.inspection-verifier-template.create',
        'api.inspection-verifier-template.update',
        'api.inspection-verifier-template.delete',
        'api.letter-of-award-clause-comments.create',
        'api.letter-of-award-clause-comments.update',
        'api.letter-of-award-clause-comments.delete',
        'api.inspections.create',
        'api.inspections.update',
        'api.inspections.delete',
        'api.interim-claim-informations.create',
        'api.interim-claim-informations.update',
        'api.interim-claim-informations.delete',
        'api.letter-of-award-clauses.create',
        'api.letter-of-award-clauses.update',
        'api.letter-of-award-clauses.delete',
        'api.letter-of-award-contract-details.create',
        'api.letter-of-award-contract-details.update',
        'api.letter-of-award-contract-details.delete',
        'api.letter-of-award-logs.create',
        'api.letter-of-award-logs.update',
        'api.letter-of-award-logs.delete',
        'api.labours.create',
        'api.labours.update',
        'api.labours.delete',
        'api.instructions-to-contractors.create',
        'api.instructions-to-contractors.update',
        'api.instructions-to-contractors.delete',
        'api.letter-of-award-print-settings.create',
        'api.letter-of-award-print-settings.update',
        'api.letter-of-award-print-settings.delete',
        'api.letter-of-award-signatories.create',
        'api.letter-of-award-signatories.update',
        'api.letter-of-award-signatories.delete',
        'api.languages.create',
        'api.languages.update',
        'api.languages.delete',
        'api.licenses.create',
        'api.licenses.update',
        'api.licenses.delete',
        'api.loss-or-and-expenses.create',
        'api.loss-or-and-expenses.update',
        'api.loss-or-and-expenses.delete',
        'api.loe-fourth-level-messages.create',
        'api.loe-fourth-level-messages.update',
        'api.loe-fourth-level-messages.delete',
        'api.loe-second-level-messages.create',
        'api.loe-second-level-messages.update',
        'api.loe-second-level-messages.delete',
        'api.loe-third-level-messages.create',
        'api.loe-third-level-messages.update',
        'api.loe-third-level-messages.delete',
        'api.loss-or-and-expense-claims.create',
        'api.loss-or-and-expense-claims.update',
        'api.loss-or-and-expense-claims.delete',
        'api.login-request-form-settings.create',
        'api.login-request-form-settings.update',
        'api.login-request-form-settings.delete',
        'api.loss-or-and-expense-interim-claims.create',
        'api.loss-or-and-expense-interim-claims.update',
        'api.loss-or-and-expense-interim-claims.delete',
        'api.loe-first-level-messages.create',
        'api.loe-first-level-messages.update',
        'api.loe-first-level-messages.delete',
        'api.letter-of-awards.create',
        'api.letter-of-awards.update',
        'api.letter-of-awards.delete',
        'api.letter-of-award-user-permissions.create',
        'api.letter-of-award-user-permissions.update',
        'api.letter-of-award-user-permissions.delete',
        'api.loe-contractor-confirm-delays.create',
        'api.loe-contractor-confirm-delays.update',
        'api.loe-contractor-confirm-delays.delete',
        'api.machinery.create',
        'api.machinery.update',
        'api.machinery.delete',
        'api.e-bidding-bids.create',
        'api.e-bidding-bids.update',
        'api.e-bidding-bids.delete',
        'api.migrations.create',
        'api.migrations.update',
        'api.migrations.delete',
        'api.my-company-profiles.create',
        'api.my-company-profiles.update',
        'api.my-company-profiles.delete',
        'api.notification-groups.create',
        'api.notification-groups.update',
        'api.notification-groups.delete',
        'api.object-forum-threads.create',
        'api.object-forum-threads.update',
        'api.object-forum-threads.delete',
        'api.object-fields.create',
        'api.object-fields.update',
        'api.object-fields.delete',
        'api.mobile-sync-companies.create',
        'api.mobile-sync-companies.update',
        'api.mobile-sync-companies.delete',
        'api.mobile-sync-defect-categories.create',
        'api.mobile-sync-defect-categories.update',
        'api.mobile-sync-defect-categories.delete',
        'api.mobile-sync-defect-category-trades.create',
        'api.mobile-sync-defect-category-trades.update',
        'api.mobile-sync-defect-category-trades.delete',
        'api.mobile-sync-defects.create',
        'api.mobile-sync-defects.update',
        'api.mobile-sync-defects.delete',
        'api.mobile-sync-project-labour-rate-contractors.create',
        'api.mobile-sync-project-labour-rate-contractors.update',
        'api.mobile-sync-project-labour-rate-contractors.delete',
        'api.mobile-sync-project-labour-rate-trades.create',
        'api.mobile-sync-project-labour-rate-trades.update',
        'api.mobile-sync-project-labour-rate-trades.delete',
        'api.mobile-sync-project-labour-rates.create',
        'api.mobile-sync-project-labour-rates.update',
        'api.mobile-sync-project-labour-rates.delete',
        'api.mobile-sync-project-structure-location-codes.create',
        'api.mobile-sync-project-structure-location-codes.update',
        'api.mobile-sync-project-structure-location-codes.delete',
        'api.mobile-sync-projects.create',
        'api.mobile-sync-projects.update',
        'api.mobile-sync-projects.delete',
        'api.mobile-sync-site-management-defects.create',
        'api.mobile-sync-site-management-defects.update',
        'api.mobile-sync-site-management-defects.delete',
        'api.mobile-sync-trades.create',
        'api.mobile-sync-trades.update',
        'api.mobile-sync-trades.delete',
        'api.mobile-sync-uploads.create',
        'api.mobile-sync-uploads.update',
        'api.mobile-sync-uploads.delete',
        'api.module-permissions.create',
        'api.module-permissions.update',
        'api.module-permissions.delete',
        'api.module-uploaded-files.create',
        'api.module-uploaded-files.update',
        'api.module-uploaded-files.delete',
        'api.notification-categories.create',
        'api.notification-categories.update',
        'api.notification-categories.delete',
        'api.notifications.create',
        'api.notifications.update',
        'api.notifications.delete',
        'api.object-logs.create',
        'api.object-logs.update',
        'api.object-logs.delete',
        'api.open-tender-banners.create',
        'api.open-tender-banners.update',
        'api.open-tender-banners.delete',
        'api.open-tender-award-recommendation-tender-analysis-table-edit-log.create',
        'api.open-tender-award-recommendation-tender-analysis-table-edit-log.update',
        'api.open-tender-award-recommendation-tender-analysis-table-edit-log.delete',
        'api.e-bidding-email-reminder-recipients.create',
        'api.e-bidding-email-reminder-recipients.update',
        'api.e-bidding-email-reminder-recipients.delete',
        'api.open-tender-award-recommendation-tender-summary.create',
        'api.open-tender-award-recommendation-tender-summary.update',
        'api.open-tender-award-recommendation-tender-summary.delete',
        'api.open-tender-page-information.create',
        'api.open-tender-page-information.update',
        'api.open-tender-page-information.delete',
        'api.open-tender-person-in-charges.create',
        'api.open-tender-person-in-charges.update',
        'api.open-tender-person-in-charges.delete',
        'api.open-tender-tender-documents.create',
        'api.open-tender-tender-documents.update',
        'api.open-tender-tender-documents.delete',
        'api.open-tender-award-recommendation-bill-details.create',
        'api.open-tender-award-recommendation-bill-details.update',
        'api.open-tender-award-recommendation-bill-details.delete',
        'api.object-tags.create',
        'api.object-tags.update',
        'api.object-tags.delete',
        'api.open-tender-announcements.create',
        'api.open-tender-announcements.update',
        'api.open-tender-announcements.delete',
        'api.open-tender-industry-codes.create',
        'api.open-tender-industry-codes.update',
        'api.open-tender-industry-codes.delete',
        'api.open-tender-news.create',
        'api.open-tender-news.update',
        'api.open-tender-news.delete',
        'api.open-tender-award-recommendation-files.create',
        'api.open-tender-award-recommendation-files.update',
        'api.open-tender-award-recommendation-files.delete',
        'api.open-tender-award-recommendation.create',
        'api.open-tender-award-recommendation.update',
        'api.open-tender-award-recommendation.delete',
        'api.open-tender-verifier-logs.create',
        'api.open-tender-verifier-logs.update',
        'api.open-tender-verifier-logs.delete',
        'api.order-item-project-tenders.create',
        'api.order-item-project-tenders.update',
        'api.order-item-project-tenders.delete',
        'api.order-item-vendor-reg-payments.create',
        'api.order-item-vendor-reg-payments.update',
        'api.order-item-vendor-reg-payments.delete',
        'api.order-items.create',
        'api.order-items.update',
        'api.order-items.delete',
        'api.order-payments.create',
        'api.order-payments.update',
        'api.order-payments.delete',
        'api.order-subs.create',
        'api.order-subs.update',
        'api.order-subs.delete',
        'api.orders.create',
        'api.orders.update',
        'api.orders.delete',
        'api.password-reminders.create',
        'api.password-reminders.update',
        'api.password-reminders.delete',
        'api.payment-gateway-results.create',
        'api.payment-gateway-results.update',
        'api.payment-gateway-results.delete',
        'api.payment-gateway-settings.create',
        'api.payment-gateway-settings.update',
        'api.payment-gateway-settings.delete',
        'api.processor-delete-company-logs.create',
        'api.processor-delete-company-logs.update',
        'api.processor-delete-company-logs.delete',
        'api.procurement-methods.create',
        'api.procurement-methods.update',
        'api.procurement-methods.delete',
        'api.open-tender-tender-requirements.create',
        'api.open-tender-tender-requirements.update',
        'api.open-tender-tender-requirements.delete',
        'api.pam-2006-project-details.create',
        'api.pam-2006-project-details.update',
        'api.pam-2006-project-details.delete',
        'api.payment-settings.create',
        'api.payment-settings.update',
        'api.payment-settings.delete',
        'api.project-module-permissions.create',
        'api.project-module-permissions.update',
        'api.project-module-permissions.delete',
        'api.project-report-chart-plots.create',
        'api.project-report-chart-plots.update',
        'api.project-report-chart-plots.delete',
        'api.project-report-charts.create',
        'api.project-report-charts.update',
        'api.project-report-charts.delete',
        'api.project-report-type-mappings.create',
        'api.project-report-type-mappings.update',
        'api.project-report-type-mappings.delete',
        'api.project-report-notification-contents.create',
        'api.project-report-notification-contents.update',
        'api.project-report-notification-contents.delete',
        'api.project-report-notification-periods.create',
        'api.project-report-notification-periods.update',
        'api.project-report-notification-periods.delete',
        'api.project-report-notification-recipients.create',
        'api.project-report-notification-recipients.update',
        'api.project-report-notification-recipients.delete',
        'api.project-report-notifications.create',
        'api.project-report-notifications.update',
        'api.project-report-notifications.delete',
        'api.project-report-columns.create',
        'api.project-report-columns.update',
        'api.project-report-columns.delete',
        'api.project-labour-rates.create',
        'api.project-labour-rates.update',
        'api.project-labour-rates.delete',
        'api.project-contract-group-tender-document-permissions.create',
        'api.project-contract-group-tender-document-permissions.update',
        'api.project-contract-group-tender-document-permissions.delete',
        'api.project-contract-management-modules.create',
        'api.project-contract-management-modules.update',
        'api.project-contract-management-modules.delete',
        'api.project-document-files.create',
        'api.project-document-files.update',
        'api.project-document-files.delete',
        'api.project-report-action-logs.create',
        'api.project-report-action-logs.update',
        'api.project-report-action-logs.delete',
        'api.project-reports.create',
        'api.project-reports.update',
        'api.project-reports.delete',
        'api.project-report-types.create',
        'api.project-report-types.update',
        'api.project-report-types.delete',
        'api.project-report-user-permissions.create',
        'api.project-report-user-permissions.update',
        'api.project-report-user-permissions.delete',
        'api.project-roles.create',
        'api.project-roles.update',
        'api.project-roles.delete',
        'api.request-for-information-messages.create',
        'api.request-for-information-messages.update',
        'api.request-for-information-messages.delete',
        'api.project-track-record-settings.create',
        'api.project-track-record-settings.update',
        'api.project-track-record-settings.delete',
        'api.property-developers.create',
        'api.property-developers.update',
        'api.property-developers.delete',
        'api.purged-vendors.create',
        'api.purged-vendors.update',
        'api.purged-vendors.delete',
        'api.request-for-inspection-replies.create',
        'api.request-for-inspection-replies.update',
        'api.request-for-inspection-replies.delete',
        'api.request-for-variation-category-kpi-limit-update-logs.create',
        'api.request-for-variation-category-kpi-limit-update-logs.update',
        'api.request-for-variation-category-kpi-limit-update-logs.delete',
        'api.request-for-variation-contract-and-contingency-sum.create',
        'api.request-for-variation-contract-and-contingency-sum.update',
        'api.request-for-variation-contract-and-contingency-sum.delete',
        'api.request-for-variation-categories.create',
        'api.request-for-variation-categories.update',
        'api.request-for-variation-categories.delete',
        'api.request-for-inspections.create',
        'api.request-for-inspections.update',
        'api.request-for-inspections.delete',
        'api.project-sectional-completion-dates.create',
        'api.project-sectional-completion-dates.update',
        'api.project-sectional-completion-dates.delete',
        'api.request-for-inspection-inspections.create',
        'api.request-for-inspection-inspections.update',
        'api.request-for-inspection-inspections.delete',
        'api.request-for-variation-action-logs.create',
        'api.request-for-variation-action-logs.update',
        'api.request-for-variation-action-logs.delete',
        'api.request-for-variation-files.create',
        'api.request-for-variation-files.update',
        'api.request-for-variation-files.delete',
        'api.rejected-materials.create',
        'api.rejected-materials.update',
        'api.rejected-materials.delete',
        'api.request-for-variation-user-permission-groups.create',
        'api.request-for-variation-user-permission-groups.update',
        'api.request-for-variation-user-permission-groups.delete',
        'api.request-for-variations.create',
        'api.request-for-variations.update',
        'api.request-for-variations.delete',
        'api.scheduled-maintenance.create',
        'api.scheduled-maintenance.update',
        'api.scheduled-maintenance.delete',
        'api.sent-tender-reminders-log.create',
        'api.sent-tender-reminders-log.update',
        'api.sent-tender-reminders-log.delete',
        'api.site-management-mcar.create',
        'api.site-management-mcar.update',
        'api.site-management-mcar.delete',
        'api.requests-for-inspection.create',
        'api.requests-for-inspection.update',
        'api.requests-for-inspection.delete',
        'api.site-management-mcar-form-responses.create',
        'api.site-management-mcar-form-responses.update',
        'api.site-management-mcar-form-responses.delete',
        'api.site-management-site-diary-general-form-responses.create',
        'api.site-management-site-diary-general-form-responses.update',
        'api.site-management-site-diary-general-form-responses.delete',
        'api.site-management-site-diary-labours.create',
        'api.site-management-site-diary-labours.update',
        'api.site-management-site-diary-labours.delete',
        'api.request-for-variation-user-permissions.create',
        'api.request-for-variation-user-permissions.update',
        'api.request-for-variation-user-permissions.delete',
        'api.risk-register-messages.create',
        'api.risk-register-messages.update',
        'api.risk-register-messages.delete',
        'api.site-management-defect-backcharge-details.create',
        'api.site-management-defect-backcharge-details.update',
        'api.site-management-defect-backcharge-details.delete',
        'api.site-management-defect-form-responses.create',
        'api.site-management-defect-form-responses.update',
        'api.site-management-defect-form-responses.delete',
        'api.site-management-site-diary-weathers.create',
        'api.site-management-site-diary-weathers.update',
        'api.site-management-site-diary-weathers.delete',
        'api.site-management-user-permissions.create',
        'api.site-management-user-permissions.update',
        'api.site-management-user-permissions.delete',
        'api.subsidiaries.create',
        'api.subsidiaries.update',
        'api.subsidiaries.delete',
        'api.subsidiary-apportionment-records.create',
        'api.subsidiary-apportionment-records.update',
        'api.subsidiary-apportionment-records.delete',
        'api.supplier-credit-facilities.create',
        'api.supplier-credit-facilities.update',
        'api.supplier-credit-facilities.delete',
        'api.site-management-site-diary-machinery.create',
        'api.site-management-site-diary-machinery.update',
        'api.site-management-site-diary-machinery.delete',
        'api.system-module-elements.create',
        'api.system-module-elements.update',
        'api.system-module-elements.delete',
        'api.supplier-credit-facility-settings.create',
        'api.supplier-credit-facility-settings.update',
        'api.supplier-credit-facility-settings.delete',
        'api.system-module-configurations.create',
        'api.system-module-configurations.update',
        'api.system-module-configurations.delete',
        'api.site-management-site-diary-rejected-materials.create',
        'api.site-management-site-diary-rejected-materials.update',
        'api.site-management-site-diary-rejected-materials.delete',
        'api.site-management-site-diary-visitors.create',
        'api.site-management-site-diary-visitors.update',
        'api.site-management-site-diary-visitors.delete',
        'api.structured-documents.create',
        'api.structured-documents.update',
        'api.structured-documents.delete',
        'api.system-settings.create',
        'api.system-settings.update',
        'api.system-settings.delete',
        'api.technical-evaluation-response-log.create',
        'api.technical-evaluation-response-log.update',
        'api.technical-evaluation-response-log.delete',
        'api.technical-evaluations.create',
        'api.technical-evaluations.update',
        'api.technical-evaluations.delete',
        'api.template-tender-document-folders.create',
        'api.template-tender-document-folders.update',
        'api.template-tender-document-folders.delete',
        'api.tender-document-download-logs.create',
        'api.tender-document-download-logs.update',
        'api.tender-document-download-logs.delete',
        'api.tender-calling-tender-information.create',
        'api.tender-calling-tender-information.update',
        'api.tender-calling-tender-information.delete',
        'api.tags.create',
        'api.tags.update',
        'api.tags.delete',
        'api.technical-evaluation-set-references.create',
        'api.technical-evaluation-set-references.update',
        'api.technical-evaluation-set-references.delete',
        'api.technical-evaluation-attachments.create',
        'api.technical-evaluation-attachments.update',
        'api.technical-evaluation-attachments.delete',
        'api.technical-evaluation-items.create',
        'api.technical-evaluation-items.update',
        'api.technical-evaluation-items.delete',
        'api.technical-evaluation-tenderer-options.create',
        'api.technical-evaluation-tenderer-options.update',
        'api.technical-evaluation-tenderer-options.delete',
        'api.technical-evaluation-verifier-logs.create',
        'api.technical-evaluation-verifier-logs.update',
        'api.technical-evaluation-verifier-logs.delete',
        'api.template-tender-document-folder-work-category.create',
        'api.template-tender-document-folder-work-category.update',
        'api.template-tender-document-folder-work-category.delete',
        'api.template-tender-document-files.create',
        'api.template-tender-document-files.update',
        'api.template-tender-document-files.delete',
        'api.template-tender-document-files-roles-readonly.create',
        'api.template-tender-document-files-roles-readonly.update',
        'api.template-tender-document-files-roles-readonly.delete',
        'api.tender-alternatives-position.create',
        'api.tender-alternatives-position.update',
        'api.tender-alternatives-position.delete',
        'api.tender-calling-tender-information-user.create',
        'api.tender-calling-tender-information-user.update',
        'api.tender-calling-tender-information-user.delete',
        'api.tender-interview-information.create',
        'api.tender-interview-information.update',
        'api.tender-interview-information.delete',
        'api.tender-lot-information-user.create',
        'api.tender-lot-information-user.update',
        'api.tender-lot-information-user.delete',
        'api.tender-rot-information.create',
        'api.tender-rot-information.update',
        'api.tender-rot-information.delete',
        'api.tender-rot-information-user.create',
        'api.tender-rot-information-user.update',
        'api.tender-rot-information-user.delete',
        'api.tender-document-files.create',
        'api.tender-document-files.update',
        'api.tender-document-files.delete',
        'api.tender-lot-information.create',
        'api.tender-lot-information.update',
        'api.tender-lot-information.delete',
        'api.tender-document-files-roles-readonly.create',
        'api.tender-document-files-roles-readonly.update',
        'api.tender-document-files-roles-readonly.delete',
        'api.tender-document-folders.create',
        'api.tender-document-folders.update',
        'api.tender-document-folders.delete',
        'api.tender-form-verifier-logs.create',
        'api.tender-form-verifier-logs.update',
        'api.tender-form-verifier-logs.delete',
        'api.tender-interviews.create',
        'api.tender-interviews.update',
        'api.tender-interviews.delete',
        'api.tender-reminders.create',
        'api.tender-reminders.update',
        'api.tender-reminders.delete',
        'api.tender-user-technical-evaluation-verifier.create',
        'api.tender-user-technical-evaluation-verifier.update',
        'api.tender-user-technical-evaluation-verifier.delete',
        'api.tender-user-verifier-open-tender.create',
        'api.tender-user-verifier-open-tender.update',
        'api.tender-user-verifier-open-tender.delete',
        'api.tender-user-verifier-retender.create',
        'api.tender-user-verifier-retender.update',
        'api.tender-user-verifier-retender.delete',
        'api.tenderer-technical-evaluation-information.create',
        'api.tenderer-technical-evaluation-information.update',
        'api.tenderer-technical-evaluation-information.delete',
        'api.tenderer-technical-evaluation-information-log.create',
        'api.tenderer-technical-evaluation-information-log.update',
        'api.tenderer-technical-evaluation-information-log.delete',
        'api.theme-settings.create',
        'api.theme-settings.update',
        'api.theme-settings.delete',
        'api.users.create',
        'api.users.update',
        'api.users.delete',
        'api.user-company-log.create',
        'api.user-company-log.update',
        'api.user-company-log.delete',
        'api.track-record-projects.create',
        'api.track-record-projects.update',
        'api.track-record-projects.delete',
        'api.vendor-categories.create',
        'api.vendor-categories.update',
        'api.vendor-categories.delete',
        'api.vendor-detail-settings.create',
        'api.vendor-detail-settings.update',
        'api.vendor-detail-settings.delete',
        'api.tenders.create',
        'api.tenders.update',
        'api.tenders.delete',
        'api.uploads.create',
        'api.uploads.update',
        'api.uploads.delete',
        'api.user-logins.create',
        'api.user-logins.update',
        'api.user-logins.delete',
        'api.user-settings.create',
        'api.user-settings.update',
        'api.user-settings.delete',
        'api.users-company-verification-privileges.create',
        'api.users-company-verification-privileges.update',
        'api.users-company-verification-privileges.delete',
        'api.vendor-category-temporary-records.create',
        'api.vendor-category-temporary-records.update',
        'api.vendor-category-temporary-records.delete',
        'api.vendor-category-vendor-work-category.create',
        'api.vendor-category-vendor-work-category.update',
        'api.vendor-category-vendor-work-category.delete',
        'api.vendor-evaluation-cycle-scores.create',
        'api.vendor-evaluation-cycle-scores.update',
        'api.vendor-evaluation-cycle-scores.delete',
        'api.vendor-evaluation-scores.create',
        'api.vendor-evaluation-scores.update',
        'api.vendor-evaluation-scores.delete',
        'api.vendor-management-instruction-settings.create',
        'api.vendor-management-instruction-settings.update',
        'api.vendor-management-instruction-settings.delete',
        'api.vendor-performance-evaluation-form-change-logs.create',
        'api.vendor-performance-evaluation-form-change-logs.update',
        'api.vendor-performance-evaluation-form-change-logs.delete',
        'api.vendor-management-grade-levels.create',
        'api.vendor-management-grade-levels.update',
        'api.vendor-management-grade-levels.delete',
        'api.vendor-performance-evaluation-module-parameters.create',
        'api.vendor-performance-evaluation-module-parameters.update',
        'api.vendor-performance-evaluation-module-parameters.delete',
        'api.vendor-performance-evaluation-project-removal-reasons.create',
        'api.vendor-performance-evaluation-project-removal-reasons.update',
        'api.vendor-performance-evaluation-project-removal-reasons.delete',
        'api.vendor-performance-evaluation-submission-reminder-settings.create',
        'api.vendor-performance-evaluation-submission-reminder-settings.update',
        'api.vendor-performance-evaluation-submission-reminder-settings.delete',
        'api.vendor-management-grades.create',
        'api.vendor-management-grades.update',
        'api.vendor-management-grades.delete',
        'api.vendor-management-user-permissions.create',
        'api.vendor-management-user-permissions.update',
        'api.vendor-management-user-permissions.delete',
        'api.vendor-performance-evaluation-company-form-evaluation-logs.create',
        'api.vendor-performance-evaluation-company-form-evaluation-logs.update',
        'api.vendor-performance-evaluation-company-form-evaluation-logs.delete',
        'api.vendor-performance-evaluation-company-forms.create',
        'api.vendor-performance-evaluation-company-forms.update',
        'api.vendor-performance-evaluation-company-forms.delete',
        'api.vendor-performance-evaluation-form-change-requests.create',
        'api.vendor-performance-evaluation-form-change-requests.update',
        'api.vendor-performance-evaluation-form-change-requests.delete',
        'api.vendor-performance-evaluation-processor-edit-details.create',
        'api.vendor-performance-evaluation-processor-edit-details.update',
        'api.vendor-performance-evaluation-processor-edit-details.delete',
        'api.vendor-performance-evaluation-processor-edit-logs.create',
        'api.vendor-performance-evaluation-processor-edit-logs.update',
        'api.vendor-performance-evaluation-processor-edit-logs.delete',
        'api.vendor-performance-evaluation-removal-requests.create',
        'api.vendor-performance-evaluation-removal-requests.update',
        'api.vendor-performance-evaluation-removal-requests.delete',
        'api.vendor-performance-evaluation-setups.create',
        'api.vendor-performance-evaluation-setups.update',
        'api.vendor-performance-evaluation-setups.delete',
        'api.vendor-performance-evaluation-template-forms.create',
        'api.vendor-performance-evaluation-template-forms.update',
        'api.vendor-performance-evaluation-template-forms.delete',
        'api.vendor-profile-module-parameters.create',
        'api.vendor-profile-module-parameters.update',
        'api.vendor-profile-module-parameters.delete',
        'api.vendor-profiles.create',
        'api.vendor-profiles.update',
        'api.vendor-profiles.delete',
        'api.vendor-registration-and-prequalification-module-parameters.create',
        'api.vendor-registration-and-prequalification-module-parameters.update',
        'api.vendor-registration-and-prequalification-module-parameters.delete',
        'api.vendor-registration-form-template-mappings.create',
        'api.vendor-registration-form-template-mappings.update',
        'api.vendor-registration-form-template-mappings.delete',
        'api.vendor-registration-sections.create',
        'api.vendor-registration-sections.update',
        'api.vendor-registration-sections.delete',
        'api.vendor-registration-submission-logs.create',
        'api.vendor-registration-submission-logs.update',
        'api.vendor-registration-submission-logs.delete',
        'api.vendor-performance-evaluations.create',
        'api.vendor-performance-evaluations.update',
        'api.vendor-performance-evaluations.delete',
        'api.vendor-performance-evaluators.create',
        'api.vendor-performance-evaluators.update',
        'api.vendor-performance-evaluators.delete',
        'api.vendor-pre-qualification-setups.create',
        'api.vendor-pre-qualification-setups.update',
        'api.vendor-pre-qualification-setups.delete',
        'api.vendor-pre-qualification-template-forms.create',
        'api.vendor-pre-qualification-template-forms.update',
        'api.vendor-pre-qualification-template-forms.delete',
        'api.vendor-pre-qualification-vendor-group-grades.create',
        'api.vendor-pre-qualification-vendor-group-grades.update',
        'api.vendor-pre-qualification-vendor-group-grades.delete',
        'api.vendor-pre-qualifications.create',
        'api.vendor-pre-qualifications.update',
        'api.vendor-pre-qualifications.delete',
        'api.vendor-profile-remarks.create',
        'api.vendor-profile-remarks.update',
        'api.vendor-profile-remarks.delete',
        'api.vendor-registration-payments.create',
        'api.vendor-registration-payments.update',
        'api.vendor-registration-payments.delete',
        'api.vendor-registration-processors.create',
        'api.vendor-registration-processors.update',
        'api.vendor-registration-processors.delete',
        'api.weather-record-reports.create',
        'api.weather-record-reports.update',
        'api.weather-record-reports.delete',
        'api.vendor-type-change-logs.create',
        'api.vendor-type-change-logs.update',
        'api.vendor-type-change-logs.delete',
        'api.weighted-node-scores.create',
        'api.weighted-node-scores.update',
        'api.weighted-node-scores.delete',
        'api.work-categories.create',
        'api.work-categories.update',
        'api.work-categories.delete',
        'api.work-subcategories.create',
        'api.work-subcategories.update',
        'api.work-subcategories.delete',
        'api.weathers.create',
        'api.weathers.update',
        'api.weathers.delete',
        'api.vendor-work-categories.create',
        'api.vendor-work-categories.update',
        'api.vendor-work-categories.delete',
        'api.vendor-work-subcategories.create',
        'api.vendor-work-subcategories.update',
        'api.vendor-work-subcategories.delete',
        'api.weighted-nodes.create',
        'api.weighted-nodes.update',
        'api.weighted-nodes.delete',
        'api.vendors.create',
        'api.vendors.update',
        'api.vendors.delete',
        'api.vendor-work-category-work-category.create',
        'api.vendor-work-category-work-category.update',
        'api.vendor-work-category-work-category.delete',
        'api.verifiers.create',
        'api.verifiers.update',
        'api.verifiers.delete',
        'api.weather-records.create',
        'api.weather-records.update',
        'api.weather-records.delete',
        'api.access-log.create',
        'api.access-log.update',
        'api.access-log.delete',
        'api.projects.create',
        'api.projects.update',
        'api.projects.delete',
        'api.accounting-report-export-log-details.create',
        'api.accounting-report-export-log-details.update',
        'api.accounting-report-export-log-details.delete',
        'api.interim-claims.create',
        'api.interim-claims.update',
        'api.interim-claims.delete',
        'api.architect-instructions.create',
        'api.architect-instructions.update',
        'api.architect-instructions.delete',
        'api.ae-second-level-messages.create',
        'api.ae-second-level-messages.update',
        'api.ae-second-level-messages.delete',
        'api.companies.create',
        'api.companies.update',
        'api.companies.delete',
        'api.countries.create',
        'api.countries.update',
        'api.countries.delete',
        'api.states.create',
        'api.states.update',
        'api.states.delete',
        'api.clause-items.create',
        'api.clause-items.update',
        'api.clause-items.delete',
        'api.consultant-management-attachment-settings.create',
        'api.consultant-management-attachment-settings.update',
        'api.consultant-management-attachment-settings.delete',
        'api.consultant-management-open-rfp.create',
        'api.consultant-management-open-rfp.update',
        'api.consultant-management-open-rfp.delete',
        'api.consultant-management-approval-document-section-a.create',
        'api.consultant-management-approval-document-section-a.update',
        'api.consultant-management-approval-document-section-a.delete',
        'api.vendor-registrations.create',
        'api.vendor-registrations.update',
        'api.vendor-registrations.delete',
        'api.company-personnel.create',
        'api.company-personnel.update',
        'api.company-personnel.delete',
        'api.consultant-management-company-roles.create',
        'api.consultant-management-company-roles.update',
        'api.consultant-management-company-roles.delete',
        'api.product-types.create',
        'api.product-types.update',
        'api.product-types.delete',
        'api.development-types.create',
        'api.development-types.update',
        'api.development-types.delete',
        'api.contractor-work-category.create',
        'api.contractor-work-category.update',
        'api.contractor-work-category.delete',
        'api.previous-cpe-grades.create',
        'api.previous-cpe-grades.update',
        'api.previous-cpe-grades.delete',
        'api.daily-labour-report-labour-rates.create',
        'api.daily-labour-report-labour-rates.update',
        'api.daily-labour-report-labour-rates.delete',
        'api.email-notification-recipients.create',
        'api.email-notification-recipients.update',
        'api.email-notification-recipients.delete',
        'api.external-application-client-modules.create',
        'api.external-application-client-modules.update',
        'api.external-application-client-modules.delete',
        'api.form-column-sections.create',
        'api.form-column-sections.update',
        'api.form-column-sections.delete',
        'api.forum-thread-user-settings.create',
        'api.forum-thread-user-settings.update',
        'api.forum-thread-user-settings.delete',
        'api.indonesia-civil-contract-early-warnings.create',
        'api.indonesia-civil-contract-early-warnings.update',
        'api.indonesia-civil-contract-early-warnings.delete',
        'api.indonesia-civil-contract-ew-eot.create',
        'api.indonesia-civil-contract-ew-eot.update',
        'api.indonesia-civil-contract-ew-eot.delete',
        'api.letter-of-award-clause-comment-read-logs.create',
        'api.letter-of-award-clause-comment-read-logs.update',
        'api.letter-of-award-clause-comment-read-logs.delete',
        'api.menus.create',
        'api.menus.update',
        'api.menus.delete',
        'api.site-management-defects.create',
        'api.site-management-defects.update',
        'api.site-management-defects.delete',
        'api.module-permission-subsidiaries.create',
        'api.module-permission-subsidiaries.update',
        'api.module-permission-subsidiaries.delete',
        'api.notifications-categories-in-groups.create',
        'api.notifications-categories-in-groups.update',
        'api.notifications-categories-in-groups.delete',
        'api.object-permissions.create',
        'api.object-permissions.update',
        'api.object-permissions.delete',
        'api.open-tender-award-recommendation-report-edit-logs.create',
        'api.open-tender-award-recommendation-report-edit-logs.update',
        'api.open-tender-award-recommendation-report-edit-logs.delete',
        'api.structured-document-clauses.create',
        'api.structured-document-clauses.update',
        'api.structured-document-clauses.delete',
        'api.technical-evaluation-attachment-list-items.create',
        'api.technical-evaluation-attachment-list-items.update',
        'api.technical-evaluation-attachment-list-items.delete',
        'api.tender-interview-logs.create',
        'api.tender-interview-logs.update',
        'api.tender-interview-logs.delete',
        'api.track-record-project-vendor-work-subcategories.create',
        'api.track-record-project-vendor-work-subcategories.update',
        'api.track-record-project-vendor-work-subcategories.delete',
        'api.vendor-performance-evaluation-cycles.create',
        'api.vendor-performance-evaluation-cycles.update',
        'api.vendor-performance-evaluation-cycles.delete',
        'api.vendor-registration-processor-remarks.create',
        'api.vendor-registration-processor-remarks.update',
        'api.vendor-registration-processor-remarks.delete',
    );

    $currentRouteName = $route->getName();

    // temporarily will allow buildspace api to pass
    // will use unique key shared between to identify
    if( in_array($currentRouteName, $byPassRoutes) )
    {
        return false;
    }

    if( ! StringUtils::equals(Session::token(), Input::get('_token')) )
    {
        throw new Illuminate\Session\TokenMismatchException;
    }
});



