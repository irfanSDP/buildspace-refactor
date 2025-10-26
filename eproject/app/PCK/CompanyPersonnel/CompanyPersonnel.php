<?php namespace PCK\CompanyPersonnel;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistration;

class CompanyPersonnel extends Model {

    use ModuleAttachmentTrait;

    const TYPE_DIRECTOR        = 1;
    const TYPE_SHAREHOLDERS    = 2;
    const TYPE_HEAD_OF_COMPANY = 3;

    protected $table = 'company_personnel';

    protected $fillable = [
        'vendor_registration_id',
        'type',
        'name',
        'identification_number',
        'email_address',
        'contact_number',
        'years_of_experience',
        'designation',
        'amount_of_share',
        'holding_percentage',
    ];

    public function vendorRegistration()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorRegistration');
    }

    public function attachments()
    {
        return $this->morphMany('PCK\ModuleUploadedFiles\ModuleUploadedFile', 'uploadable', null, null, 'id')->orderBy('id');
    }

    public static function getCompanyPersonnelTypeDescription($identifier = null)
    {
        $descriptions = [
            self::TYPE_DIRECTOR        => trans('vendorManagement.director'),
            self::TYPE_SHAREHOLDERS    => trans('vendorManagement.shareholder'),
            self::TYPE_HEAD_OF_COMPANY => trans('vendorManagement.headOfCompany'),
        ];

        return is_null($identifier) ? $descriptions : $descriptions[$identifier];
    }

    public static function getVendorRegistrationCompanyPersonnels(Company $company)
    {
        $companyPersonnels = [];

        foreach($company->vendorRegistration->companyPersonnel as $companyPersonnel)
        {
            array_push($companyPersonnels, [
                'label'              => $companyPersonnel->name,
                'values'             => [],
                'route_attachments'  => route('vendors.vendorRegistration.companyPersonnel.attachments.get', [$companyPersonnel->id]),
                'attachments_count'  => $companyPersonnel->attachments->count(),
            ]);
        }

        return $companyPersonnels;
    }

    public static function createOrUpdateRecord(array $inputs, self $record = null)
    {
        if(is_null($record))
        {
            $record                         = new self;
            $record->vendor_registration_id = $inputs['vendor_registration_id'];
        }

        $record->type                   = $inputs['type'];
        $record->name                   = $inputs['name'];
        $record->identification_number  = $inputs['identification_number'];
        $record->email_address          = in_array($inputs['type'], [self::TYPE_DIRECTOR, self::TYPE_HEAD_OF_COMPANY]) ? $inputs['email_address'] : null;
        $record->contact_number         = in_array($inputs['type'], [self::TYPE_DIRECTOR, self::TYPE_HEAD_OF_COMPANY]) ? $inputs['contact_number'] : null;
        $record->years_of_experience    = in_array($inputs['type'], [self::TYPE_DIRECTOR, self::TYPE_HEAD_OF_COMPANY]) ? $inputs['years_of_experience'] : 0;
        $record->designation            = ($inputs['type'] == self::TYPE_SHAREHOLDERS) ? $inputs['designation'] : null;
        $record->amount_of_share        = ($inputs['type'] == self::TYPE_SHAREHOLDERS) ? $inputs['amount_of_share'] : 0;
        $record->holding_percentage     = ($inputs['type'] == self::TYPE_SHAREHOLDERS) ? $inputs['holding_percentage'] : 0;
        $record->save();

        return self::find($record->id);
    }

    // find duplicate company personnels among companies
    // matching by identification number
    public static function getDuplicateCompanyPersonnelsGroupByCompany(array $companyIds, $withInfo = false)
    {
        if(empty($companyIds)) return [];

        $clause = " SELECT fc.company_id, ARRAY_TO_JSON(fc.company_personnel_ids) AS company_personnel_ids FROM final_cte fc";

        if($withInfo)
        {
            $clause = "SELECT DISTINCT UNNEST(fc.company_personnel_ids) AS company_personnel_id FROM final_cte fc";
        }

        $query = "WITH final_vendor_registrations_cte AS (
                      SELECT RANK() OVER (PARTITION BY company_id ORDER BY revision DESC) AS rank, * FROM vendor_registrations WHERE company_id IN (" . implode(', ', $companyIds) . ") AND status = " . VendorRegistration::STATUS_COMPLETED . " AND deleted_at IS NULL
                  ), 
                  company_personnel_cte AS (
                      SELECT c.id AS company_id, cp.id AS company_personnel_id, REGEXP_REPLACE(cp.identification_number, '[^a-zA-Z0-9]+', '', 'gn') AS identification_number 
                      FROM final_vendor_registrations_cte vr
                      INNER JOIN company_personnel cp ON cp.vendor_registration_id = vr.id
                      INNER JOIN companies c ON c.id = vr.company_id
                      WHERE rank = 1
                      AND c.confirmed IS TRUE
                      AND (LENGTH(REGEXP_REPLACE(cp.identification_number, '[^a-zA-Z0-9]+', '', 'gn')) > 0)
                      ORDER BY c.id ASC, cp.type ASC
                  ), 
                  final_cte AS (
                      SELECT UNNEST(ARRAY_AGG(DISTINCT cp.company_id)) AS company_id, cp.identification_number, 
                      ARRAY_AGG(cp.company_personnel_id ORDER BY cp.company_personnel_id ASC) AS company_personnel_ids 
                      FROM company_personnel_cte cp
                      GROUP BY cp.identification_number 
                      HAVING CARDINALITY(ARRAY_AGG(DISTINCT cp.company_id)) > 1
                  )
                  {$clause};";

        return DB::select(DB::raw($query));
    }
}