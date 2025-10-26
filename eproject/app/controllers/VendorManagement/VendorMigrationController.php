<?php
namespace VendorManagement;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use PCK\ContractGroups\Types\Role;
use PCK\Companies\CompanyRepository;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Exceptions\ValidationException;
use PCK\Forms\VendorMigration\VendorMigrationForm;
use PCK\Helpers\DBTransaction;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\VendorProfile;

class VendorMigrationController extends \BaseController {

    private $companyRepository;
    private $form;

    public function __construct(CompanyRepository $companyRepository, VendorMigrationForm $form)
    {
        $this->companyRepository = $companyRepository;
        $this->form = $form;
    }

    public function index()
    {
        $externalVendorGroups = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', '=', false)->get();

        return \View::make('vm_vendor_migration.index', [
            'externalVendorGroups' => $externalVendorGroups
        ]);
    }

    public function list()
    {
        $roles   = Role::getRolesExcept(Role::PROJECT_OWNER, Role::GROUP_CONTRACT, Role::PROJECT_MANAGER);
        $vendors = Company::select('companies.*', 'cgc.id AS vendor_group_id', 'cgc.name AS vendor_group')
                    ->join(DB::raw('contract_group_categories AS cgc'), function($join) {
                        $join->on('cgc.id', '=', 'companies.contract_group_category_id');
                        $join->on('cgc.hidden', DB::raw('IS'), DB::raw('FALSE'));
                    })
                    ->join(DB::raw('contract_group_contract_group_category AS cgcgc'), 'cgcgc.contract_group_category_id', '=', 'cgc.id')
                    ->join(DB::raw('contract_groups AS cg'), 'cg.id', '=', 'cgcgc.contract_group_id')
                    ->whereRaw('companies.confirmed IS TRUE')
                    ->whereIn('cg.group', $roles)
                    ->whereNotExists(function($query) {
                        $query->select(\DB::raw(1))
                        ->from('vendor_registrations')
                        ->whereRaw('companies.id = company_id');
                    })
                    ->distinct()
                    ->orderBy('companies.id', 'ASC')
                    ->get();

        $data = [];

        foreach($vendors as $vendor) {
            array_push($data, [
                'id'              => $vendor->id,
                'name'            => $vendor->name,
                'vendor_group_id' => $vendor->vendor_group_id,
                'vendor_group'    => $vendor->vendor_group,
            ]);
        }

        return \Response::json($data);
    }

    public function migrateSubmit()
    {
        $inputs  = \Input::all();
        $success = false;
        $errors  = null;

        $transaction = new DBTransaction();

        try {
            $transaction->begin();

            $this->form->validate($inputs);

            $companyIds    = $inputs['ids'];
            $vendorGroupId = $inputs['vendorGroupId'];
            
            $companies = Company::whereIn('id', $companyIds)->orderBy('id', 'ASC')->get();

            foreach($companies as $company)
            {
                if(is_null($company->vendorRegistration))
                {
                    $vendorRegistration      			 = new VendorRegistration();
                    $vendorRegistration->company_id  	 = $company->id;
                    $vendorRegistration->status    		 = VendorRegistration::STATUS_COMPLETED;
                    $vendorRegistration->revision    	 = 0;
                    $vendorRegistration->submission_type = VendorRegistration::SUBMISSION_TYPE_NEW;
                    $vendorRegistration->save();
                }

                if(is_null($company->vendorProfile))
                {
                    $vendorProfile       	   = new VendorProfile();
                    $vendorProfile->company_id = $company->id;
                    $vendorProfile->save();
                }

                $company->setExpiryDate();
                $company->contract_group_category_id = $vendorGroupId;
                $company->save();
            }

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }

        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}