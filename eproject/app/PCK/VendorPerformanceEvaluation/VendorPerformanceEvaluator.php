<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroups\Types\Role;
use PCK\Companies\Company;
use PCK\Users\User;

class VendorPerformanceEvaluator extends Model {

    protected $table = 'vendor_performance_evaluators';

    protected $fillable = ['vendor_performance_evaluation_id', 'company_id', 'user_id'];

    public function vendorPerformanceEvaluation()
    {
        return $this->belongsTo(VendorPerformanceEvaluation::class, 'vendor_performance_evaluation_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function purge(VendorPerformanceEvaluation $vendorPerformanceEvaluation)
    {
        $query = "SELECT DISTINCT company_id FROM " . (new self())->getTable() . " WHERE vendor_performance_evaluation_id = " . $vendorPerformanceEvaluation->id . ";";

        $companyIds = array_column(DB::select(DB::raw($query)), 'company_id');

        $companies = Company::whereIn('id', $companyIds)->get();

        $buCompanyId = null;

        foreach($companies as $company)
        {
            if($vendorPerformanceEvaluation->project && $company->hasProjectRole($vendorPerformanceEvaluation->project, Role::PROJECT_OWNER))
            {
                $buCompanyId = $company->id;

                break;
            }
        }

        if($buCompanyId)
        {
            self::where('vendor_performance_evaluation_id', $vendorPerformanceEvaluation->id)->where('company_id', '!=', $buCompanyId)->delete();
        }

        return true;
    }
}