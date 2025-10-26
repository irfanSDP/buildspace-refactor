<?php

use PCK\CompanyVerification\CompanyVerificationRepository;
use PCK\ContractGroupCategory\ContractGroupCategoryRepository;
use PCK\Licenses\LicenseRepository;
use PCK\Companies\CompanyRepository;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;

class CompanyVerificationController extends \BaseController {

    private $compRepo;
    private $contractGroupCategoryRepository;
    private $companyVerificationRepository;
    private $licenseRepository;

    public function __construct
    (
        CompanyRepository $compRepo,
        ContractGroupCategoryRepository $contractGroupCategoryRepository,
        CompanyVerificationRepository $companyVerificationRepository,
        LicenseRepository $licenseRepository
    )
    {
        $this->compRepo = $compRepo;
        $this->contractGroupCategoryRepository = $contractGroupCategoryRepository;
        $this->companyVerificationRepository = $companyVerificationRepository;
        $this->licenseRepository = $licenseRepository;
    }

    /**
     * Display the list of unconfirmed (unverified) companies.
     *
     * GET /companies
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return View::make('companyVerification.index', array(
            'user'       => Confide::user(),
            'datasource' => route('companies.verification.data'),
        ));
    }

    /**
     * Returns all unconfirmed (unverified) companies.
     *
     * @return string
     */
    public function get()
    {
        $records = $this->compRepo->allInArray(Input::all(), false);

        foreach($records['aaData'] as $i => $record)
        {
            $records["aaData"][ $i ]["createdAt"] = date('d M Y', strtotime($record["createdAt"]));
        }

        return Response::json($records);
    }

    /**
     * Shows the details of the registering company.
     *
     * @param $companyId
     *
     * @return \Illuminate\View\View
     */
    public function show($companyId)
    {
        $company = $this->compRepo->find($companyId);

        $contractGroupCategoriesList = $this->contractGroupCategoryRepository->lists();

        $urlCountry = route('country');
        $urlStates = route('country.states');
        $stateId = $company->state_id;
        $country = $company->country->country;
        $state = $company->state->name;

        $multipleVendorCategories = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        JavaScript::put(compact('urlCountry', 'urlStates', 'stateId'));

        return View::make('companyVerification.show', array(
            'company'                     => $company,
            'contractGroupCategoriesList' => $contractGroupCategoriesList,
            'country'                     => $country,
            'state'                       => $state,
            'multipleVendorCategories'    => $multipleVendorCategories,
        ));
    }

    /**
     * Set the confirm attribute of the company to true and
     * notifies the registered admin user of the company.
     *
     * @param $companyId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmCompany($companyId)
    {
        $companyLimitHasBeenReached = $this->licenseRepository->checkCompanyLimitHasBeenReached();

        if($companyLimitHasBeenReached)
        {
            Flash::error(trans('licenses.companyLimitReached'));

            return Redirect::route('companies.verification.index');       
        }

        $company = $this->compRepo->find($companyId);

        $company->confirmed = true;

        $company->save();

        \Event::fire('user.newlyRegistered', $company->companyAdmin);

        Flash::success(trans('companies.confirmSuccess'));

        return Redirect::route('companies.verification.index');
    }

    /**
     * Deletes the unconfirmed company.
     * Deletes users first, as the conventional delete will fail if there are users.
     *
     * @param $companyId
     *
     * @return bool
     */
    public function destroy($companyId)
    {
        $company = $this->compRepo->find($companyId);

        foreach($company->users as $user)
        {
            $user->delete();
        }

        // Reload relation so that the relation 'users' is updated,
        // lest the relation 'users' will still be present when trying to delete the company.
        $company->load('users');

        if( ! $company->users->isEmpty() )
        {
            Flash::error(trans('companies.hasRegisteredUsers') . ' ' . trans('companyVerification.deleteCompanyFailed'));
        }

        try
        {
            if( $company->delete() )
            {
                Flash::success(trans('companyVerification.deleteCompanySuccess'));
            }
        }
        catch(Exception $e)
        {
            Flash::error(trans('companyVerification.deleteCompanyFailed'));
        }

        return Redirect::route('companies.verification.index');
    }

    /**
     * Shows the view for the user to grant the privilege (i.e. delegate)
     * to verify registering companies
     * to other users.
     *
     * @return \Illuminate\View\View
     */
    public function delegate()
    {
        return View::make('companyVerification.delegate', array(
            'getAssigned'   => route('users.companies.verification.assigned'),
            'getAssignable' => route('users.companies.verification.assignable'),
        ));
    }

    /**
     * Returns a list of all users who are granted the privilege
     * to verify registering companies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignedUsers()
    {
        return Response::json($this->companyVerificationRepository->getAssignedUsers(Input::all()));
    }

    /**
     * Returns a list of all users who are not granted the privilege
     * to verify registering companies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignableUsers()
    {
        return Response::json($this->companyVerificationRepository->getAssignableUsers(Input::all()));
    }

    /**
     * Grants the users the privilege to verify registering companies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign()
    {
        $success = $this->companyVerificationRepository->assign(Input::get('users'));

        return Response::json(array(
            'success' => $success,
        ));
    }

    /**
     * Revokes the user's privilege to verify registering companies.
     *
     * @param $userId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unassign($userId)
    {
        $success = $this->companyVerificationRepository->unassign($userId);

        Flash::error(trans('companyVerification.unassignFailed'));

        if( $success )
        {
            Flash::success(trans('companyVerification.unassignSuccess'));
        }

        return Redirect::back();
    }
}