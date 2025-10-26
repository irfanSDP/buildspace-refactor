<?php namespace PCK\Providers;

use Illuminate\Support\ServiceProvider;
use PCK\Buildspace\ClaimCertificateInformation;
use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\ContractManagementVerifier;
use PCK\Buildspace\MasterCostData;
use PCK\ContractGroups\Types\Role;
use PCK\Contracts\Contract;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationRepository;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarningRepository;
use PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpenseRepository;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;
use PCK\EngineerInstructions\EngineerInstructionRepository;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\RequestForInspection\RequestForInspection;
use PCK\WeatherRecords\WeatherRecordRepository;
use PCK\InterimClaims\InterimClaimRepository;
use PCK\SiteManagement\SiteManagementDefectRepository;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryRepository;
use PCK\InstructionsToContractors\InstructionsToContractor;
use PCK\DailyReport\DailyReport;
use PCK\DailyLabourReports\DailyLabourReportRepository;
use PCK\RequestForVariation\RequestForVariationRepository;
use PCK\RequestForVariation\RequestForVariationUserPermissionRepository;
use PCK\LetterOfAward\LetterOfAwardUserPermissionRepository;
use PCK\ModulePermission\ModulePermission;
use PCK\Vendor\Vendor;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\RemovalRequest;

class ViewServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->queryProjectMenuByContract();

        $this->generateLayoutParameters();
    }

    private function queryProjectMenuByContract()
    {
        $app = $this->app;

        $this->app['view']->composer('dashboard.partials.header', function($view) use ($app)
        {
            $user = \Confide::user();
            
            $view->with('user', $user);
        });

        $this->app['view']->composer('dashboard.partials.leftNavigation', function($view) use ($app)
        {
            $user    = \Confide::user();
            $project = $app['router']->input('projectId');

            $view->with('user', $user);
            $view->with('currentProjectId', $project->id);
            $view->with('menus', $project->contract->menus);
        });
    }

    private function generateLayoutParameters()
    {
        $app = $this->app;

        $this->app['view']->composer('*', function($view)
        {
            $currentUser = \Confide::user();

            $view->with('currentUser', $currentUser);
        });

        $this->app['view']->composer('layout.main', function($view)
        {
            $isLicenseValid = \App::make('PCK\Licenses\LicenseRepository')->checkLicenseValidity();
            $licensingDisabled = (getenv('disable_licensing') === '1');

            $view->with('isLicenseValid', $isLicenseValid)->with('licensingDisabled', $licensingDisabled);
        });

        $this->app['view']->composer('layout.partials.header', function($view) use ($app)
        {
            $user    = \Confide::user();
            $project = $app['router']->input('projectId');
            
            $unreadNotificationsCount = \Notifynder::countNotRead($user->id)->notread;
            $latest10Notifications    = \Notifynder::getAll($user->id, 10)->toArray();
            
            $view->with('user', $user);
            $view->with('unreadNotificationsCount', $unreadNotificationsCount);
            $view->with('latest10Notifications', $latest10Notifications);
            $view->with('project', $project ?: null);
        });

        $this->app['view']->composer('layout.partials.navigation', function($view) use ($app)
        {
            $user                      = \Confide::user();
            $project                   = $app['router']->input('projectId');
            $companyCount              = Company::where('confirmed', '=', true)->count();
            $unconfirmedCompanyCount   = Company::where('confirmed', '=', false)->count();
            $documentManagementFolders = null;
            $node                      = ( $app['router']->input('folderId') ) ? DocumentManagementFolder::find($app['router']->input('folderId')) : false;

            $postContractMenu = ( $project && $project->onPostContractStages() && ( ! $user->isSuperAdmin() ) ) ? $project->contract->menus : null;

            $masterCostDataCount = MasterCostData::all()->count();
            $costDataCount       = $user->getVisibleCostData()->count();

            if( $project )
            {
                $countDefectListing            = SiteManagementDefectRepository::processQuery($user, $project)->count();
                $countSiteDiaryListing         = SiteManagementSiteDiaryRepository::processQuery($user, $project)->count();
                $countInstructionsToContractorListing = InstructionsToContractor::processQuery($user, $project)->count();
                $countDailyReportListing = DailyReport::processQuery($user, $project)->count();
                $countDailyLabourReportListing = DailyLabourReportRepository::processQuery($user, $project)->count();
                $documentManagementFolders     = DocumentManagementFolder::getRootsByProject($project);

                if( $project->onPostContractStages() )
                {
                    $contractualClaimCount = [];

                    if( $project->contractIs(Contract::TYPE_PAM2006) )
                    {
                        $contractualClaimCount['architectInstruction'] = ArchitectInstructionRepository::getAICount($project);
                        $contractualClaimCount['engineerInstruction']  = EngineerInstructionRepository::getEICount($project);
                        $contractualClaimCount['extensionOfTime']      = ExtensionOfTimeRepository::getEOTCount($project);
                        $contractualClaimCount['lossOrAndExpenses']    = LossOrAndExpenseRepository::getLOACount($project);
                        $contractualClaimCount['additionalExpenses']   = AdditionalExpenseRepository::getAECount($project);
                        $contractualClaimCount['weatherRecord']        = WeatherRecordRepository::getWRCount($project);
                        $contractualClaimCount['interimClaim']         = InterimClaimRepository::getICCount($project);
                    }
                    elseif( $project->contractIs(Contract::TYPE_INDONESIA_CIVIL_CONTRACT) )
                    {
                        $contractualClaimCount['architectInstruction'] = \PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstructionRepository::getCount($project);
                        $contractualClaimCount['extensionOfTime']      = \PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTimeRepository::getCount($project);
                        $contractualClaimCount['lossOrAndExpenses']    = LossAndExpenseRepository::getCount($project);
                        $contractualClaimCount['earlyWarning']         = EarlyWarningRepository::getCount($project);
                    }
                }

                if( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) && ( ! $project->contractor_contractual_claim_access_enabled ) ) $postContractMenu = null;

                $documentControl['inspectionRequestCount'] = RequestForInspection::getPublicCount($project);

                $pendingContractManagementReviews = ContractManagementClaimVerifier::getPendingRecordsByModule($user, true, $project) + ContractManagementVerifier::getPendingRecordsByModule($user, true, $project);

                $unreadThreads = 0;
                foreach($project->threads()->where('type', '!=', \PCK\Forum\Thread::TYPE_SECRET)->get() as $thread)
                {
                    if( $thread->isViewable($user) && ( $thread->getUnreadPostCount($user, true) > 0 ) ) $unreadThreads++;
                }
            }

            if( \PCK\ModulePermission\ModulePermission::hasPermission($user, \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE) )
            {
                $query = \PCK\Buildspace\ClaimCertificate::getClaimCertificateQuery(false);

                if(!$user->isSuperAdmin())
                {
                    $visibleProjectIds = [];

                    $claimCertificatePaymentRepository = \App::make('\PCK\ClaimCertificate\ClaimCertificatePaymentRepository');

                    $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

                    foreach($visibleSubsidiaryIds as $id)
                    {
                        $visibleProjectIds = array_merge($visibleProjectIds, $claimCertificatePaymentRepository->getListOfProjectIds($id));
                    }

                    $query->whereIn('bs_project_main_information.eproject_origin_id', $visibleProjectIds);
                }

                $claimCertCountWithPaymentsPending = $query->count();
            }
            else
            {
                $claimCertCountWithPaymentsPending = \PCK\Buildspace\ClaimCertificate::getContractorClaimCertificateQuery($user, Project::STATUS_TYPE_POST_CONTRACT)->count();
            }

            $approvedRfvCount = RequestForVariationRepository::getApprovedRfvCount($project);
            $isAdminUserOfProjectOwnerOrGCD = RequestForVariationUserPermissionRepository::isAdminUserOfProjectOwnerOrGCD($project);
            $isProjectOwnnerOrConsultant = RequestForVariationUserPermissionRepository::isProjectOwnnerOrConsultant($project);
            $isUserAssignedToRFV = RequestForVariationUserPermissionRepository::getIsUserAssignedToRfvByProject($project, \Confide::user());
            $isUserAssignedToLetterOfAward =  LetterOfAwardUserPermissionRepository::getIsUserAssignedToLetterOfAwardByProject($project, \Confide::user());
            $isAdminUserOfProjectOwnerOrGCD_LA = LetterOfAwardUserPermissionRepository::isAdminUserOfProjectOwnerOrGCD($project);

            $totalActiveVendors = Vendor::getTotalActiveVendorGroupByCompany();
            $totalDeactivedVendors = Vendor::getTotalDeactivedVendor();
            $totalWatchListVendors = Vendor::getTotalWatchListVendor();
            $totalNomineesForWatchListVendors = Vendor::getTotalNomineesForWatchListVendors();
            $totalUnsuccessfullyRegisteredVendors = Vendor::getTotalUnsuccessfullyRegisteredVendors();

            $pendingVendorRegistrations = VendorRegistration::getPendingVendorRegistrationsCount();
            $pendingVpeCompanyForms     = VendorPerformanceEvaluationCompanyForm::getPendingVpeCompanyFormsCount();
            $pendingEvaluations         = VendorPerformanceEvaluation::getPendingEvaluationsCount();
            $pendingVpeRemovalRequests  = RemovalRequest::getPendingRemovalRequestsCount();

            $pendingDsEvaluationForms = DsEvaluationRepository::getPendingFormsCount();

            $consultantManagementContract   = $app['router']->input('consultantManagementContractId');
            $consultantManagementSubsidiary = $app['router']->input('consultantManagementSubsidiaryId');
            $consultantManagementVendorCategoryRfp = $app['router']->input('vendorCategoryRfpId');
            $consultantManagementCallingRfp = $app['router']->input('consultantManagementRfpId');
            
            $consultantManagementContract   = (!$consultantManagementContract && $consultantManagementSubsidiary) ? $consultantManagementSubsidiary->consultantManagementContract : $consultantManagementContract;
            
            if($consultantManagementVendorCategoryRfp)
            {
                $consultantManagementContract = $consultantManagementVendorCategoryRfp->consultantManagementContract;
            }

            $buCompany = $project ? $project->getCompanyByGroup(Role::PROJECT_OWNER) : null;

            $isVendorMigrationModeEnabled = getenv('VENDOR_MANAGEMENT_MIGRATION_MODE');
            
            $view->with('user', $user);
            $view->with('project', $project ?: null);
            $view->with('currentProjectId', ( $project ) ? $project->id : null);
            $view->with('documentManagementFolders', $documentManagementFolders ?: null);
            $view->with('postContractMenu', $postContractMenu);
            $view->with('designMenu', ( $project && $project->status_id == Project::STATUS_TYPE_DESIGN ) ? true : null);
            $view->with('node', $node);
            $view->with('companyCount', $companyCount);
            $view->with('unconfirmedCompanyCount', $unconfirmedCompanyCount);
            $view->with('contractualClaimCount', $contractualClaimCount ?? null);
            $view->with('documentControl', $documentControl ?? null);
            $view->with('countDefectListing', $countDefectListing ?? null);
            $view->with('countSiteDiaryListing', $countSiteDiaryListing ?? null);
            $view->with('countInstructionsToContractorListing', $countInstructionsToContractorListing ?? null);
            $view->with('countDailyReportListing', $countDailyReportListing ?? null);
            $view->with('countDailyLabourReportListing', $countDailyLabourReportListing ?? null);
            $view->with('pendingContractManagementReviews', $pendingContractManagementReviews ?? []);
            $view->with('claimCertCountWithPaymentsPending', $claimCertCountWithPaymentsPending ?? 0);
            $view->with('unreadThreads', $unreadThreads ?? 0);
            $view->with('approvedRfvCount', $approvedRfvCount ?? 0);
            $view->with('isAdminUserOfProjectOwnerOrGCD', $isAdminUserOfProjectOwnerOrGCD);
            $view->with('isProjectOwnnerOrConsultant', $isProjectOwnnerOrConsultant);
            $view->with('isUserAssignedToRFV', $isUserAssignedToRFV);
            $view->with('masterCostDataCount', $masterCostDataCount);
            $view->with('costDataCount', $costDataCount);
            $view->with('isAdminUserOfProjectOwnerOrGCD_LA', $isAdminUserOfProjectOwnerOrGCD_LA);
            $view->with('isUserAssignedToLetterOfAward', $isUserAssignedToLetterOfAward);
            $view->with('totalActiveVendors', $totalActiveVendors);
            $view->with('totalDeactivedVendors', $totalDeactivedVendors);
            $view->with('totalWatchListVendors', $totalWatchListVendors);
            $view->with('totalNomineesForWatchListVendors', $totalNomineesForWatchListVendors);
            $view->with('totalUnsuccessfullyRegisteredVendors', $totalUnsuccessfullyRegisteredVendors);
            $view->with('consultantManagementContract', $consultantManagementContract ?: null);
            $view->with('consultantManagementVendorCategoryRfp', $consultantManagementVendorCategoryRfp ?: null);
            $view->with('consultantManagementCallingRfp', $consultantManagementCallingRfp ?: null);
            $view->with('pendingVendorRegistrations', $pendingVendorRegistrations);
            $view->with('pendingVpeCompanyForms', $pendingVpeCompanyForms);
            $view->with('pendingEvaluations', $pendingEvaluations);
            $view->with('pendingVpeRemovalRequests', $pendingVpeRemovalRequests);
            $view->with('pendingDsEvaluationForms', $pendingDsEvaluationForms);
            $view->with('buCompany', $buCompany);
            $view->with('isVendorMigrationModeEnabled', $isVendorMigrationModeEnabled);
        });

        $this->app['view']->composer('layout.partials.shortcut', function($view) use ($app)
        {
            $user         = \Confide::user();
            $companyCount = Company::where('confirmed', '=', true)->count();

            $view->with('user', $user);
            $view->with('companyCount', $companyCount);
        });
    }

}
