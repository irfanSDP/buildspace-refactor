<?php namespace PCK\ContractGroupCategory;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\Parameter;

class ContractGroupCategory extends Model {

    const API_DEFAULT_NAME = "API Contract Group";
    const TYPE_INTERNAL = 1;
    const TYPE_EXTERNAL = 2;

    const BUSINESS_UNIT_NAME           = 'Business Unit';
    const GROUP_CONTRACT_DIVISION_NAME = 'Group Contract Division';
    const PROJECT_MANAGER_NAME         = 'Project Manager';
    const CONTRACTOR_NAME              = 'Contractor';
    const CONSULTANT_NAME              = 'Consultant';

    const VENDOR_TYPE_DEFAULT    = 1;
    const VENDOR_TYPE_CONTRACTOR = 2;
    const VENDOR_TYPE_CONSULTANT = 4;
    const VENDOR_TYPE_SUPPLIER   = 8;

    protected $table = 'contract_group_categories';

    protected $fillable = ['name', 'code', 'type'];

    public function contractGroups()
    {
        return $this->belongsToMany('PCK\ContractGroups\ContractGroup')->withTimestamps();
    }

    public function vendorCategories()
    {
        return $this->hasMany('PCK\VendorCategory\VendorCategory', 'contract_group_category_id');
    }

    public function isTypeInternal()
    {
        return ($this->type == self::TYPE_INTERNAL);
    }

    public function isTypeExternal()
    {
        return ($this->type == self::TYPE_EXTERNAL);
    }

    public static function getPrivateGroupNames()
    {
        return array(
            self::BUSINESS_UNIT_NAME,
            self::GROUP_CONTRACT_DIVISION_NAME,
            self::PROJECT_MANAGER_NAME,
        );
    }

    public static function getVendorTypes($identifier = null)
    {
        $vendorTypes = [
            self::VENDOR_TYPE_DEFAULT    => trans('contractGroupCategories.default'),
            self::VENDOR_TYPE_CONTRACTOR => trans('contractGroupCategories.contractor'),
            self::VENDOR_TYPE_CONSULTANT => trans('contractGroupCategories.consultant'),
            self::VENDOR_TYPE_SUPPLIER   => trans('contractGroupCategories.supplier'),
        ];

        return is_null($identifier) ? $vendorTypes : $vendorTypes[$identifier];
    }

    /**
     * Returns true if this category is associated with at least one of the Contract Group(s).
     *
     * @param int|array $contractGroupIds
     *
     * @return bool
     */
    public function includesContractGroups($contractGroupIds)
    {
        $contractGroupIds = Parameter::toArray($contractGroupIds);

        foreach($this->contractGroups as $contractGroup)
        {
            if( in_array($contractGroup->id, $contractGroupIds) )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the contract group category has a privilege.
     *
     * @param $privilegeIdentifier
     *
     * @return bool
     */
    public function hasPrivilege($privilegeIdentifier)
    {
        $record = ContractGroupCategoryPrivilege::where('identifier', '=', $privilegeIdentifier)
            ->where('contract_group_category_id', '=', $this->id)
            ->first();

        return ! is_null($record);
    }

    /**
     * Sets a privilege for the contract group category.
     *
     * @param      $privilegeIdentifier
     * @param bool $permit
     *
     * @return bool
     */
    public function setPrivilege($privilegeIdentifier, $permit = true)
    {
        if( $permit )
        {
            ContractGroupCategoryPrivilege::firstOrCreate(array(
                'identifier'                 => $privilegeIdentifier,
                'contract_group_category_id' => $this->id,
            ));
        }
        else
        {
            ContractGroupCategoryPrivilege::where('identifier', '=', $privilegeIdentifier)
                ->where('contract_group_category_id', '=', $this->id)
                ->delete();
        }

        return true;
    }

    public static function getRecordsByIds(Array $ids)
    {
        if(count($ids) == 0) return [];

        $query = "SELECT id, name
                    FROM contract_group_categories 
                    WHERE id IN (" . implode(', ', $ids) . ")
                    ORDER BY id ASC;";

        $queryResult = DB::select(DB::raw($query));

        return array_map(function($record) {
			return trim($record);
		}, array_column($queryResult, 'name'));
    }
}