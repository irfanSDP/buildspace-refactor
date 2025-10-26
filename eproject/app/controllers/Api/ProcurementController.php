<?php
namespace Api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\VendorRegistration\VendorRegistration;

class ProcurementController extends \BaseController
{
    protected $expectedToken = 'omkoFF3J2J6XywgbZF81Si5AK7uJNza6yos0FnrL5RdnTkLacsKS60LxcFxe6mPR';

    public function users($allow_access_to_gp)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('users')->where('allow_access_to_gp', $allow_access_to_gp)
                                    ->where("is_gp_vendor", false)
                                    ->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    $company = DB::table('companies')->where('id', $item->company_id)->first();
                    return [
                        'id' => $item->id,
                        'company_id' => $item->company_id,
                        'company' => $company ?: null,
                        'is_super_admin' => $item->is_super_admin,
                        'is_admin' => $item->is_admin,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                        'account_blocked_status' => $item->account_blocked_status,
                        'allow_access_to_buildspace' => $item->allow_access_to_buildspace,
                        'allow_access_to_gp' => $item->allow_access_to_gp,
                        'is_gp_admin' => $item->is_gp_admin,
                        'password_updated_at' => $item->password_updated_at,
                        'purge_date' => $item->purge_date,
                        'confirmed' => $item->confirmed,
                        'name' => $item->name,
                        'contact_number' => $item->contact_number,
                        'username' => $item->username,
                        'email' => $item->email,
                        'password' => $item->password,
                        'confirmation_code' => $item->confirmation_code,
                        'remember_token' => $item->remember_token,
                        'designation' => $item->designation,
                        'gp_access_token' => $item->gp_access_token,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendors($status)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken)
        {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        if(!in_array($status, [Company::STATUS_ACTIVE, Company::STATUS_EXPIRED, Company::STATUS_DEACTIVATED]))
        {
            return \Response::json(['message' => 'Invalid Request'], 500); 
        }

        $registrationStatusClause = null;

        switch($status)
        {
            case Company::STATUS_ACTIVE:
                $registrationStatusClause = " AND c.activation_date IS NOT NULL AND (c.expiry_date > NOW()) AND c.deactivated_at IS NULL ";
                break;
            case Company::STATUS_EXPIRED:
                $registrationStatusClause = " AND c.expiry_date IS NOT NULL AND (c.expiry_date <= NOW()) AND deactivated_at IS NULL ";
                break;
            case Company::STATUS_DEACTIVATED:
                $registrationStatusClause = " AND c.deactivated_at IS NOT NULL ";
                break;
        }

        /**
         * Companies
         */
        $query = "WITH completed_vendor_registrations AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS RANK, * 
                      FROM vendor_registrations 
                      WHERE deleted_at IS NULL
                      AND status = " . VendorRegistration::STATUS_COMPLETED . "
                      AND revision = 0
                  )
                  SELECT c.id, c.name, c.address, c.telephone_number, c.fax_number, c.email, c.country_id, c.state_id, c.business_entity_type_id, c.main_contact, c.reference_no, c.tax_registration_no
                  FROM companies c
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id
                  INNER JOIN completed_vendor_registrations vr on vr.company_id = c.id
                  WHERE c.confirmed IS TRUE 
                  {$registrationStatusClause}
                  AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                  AND vr.rank = 1
                  ORDER BY c.id ASC";

        $companiesData = DB::select(DB::raw($query));

        $companyIds = array_column($companiesData, 'id');

        if(empty($companyIds))
        {
            return Response::json([
                'success' => true,
                'data' => [],
            ], 200);
        }

        $usersByCompany = [];

        $companyUserRecords = User::where('confirmed', '=', true)
            ->whereIn('company_id', $companyIds)
            ->orderBy('company_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        // grant GP access
        foreach($companyUserRecords as $user)
        {
            if (!$user->gp_access_token)
            {
                $user->gp_access_token = Str::random(64);
            }

            $user->allow_access_to_gp = true;
            $user->is_gp_vendor = true;
            $user->save();
        }

        foreach($companyUserRecords as $record)
        {
            $usersByCompany[$record->company_id][] = [
                'id' => $record->id,
                'name' => $record->name,
                'email' => $record->email,
                'password' => $record->password,
                'is_admin' => $record->is_admin,
                'confirmed' => $record->confirmed,
                'designation' => $record->designation,
                'contact_number' => $record->contact_number,
                'allow_access_to_gp' => $record->allow_access_to_gp,
                'gp_access_token' => $record->gp_access_token,
                'is_gp_vendor'  => $record->is_gp_vendor,
            ];
        }

        /**
         * Vendor Group
         */
        $companyVendorGroupQuery = "SELECT c.id AS company_id, cgc.id AS contract_group_category_id, cgc.name AS contract_group_category
                                    FROM companies c 
                                    INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                                    WHERE c.id IN (" . implode(', ', $companyIds) . ")
                                    ORDER BY c.id ASC";
        
        $companyVendorGroupResults = DB::select(DB::raw($companyVendorGroupQuery));

        $vendorGroupByCompany = [];

        foreach($companyVendorGroupResults as $record)
        {
            $vendorGroupByCompany[$record->company_id] = [
                'id' => $record->contract_group_category_id,
                'name' => $record->contract_group_category,
            ];
        }

        /**
         * Vendor Categories
         */
        $companyVendorCategoriesQuery = "SELECT cvc.company_id, cvc.vendor_category_id, vc.code , vc.name
                                         FROM company_vendor_category cvc
                                         INNER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id
                                         WHERE cvc.company_id IN (" . implode(', ', $companyIds) . ")
                                         ORDER BY cvc.company_id ASC, vc.id ASC";

        $companyVendorCategoryResults = DB::select(DB::raw($companyVendorCategoriesQuery));

        $vendorCategoriesByCompany = [];

        foreach($companyVendorCategoryResults as $record)
        {
            $vendorCategoriesByCompany[$record->company_id][] = [
                'id' => $record->vendor_category_id,
                'code' => $record->code,
                'name' => $record->name,
            ];
        }

        /**
         * Compile all the data
         */
        $data = array_map(function ($data) use ($usersByCompany, $vendorCategoriesByCompany, $vendorGroupByCompany) {
            return [
                'company' => (array) $data,
                'users' => array_key_exists($data->id, $usersByCompany) ? $usersByCompany[$data->id] : [],
                'vendor_group' => array_key_exists($data->id, $vendorGroupByCompany) ? $vendorGroupByCompany[$data->id] : null,
                'vendor_categories' => array_key_exists($data->id, $vendorCategoriesByCompany) ? $vendorCategoriesByCompany[$data->id]: [],
            ];
        }, (array) $companiesData);

        return Response::json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function subsidiaries()
    {
        $authToken = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('subsidiaries')->get();

        if (\count($items) > 0) {
            // Convert items to array and index by ID
            $indexed = [];
            foreach ($items as $item) {
                $indexed[$item->id] = [
                    'company_id' => $item->company_id,
                    'updated_at' => $item->updated_at,
                    'parent_id' => $item->parent_id,
                    'id' => $item->id,
                    'created_at' => $item->created_at,
                    'name' => $item->name,
                    'identifier' => $item->identifier,
                    'children' => []
                ];
            }

            // Build the tree structure
            $tree = [];
            foreach ($indexed as $id => $item) {
                if (is_null($item['parent_id'])) {
                    // This is a root item
                    $tree[] = &$indexed[$id];
                } else {
                    // This is a child item
                    $indexed[$item['parent_id']]['children'][] = &$indexed[$id];
                }
            }

            return Response::json([
                'success' => true,
                'data' => array_values($tree)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }
}