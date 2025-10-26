<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Companies\Company;
use PCK\Vendor\Vendor;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\VendorRegistration\VendorProfile;

class RevertVendorStatusToRegistering extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:revert-vendor-to-registering-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reverts the vendor back to the registration stage. Unsets the company as a vendor.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $companyIds = $this->argument('company_ids');

        if(empty($companyIds))
        {
            $this->info('No company ids specified.');
        }

        foreach($companyIds as $companyId)
        {
            $company = Company::find($companyId);

            if(!$company)
            {
                $this->info("Company (id:{$companyId}) not found.");

                continue;
            }

            if(is_null($company->activation_date))
            {
                $this->info("Company (id:{$companyId}, name:{$company->name}) is not an active vendor.");

                continue;
            }

            $this->revert($company);
        }
    }

    public function revert(Company $company)
    {
        $transaction = new \PCK\Helpers\DBTransaction(['buildspace']);
        $transaction->begin();

        try
        {
            $this->unsetAsVendor($company);
            $this->restartRegistration($company);

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            $this->error($e->getMessage());
        }
    }

    protected function unsetAsVendor(Company $company)
    {
        Vendor::where('company_id', '=', $company->id)->delete();

        $company->activation_date = null;
        $company->expiry_date = null;
        $company->deactivation_date = null;
        $company->deactivated_at = null;

        $company->save();
    }

    protected function restartRegistration(Company $company)
    {
        $vendorRegistrationIds = VendorRegistration::where('company_id', '=', $company->id)->lists('id');

        $vendorRegistration = VendorRegistration::create(array(
            'company_id' => $company->id
        ));

        $latestVendorRegistration = $company->vendorRegistration;

        if($latestVendorRegistration)
        {
            $latestVendorRegistration->cloneVendorRegistrationForm($vendorRegistration, true);
            $latestVendorRegistration->clonePreQualification($vendorRegistration, true);
            $latestVendorRegistration->cloneCompanyPersonnel($vendorRegistration);
            $latestVendorRegistration->cloneSupplierCreditFacilities($vendorRegistration);
            $latestVendorRegistration->cloneProjectTrackRecord($vendorRegistration);
        }

        VendorRegistration::whereIn('id', $vendorRegistrationIds)->delete();

        VendorProfile::where('company_id', '=', $company->id)->delete();
    }

    protected function getArguments()
    {
        return array(
                array('company_ids', InputArgument::IS_ARRAY, 'Ids of companies to revert.'),
        );
    }
}