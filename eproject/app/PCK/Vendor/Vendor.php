<?php namespace PCK\Vendor;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\Base\Helpers;
use PCK\VendorPerformanceEvaluation\CycleScore;
use Carbon\Carbon;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistration;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\ContractGroupCategory\ContractGroupCategory;

class Vendor extends Model {

    const TYPE_ACTIVE                             = 1;
    const TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION = 2;
    const TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST = 4;
    const TYPE_WATCH_LIST                         = 8;

    protected $table = 'vendors';

    protected $fillable = ['vendor_work_category_id', 'company_id', 'type', 'vendor_evaluation_cycle_score_id', 'watch_list_entry_date', 'watch_list_release_date', 'is_qualified'];

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function score()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\CycleScore', 'vendor_evaluation_cycle_score_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function($model)
        {
            $model->type = self::TYPE_ACTIVE;
        });
    }

    public function moveToNominatedWatchList()
    {
        $logger = new VendorTypeChangeLogger($this);

        switch($this->type)
        {
            case self::TYPE_ACTIVE:
                \Log::info("Moving active vendor to NWL [vendor id:{$this->id}]");
                $this->type = self::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION;
                break;
            case self::TYPE_WATCH_LIST:
                \Log::info("Moving watch list vendor to NWL [vendor id:{$this->id}]");
                $this->type = self::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST;
                $this->watch_list_entry_date   = null;
                $this->watch_list_release_date = null;
                break;
            default:
                return;
        }

        $this->save();

        $logger->log();

        $this->company->updateVendorStatus();
    }

    public function moveToActiveVendorList()
    {
        \Log::info("Moving vendor to Approved Vendor List [vendor id:{$this->id}]");

        $logger = new VendorTypeChangeLogger($this);

        $this->type = self::TYPE_ACTIVE;

        $this->save();

        $logger->log();

        $this->company->updateVendorStatus();
    }

    public function moveToWatchList()
    {
        \Log::info("Moving vendor to Watch List [vendor id:{$this->id}]");

        $logger = new VendorTypeChangeLogger($this);

        switch(VendorProfileModuleParameter::getValue('vendor_retain_period_in_wl_unit'))
        {
            case VendorProfileModuleParameter::DAY:
                $timeUnit = 'days';
                break;
            case VendorProfileModuleParameter::WEEK:
                $timeUnit = 'weeks';
                break;
            case VendorProfileModuleParameter::MONTH:
                $timeUnit = 'months';
                break;
            default:
                throw new \Exception("Invalid time unit");
        }

        $this->type = self::TYPE_WATCH_LIST;

        $this->watch_list_entry_date   = Carbon::now();
        $this->watch_list_release_date = Helpers::getTimeFromNow(VendorProfileModuleParameter::getValue('vendor_retain_period_in_wl_value'), $timeUnit);

        $this->save();

        $logger->log();

        $this->company->updateVendorStatus();
    }

    public function getTypeNameAttribute()
    {
        switch($this->type)
        {
            case self::TYPE_WATCH_LIST:
                $name = trans('vendorManagement.watchList');
                break;
            case self::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION:
            case self::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST:
                $name = trans('vendorManagement.nomineesForWatchList');
                break;
            default :
                $name = trans('vendorManagement.activeVendorList');
        }

        return $name;
    }

    public function canBeDeleted()
    {
        //for now only vpe scores and project track records are known to have direct relation with vendors. Might need to add other cases to validate either vendor can be removed or not
        if($this->vendor_evaluation_cycle_score_id)
        {
            return false;
        }

        $projectTrackRecordVendorWorkCategories = TrackRecordProject::where('vendor_registration_id', '=', $this->company->finalVendorRegistration->id)->lists('vendor_work_category_id');

        if(in_array($this->vendor_work_category_id, $projectTrackRecordVendorWorkCategories))
        {
            return false;
        }

        return true;
    }

    public function getLatestPerformanceEvaluationCycleScore()
    {
        if($this->vendor_evaluation_cycle_score_id)
        {
            return CycleScore::findOrFail($this->vendor_evaluation_cycle_score_id);
        }
        
        return null;
    }

    public static function getTotalActiveVendorGroupByCompany()
    {
        return Company::select('companies.id')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->leftJoin('vendors', function($join) {
            $join->on('vendors.company_id', '=', 'companies.id');
            $join->on(\DB::raw("vendors.type = " . Vendor::TYPE_ACTIVE), \DB::raw(''), \DB::raw(''));
        })
        ->leftJoin('vendor_work_categories', function($join) {
            $join->on('vendor_work_categories.id', '=', 'vendors.vendor_work_category_id');
            $join->on(\DB::raw('vendor_work_categories.hidden IS FALSE'), \DB::raw(''), \DB::raw(''));
        })
        ->where('companies.confirmed', '=', true)
        ->whereNull('companies.deactivated_at')
        ->whereNotNull('companies.activation_date')
        ->where('contract_group_categories.hidden', '=', false)
        ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->groupBy('companies.id')
        ->get()
        ->count();
    }

    public static function getTotalDeactivedVendor()
    {
        //vendor under watch list should stay in watch list
        return Company::select('companies.id')
        ->join('vendors', 'vendors.company_id', '=', 'companies.id')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->where('companies.confirmed', '=', true)
        ->where('vendors.type', '=', self::TYPE_ACTIVE)
        ->whereNotNull('companies.deactivated_at')
        ->whereNotNull('companies.activation_date')
        ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->where('contract_group_categories.hidden', '=', false)
        ->groupBy('companies.id')
        ->get()//because of group by we need to call get() if not result return 1 as first record
        ->count();
    }

    public static function getTotalWatchListVendor()
    {
        return Vendor::select('vendors.id')
        ->join('companies', 'companies.id', '=', 'vendors.company_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendor_categories', 'vendor_categories.contract_group_category_id', '=', 'companies.contract_group_category_id')
        ->where('vendors.type', '=', self::TYPE_WATCH_LIST)
        ->where('vendor_work_categories.hidden', '=', false)
        ->where('companies.confirmed', '=', true)
        ->whereNull('companies.deactivated_at')
        ->whereNotNull('companies.activation_date')
        ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->where('contract_group_categories.hidden', '=', false)
        ->where('vendor_categories.hidden', '=', false)
        ->where('contract_group_categories.hidden', '=', false)
        ->groupBy('vendors.id')
        ->get()//because of group by we need to call get() if not result return 1 as first record
        ->count();
    }

    public static function getTotalNomineesForWatchListVendors()
    {
        return Vendor::select(\DB::raw('COUNT(companies.id)'))
            ->join('companies', 'companies.id', '=', 'vendors.company_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where(function($query){
                $query->where('vendors.type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION);
                $query->orWhere('vendors.type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST);
            })
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendors.vendor_work_category_id')
            ->whereIn('vendors.type', [self::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION, self::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST] )
            ->where('companies.confirmed', '=', true)
            ->where('vendor_work_categories.hidden', '=', false)
            ->where('contract_group_categories.hidden', '=', false)
            ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->whereNull('companies.deactivated_at')
            ->whereNotNull('companies.activation_date')
            ->first()
            ->count;
    }

    public static function getTotalUnsuccessfullyRegisteredVendors()
    {
        return VendorRegistration::select('vendor_registrations.id')
        ->join('companies', 'companies.id', '=', 'vendor_registrations.company_id')
        ->whereNotNull('vendor_registrations.unsuccessful_at')
        ->whereNull('vendor_registrations.deleted_at')
        ->groupBy('vendor_registrations.id')
        ->get()//because of group by we need to call get() if not result return 1 as first record
        ->count();
    }

    public static function flushRecords(Company $company)
    {
        foreach($company->vendors as $record)
        {
            $record->delete();
        }
    }

    public static function getTrackRecordProjectVendorWorkSubCategories(array $companyIds = [])
    {
        if(empty($companyIds)) return [];

        $companyIdsString = implode(', ', $companyIds);

        $query = "WITH final_vendor_registrations AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS RANK, *  
                      FROM vendor_registrations 
                      WHERE company_id IN (" . $companyIdsString . ") 
                      AND deleted_at IS NULL 
                      AND status = " . VendorRegistration::STATUS_COMPLETED . "
                  ),
                  track_record_projects_cte AS (
                      SELECT c.id AS company_id, t.*
                      FROM track_record_projects t
                      INNER JOIN final_vendor_registrations vr ON vr.id = t.vendor_registration_id 
                      INNER JOIN companies c ON c.id = vr.company_id
                      WHERE vr.rank = 1
                  )
                  SELECT v.company_id , v.vendor_work_category_id, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vws.code) FILTER (WHERE vws.code IS NOT NULL)) AS code, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT trpvws.vendor_work_subcategory_id) FILTER (WHERE trpvws.vendor_work_subcategory_id IS NOT NULL)) AS vendor_work_subcategory_ids, 
                  ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vws.name) FILTER (WHERE vws.name IS NOT NULL)) AS vendor_work_subcategories
                  FROM vendors v
                  LEFT OUTER JOIN track_record_projects_cte trp ON trp.vendor_work_category_id = v.vendor_work_category_id AND trp.company_id = v.company_id 
                  LEFT OUTER JOIN track_record_project_vendor_work_subcategories trpvws ON trpvws.track_record_project_id = trp.id 
                  LEFT OUTER JOIN vendor_work_subcategories vws ON vws.id = trpvws.vendor_work_subcategory_id 
                  WHERE v.company_id in (" . $companyIdsString . ") 
                  GROUP BY v.company_id, v.vendor_work_category_id
                  ORDER BY v.company_id ASC, v.vendor_work_category_id ASC;";

        $queryResults = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResults as $result)
        {
            $data[$result->company_id][$result->vendor_work_category_id] = [
                'codes' => json_decode($result->code),
                'ids'   => json_decode($result->vendor_work_subcategory_ids),
                'names' => json_decode($result->vendor_work_subcategories),
            ];
        }

        return $data;
    }
}