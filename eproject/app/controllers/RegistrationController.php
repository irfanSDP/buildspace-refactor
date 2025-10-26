<?php

use PCK\Companies\CompanyRepository;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Forms\RegistrationForm;
use PCK\Helpers\DBTransaction;
use PCK\Users\UserRepository;
use PCK\LoginRequestFormSetting\LoginRequestFormSetting;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\BusinessEntityType\BusinessEntityType;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Settings\SystemSettings;
use PCK\SystemModules\SystemModuleConfiguration;

class RegistrationController extends \BaseController {

    private $companyForm;
    private $companyRepository;
    private $userRepository;

    public function __construct(
        RegistrationForm $companyForm,
        CompanyRepository $companyRepository,
        UserRepository $userRepository
    )
    {
        $this->companyForm       = $companyForm;
        $this->companyRepository = $companyRepository;
        $this->userRepository    = $userRepository;
    }

    /**
     * Show the form to register a company and a user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $urlCountry = route('country');
        $urlStates  = route('country.states');
        $stateId    = Input::old('company_state_id', null);

        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        $urlContractGroupCategories = route('registration.contractGroupCategories');
        $contractGroupCategoryId    = Input::old('contract_group_category_id');

        $urlVendorCategories           = null;
        $vendorCategoryId              = null;
        $multipleVendorCategories      = false;
        $businessEntityTypes           = [];
        $allowOtherBusinessEntityTypes = false;
        $settings                      = null;

        if($vendorManagementModuleEnabled)
        {
            $urlContractGroupCategories    = route('registration.externalVendors.contractGroupCategories');
            $urlVendorCategories           = route('registration.vendorCategories');
            $vendorCategoryId              = Input::old('vendor_category_id');
            $multipleVendorCategories      = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');
            $businessEntityTypes           = BusinessEntityType::where('hidden', false)->orderBy('name', 'ASC')->get();
            $allowOtherBusinessEntityTypes = SystemSettings::getValue('allow_other_business_entity_types');
            $settings                      = LoginRequestFormSetting::first();
        }

        return View::make('registration.companyAndUser', compact(
            'vendorManagementModuleEnabled',
            'multipleVendorCategories',
            'urlCountry',
            'urlStates',
            'stateId',
            'urlContractGroupCategories',
            'urlVendorCategories',
            'contractGroupCategoryId',
            'vendorCategoryId',
            'businessEntityTypes',
            'settings',
            'allowOtherBusinessEntityTypes'
        ));
    }

    /**
     * Saves the details.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $input = Input::all();

        if( ! isset( $input['agree-to-terms-and-conditions'] ))
        {
            $messageBag = new \Illuminate\Support\MessageBag();
            $messageBag->add('agree-to-terms-and-privacy', 'You shall not pass');

            return Redirect::route('register')->withInput()->withErrors($messageBag);
        }

        $this->companyForm->validate($input);

        $transaction = new DBTransaction(array( 'buildspace' ));

        $transaction->begin();

        try
        {
            $company = $this->companyRepository->add($this->extractCompanyInput($input));

            $userInput = $this->extractUserInput($input);

            $userInput['relation_column']               = 'company_id';
            $userInput[ $userInput['relation_column'] ] = $company->id;

            $user = $this->userRepository->signUp($userInput, false);

            if( ! $user->exists or (isset($user->errors) && $user->errors->count()))
            {
                $transaction->rollback();

                $messageBag = $this->markUserMessageBag($user->errors);

                return Redirect::back()->withInput(Input::except('password'))->with('errors', $messageBag);
            }
            /* User Validation End */

            // Remove loaded relations.
            $user = \PCK\Users\User::find($user->id);

            $user->company_id = $company->id;
            $user->is_admin   = true;

            $user->save();

            $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

            if($vendorManagementModuleEnabled)
            {
                $company->setTemporaryLoginAccountValidity();

                VendorRegistration::create(array(
                    'company_id' => $company->id
                ));

                \Event::fire('vendor.newlyRegistered', $user);
            }

            $transaction->commit();
        }
        catch(Exception $e)
        {
            $transaction->rollback();

            Flash::error(trans('auth.registrationFailed'));

            \Log::error($e->getMessage());

            return Redirect::route('register')->withInput();
        }

        return Redirect::route('register.success');
    }

    /**
     * Extracts the input relevant to the Company.
     *
     * @param $input
     *
     * @return array
     */
    private function extractCompanyInput($input)
    {
        $companyInput = array();

        $companyInput['_token']                     = $input['_token'];
        $companyInput['name']                       = $input['company'];
        $companyInput['address']                    = $input['address'];
        $companyInput['contract_group_category_id'] = $input['contract_group_category_id'] ?? null;
        $companyInput['vendor_category_id']         = $input['vendor_category_id'] ?? null;
        $companyInput['business_entity_type_id']    = isset( $input['business_entity_type_id'] ) ? $input['business_entity_type_id'] : null;
        $companyInput['business_entity_type_other'] = isset( $input['business_entity_type_other'] ) ? $input['business_entity_type_other'] : null;
        $companyInput['main_contact']               = $input['main_contact'];
        $companyInput['reference_no']               = $input['reference_no'];
        $companyInput['tax_registration_no']        = $input['tax_registration_no'];
        $companyInput['email']                      = $input['company_email'];
        $companyInput['telephone_number']           = $input['telephone_number'];
        $companyInput['fax_number']                 = $input['fax_number'];
        $companyInput['country_id']                 = $input['country_id'];
        $companyInput['state_id']                   = isset( $input['state_id'] ) ? $input['state_id'] : null;

        return $companyInput;
    }

    /**
     * Extracts the input relevant to the User.
     *
     * @param $input
     *
     * @return array
     */
    private function extractUserInput($input)
    {
        $userInput = array();

        $userInput['_token']         = $input['_token'];
        $userInput['name']           = $input['user_name'];
        $userInput['contact_number'] = $input['user_contact_number'];
        $userInput['email']          = $input['user_email'];

        return $userInput;
    }

    /**
     * Mark the errors as errors for the User form.
     *
     * @param \Illuminate\Support\MessageBag $messageBag
     *
     * @return \Illuminate\Support\MessageBag
     */
    private function markUserMessageBag(\Illuminate\Support\MessageBag $messageBag)
    {
        ! $messageBag->has('name') ?: $messageBag->add('user_name', $messageBag->first('name'));
        ! $messageBag->has('contact_number') ?: $messageBag->add('user_contact_number', $messageBag->first('contact_number'));
        ! $messageBag->has('email') ?: $messageBag->add('user_email', $messageBag->first('email'));

        return $messageBag;
    }

    /**
     * Returns view for when the registration is successful.
     *
     * @return \Illuminate\View\View
     */
    public function success()
    {
        $alternativeView = getenv('REGISTRATION_SUCCESS_ALTERNATIVE_VIEW');

        $view = $alternativeView ? $alternativeView : 'success';
        
        return View::make('registration.' . $view);
    }

    public function getExternalVendorContractGroupCategories()
    {
        $data = ContractGroupCategory::select('id', 'name AS description')
            ->whereNotIn('name', ContractGroupCategory::getPrivateGroupNames())
            ->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        return Response::json(array(
            'success' => true,
            'default' => null,
            'data'    => $data
        ));
    }

}
