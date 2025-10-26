<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;
use PCK\ConsultantManagement\ConsultantManagementRfpRevision;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;

class ConsultantManagementCallingRfp extends Model
{
    protected $table = 'consultant_management_calling_rfp';

    protected $fillable = ['consultant_management_rfp_revision_id', 'calling_rfp_date', 'closing_rfp_date', 'status'];

    const STATUS_DRAFT = 1;
    const STATUS_APPROVAL = 2;
    const STATUS_APPROVED = 4;

    const STATUS_DRAFT_TEXT = 'Draft';
    const STATUS_APPROVAL_TEXT = 'Pending Approval';
    const STATUS_APPROVED_TEXT = 'Approved';

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $model)
        {
            $model->status = ConsultantManagementCallingRfp::STATUS_DRAFT;
        });

        self::created(function(self $model)
        {
            $user = \Confide::user();
            $rev  = $model->consultantManagementRfpRevision;

            $consultantData = [];

            $consultants = $rev->listOfConsultant->consultants()->where('status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)->get();

            foreach($consultants as $consultant)
            {
                $consultantData[] = [
                    'consultant_management_calling_rfp_id' => $model->id,
                    'company_id' => $consultant->company_id,
                    'status'     => $consultant->status,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            if(!empty($consultantData))
            {
                ConsultantManagementCallingRfpCompany::insert($consultantData);
            }

            $openRfp = new ConsultantManagementOpenRfp;
            $openRfp->consultant_management_rfp_revision_id = $model->consultant_management_rfp_revision_id;
            $openRfp->created_by = $user->id;
            $openRfp->updated_by = $user->id;

            $openRfp->save();
        });
    }

    public function consultantManagementRfpRevision()
    {
        return $this->belongsTo(ConsultantManagementRfpRevision::class, 'consultant_management_rfp_revision_id');
    }

    public function consultants()
    {
        return $this->hasMany(ConsultantManagementCallingRfpCompany::class, 'consultant_management_calling_rfp_id');
    }

    public function getStatusText()
    {
        switch($this->status)
        {
            case self::STATUS_DRAFT:
                return self::STATUS_DRAFT_TEXT;
            case self::STATUS_APPROVAL:
                return self::STATUS_APPROVAL_TEXT;
            case self::STATUS_APPROVED:
                return self::STATUS_APPROVED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function editableByUser(User $user)
    {
        $consultantManagementContract = $this->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp->consultantManagementContract;
        if($this->status == self::STATUS_DRAFT && $user->isConsultantManagementCallingRfpEditor($consultantManagementContract))
        {
            return true;
        }

        return false;
    }

    public function needApprovalFromUser(User $user)
    {
        if($this->status != self::STATUS_APPROVAL)
        {
            return false;
        }

        $latestVersion = ConsultantManagementCallingRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $this->id)
            ->groupBy('consultant_management_calling_rfp.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }

        $latestVerifierLog = ConsultantManagementCallingRfpVerifierVersion::select("consultant_management_call_rfp_verifier_versions.id AS id", "consultant_management_call_rfp_verifier_versions.status AS status", "consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id")
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $this->id)
            ->where('consultant_management_call_rfp_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_calling_rfp_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        $previousVerifierLog = ConsultantManagementCallingRfpVerifierVersion::select("consultant_management_calling_rfp_verifiers.id AS id", "consultant_management_call_rfp_verifier_versions.status")
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $this->id)
            ->where('consultant_management_call_rfp_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_calling_rfp_verifiers.id', '<', $latestVerifierLog->consultant_management_calling_rfp_verifier_id)
            ->orderBy('consultant_management_calling_rfp_verifiers.id', 'desc')
            ->first();
        
        if(!$previousVerifierLog || $previousVerifierLog->status == ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED)
        {
            return true;
        }

        return false;
    }

    public function extendableByUser(User $user)
    {
        $vendorCategoryRfp = $this->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp;

        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        if($user->isConsultantManagementCallingRfpEditor($consultantManagementContract) && $this->consultantManagementRfpRevision->isLatestRevision() &&
        $this->status == self::STATUS_APPROVED &&
        (!$vendorCategoryRfp->approvalDocument || $vendorCategoryRfp->approvalDocument->status == ApprovalDocument::STATUS_DRAFT))
        {
            return true;
        }

        return false;
    }

    public function getCallingRfpDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getClosingRfpDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function isCallingRFpStillOpen()
    {
        $consultantManagementContract = $this->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp->consultantManagementContract;

        $closingRfpDate = new Carbon($this->closing_rfp_date, $consultantManagementContract->timezone);
        $nowDate = Carbon::now($consultantManagementContract->timezone);

        return (!$nowDate->gt($closingRfpDate));
    }

    public static function getPendingReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();

        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;
        //latest first verifier
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, calling_rfp.id AS calling_rfp_id, rfp_rev.revision, calling_rfp_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_calling_rfp_verifiers calling_rfp_v ON calling_rfp_v.consultant_management_calling_rfp_id = calling_rfp.id
        JOIN consultant_management_call_rfp_verifier_versions calling_rfp_vv ON calling_rfp_vv.consultant_management_calling_rfp_verifier_id = calling_rfp_v.id
        INNER JOIN (
            SELECT calling_rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_calling_rfp calling_rfp
            JOIN consultant_management_rfp_revisions rfp_rev ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY calling_rfp.id
        ) max_rfp_revisions ON calling_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
        INNER JOIN (
            SELECT calling_rfp.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_calling_rfp_verifier_id
            FROM consultant_management_call_rfp_verifier_versions vv
            JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
            JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.id = v.consultant_management_calling_rfp_id
            JOIN consultant_management_rfp_revisions rfp_rev ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY calling_rfp.id
        ) first_verifiers ON calling_rfp.id = first_verifiers.id AND calling_rfp_vv.version = first_verifiers.version AND calling_rfp_v.id = first_verifiers.consultant_management_calling_rfp_verifier_id
        WHERE calling_rfp_v.user_id = ".$user->id." AND calling_rfp_vv.status = ".ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING."
        AND calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND calling_rfp_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, calling_rfp.id, rfp_rev.id, calling_rfp_vv.id");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        //next pending verifier inline
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, calling_rfp.id AS calling_rfp_id, rfp_rev.revision, prev_calling_rfp_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_calling_rfp_verifiers calling_rfp_v ON calling_rfp_v.consultant_management_calling_rfp_id = calling_rfp.id
        JOIN consultant_management_call_rfp_verifier_versions calling_rfp_vv ON calling_rfp_vv.consultant_management_calling_rfp_verifier_id = calling_rfp_v.id
        INNER JOIN (
            SELECT calling_rfp.id, MAX(vv.id) AS verifier_version_id
            FROM consultant_management_call_rfp_verifier_versions vv
            JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
            JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.id = v.consultant_management_calling_rfp_id
            JOIN consultant_management_rfp_revisions rfpv ON calling_rfp.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            INNER JOIN (
                SELECT calling_rfp.id, MAX(vv.version) AS version
                FROM consultant_management_call_rfp_verifier_versions vv
                JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
                JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.id = v.consultant_management_calling_rfp_id
                JOIN consultant_management_rfp_revisions rfpv ON calling_rfp.consultant_management_rfp_revision_id = rfpv.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND vv.status = ".ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED."
                AND calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
                GROUP BY calling_rfp.id
            ) max_versions ON max_versions.id = calling_rfp.id AND max_versions.version = vv.version
            WHERE v.user_id <> ".$user->id." AND v.deleted_at IS NULL AND vv.status = ".ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED."
            AND calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY calling_rfp.id
        ) latest_approved_verifier ON latest_approved_verifier.id = calling_rfp.id AND latest_approved_verifier.verifier_version_id = (calling_rfp_vv.id - 1)
        JOIN consultant_management_call_rfp_verifier_versions prev_calling_rfp_vv ON prev_calling_rfp_vv.id = latest_approved_verifier.verifier_version_id
        INNER JOIN (
            SELECT calling_rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_calling_rfp calling_rfp
            JOIN consultant_management_rfp_revisions rfp_rev ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY calling_rfp.id
        ) max_rfp_revisions ON calling_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
        INNER JOIN (
            SELECT calling_rfp.id, MAX(vv.version) AS version
            FROM consultant_management_call_rfp_verifier_versions vv
            JOIN consultant_management_calling_rfp_verifiers v ON v.id = vv.consultant_management_calling_rfp_verifier_id
            JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.id = v.consultant_management_calling_rfp_id
            JOIN consultant_management_rfp_revisions rfp_rev ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.user_id = ".$user->id." AND v.deleted_at IS NULL AND calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY calling_rfp.id
        ) max_versions ON calling_rfp.id = max_versions.id AND calling_rfp_vv.version = max_versions.version AND prev_calling_rfp_vv.version = max_versions.version
        WHERE calling_rfp_v.user_id = ".$user->id." AND calling_rfp_vv.status = ".ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING."
        AND prev_calling_rfp_vv.status = ".ConsultantManagementCallingRfpVerifierVersion::STATUS_APPROVED."
        AND calling_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND calling_rfp_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, calling_rfp.id, rfp_rev.id, prev_calling_rfp_vv.id");

        $stmt->execute();

        $result2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result2 as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result2[$idx]['days_pending'] = $then->diffInDays($now);
        }

        return array_merge($result, $result2);
    }

    public static function getPendingVerificationByCompanyRoleAndContract(Company $company, ConsultantManagementContract $contract, $role)
    {
        $pdo = \DB::getPdo();

        $companyUsers = $contract->getUsersByRoleAndCompany($role, $company);

        $userIds = [];
        $data = [];

        foreach($companyUsers as $type => $users)
        {
            foreach($users as $user)
            {
                $userIds[] = $user['id'];
            }
        }

        if(!empty($userIds))
        {
            $stmt = $pdo->prepare("SELECT calling_rfp.id AS calling_rfp_id, rfp.id AS rfp_id, vc.id AS vendor_category_id, vc.name AS vendor_category_name,
            TO_CHAR(calling_rfp.created_at :: DATE, 'dd/mm/yyyy') AS created_at, ".$company->id." AS company_id
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
            JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
            JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_calling_rfp calling_rfp ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_calling_rfp_verifiers calling_rfp_v ON calling_rfp_v.consultant_management_calling_rfp_id = calling_rfp.id
            JOIN consultant_management_call_rfp_verifier_versions calling_rfp_vv ON calling_rfp_vv.consultant_management_calling_rfp_verifier_id = calling_rfp_v.id
            INNER JOIN (
                SELECT calling_rfp.id, MAX(rfp_rev.revision) AS revision
                FROM consultant_management_calling_rfp calling_rfp
                JOIN consultant_management_rfp_revisions rfp_rev ON calling_rfp.consultant_management_rfp_revision_id = rfp_rev.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE calling_rfp.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
                GROUP BY calling_rfp.id
            ) max_rfp_revisions ON calling_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
            WHERE calling_rfp_v.user_id IN (".implode(',', $userIds).")
            AND calling_rfp_vv.status = ".ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING."
            AND calling_rfp.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
            AND calling_rfp_v.deleted_at IS NULL
            GROUP BY c.id, rfp.id, vc.id, calling_rfp.id");

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }

}