<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifier;
use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementRfpResubmissionVerifier;
use PCK\ConsultantManagement\ConsultantManagementRfpResubmissionVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementRfpRevision;
use PCK\Users\User;
use PCK\Companies\Company;

class ConsultantManagementOpenRfp extends Model
{
    protected $table = 'consultant_management_open_rfp';

    protected $fillable = ['consultant_management_rfp_revision_id', 'status'];

    const STATUS_DRAFT = 1;
    const STATUS_APPROVAL = 2;
    const STATUS_APPROVED = 4;
    const STATUS_RESUBMISSION_APPROVAL = 8;
    const STATUS_RESUBMISSION_APPROVED = 16;

    const STATUS_DRAFT_TEXT = 'Draft';
    const STATUS_APPROVAL_TEXT = 'Pending Approval';
    const STATUS_APPROVED_TEXT = 'Approved';
    const STATUS_RESUBMISSION_APPROVAL_TEXT = 'Pending Resubmission Approval';
    const STATUS_RESUBMISSION_APPROVED_TEXT = 'Resubmitted';

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $model)
        {
            $model->status = ConsultantManagementOpenRfp::STATUS_DRAFT;
        });
    }

    public function consultantManagementRfpRevision()
    {
        return $this->belongsTo(ConsultantManagementRfpRevision::class, 'consultant_management_rfp_revision_id');
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
            case self::STATUS_RESUBMISSION_APPROVAL:
                return self::STATUS_RESUBMISSION_APPROVAL_TEXT;
            case self::STATUS_RESUBMISSION_APPROVED:
                return self::STATUS_RESUBMISSION_APPROVED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function needApprovalFromUser(User $user)
    {
        if($this->status != self::STATUS_APPROVAL)
        {
            return false;
        }

        $latestVersion = ConsultantManagementOpenRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $this->id)
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }

        $latestVerifierLog = ConsultantManagementOpenRfpVerifierVersion::select("consultant_management_open_rfp_verifier_versions.id AS id", "consultant_management_open_rfp_verifier_versions.status AS status", "consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id")
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $this->id)
            ->where('consultant_management_open_rfp_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_open_rfp_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == ConsultantManagementOpenRfpVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        return true;
    }

    public function needResubmissionApprovalFromUser(User $user)
    {
        if($this->status != self::STATUS_RESUBMISSION_APPROVAL)
        {
            return false;
        }

        $latestVersion = ConsultantManagementRfpResubmissionVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $this->id)
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }
        
        $latestVerifierLog = ConsultantManagementRfpResubmissionVerifierVersion::select("consultant_management_rfp_resubmission_verifier_versions.id AS id", "consultant_management_rfp_resubmission_verifier_versions.status AS status", "consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id")
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $this->id)
            ->where('consultant_management_rfp_resubmission_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_rfp_resubmission_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        $previousVerifierLog = ConsultantManagementRfpResubmissionVerifierVersion::select("consultant_management_rfp_resubmission_verifiers.id AS id", "consultant_management_rfp_resubmission_verifier_versions.status")
            ->join('consultant_management_rfp_resubmission_verifiers', 'consultant_management_rfp_resubmission_verifiers.id', '=', 'consultant_management_rfp_resubmission_verifier_versions.consultant_management_rfp_resubmission_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_rfp_resubmission_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $this->id)
            ->where('consultant_management_rfp_resubmission_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_rfp_resubmission_verifiers.id', '<', $latestVerifierLog->consultant_management_rfp_resubmission_verifier_id)
            ->orderBy('consultant_management_rfp_resubmission_verifiers.id', 'desc')
            ->first();
        
        if(!$previousVerifierLog || $previousVerifierLog->status == ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED)
        {
            return true;
        }

        return false;
    }

    public function shortlistedCompanies()
    {
        return Company::select("companies.*")
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'companies.id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_open_rfp.consultant_management_rfp_revision_id')
        ->leftJoin('consultant_management_consultant_rfp', function($join){
            $join->on('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id');
            $join->on('consultant_management_consultant_rfp.company_id','=', 'companies.id');
        })
        ->leftJoin('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->where('consultant_management_open_rfp.id', '=', $this->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->groupBy('companies.id');
    }

    public static function getPendingReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();

        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;

        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, open_rfp.id AS open_rfp_id, rfp_rev.revision, open_rfp_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_open_rfp_verifiers open_rfp_v ON open_rfp_v.consultant_management_open_rfp_id = open_rfp.id
        JOIN consultant_management_open_rfp_verifier_versions open_rfp_vv ON open_rfp_vv.consultant_management_open_rfp_verifier_id = open_rfp_v.id
        INNER JOIN (
            SELECT open_rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_open_rfp open_rfp
            JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE open_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) max_rfp_revisions ON open_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
        INNER JOIN (
            SELECT open_rfp.id, MAX(vv.version) AS version
            FROM consultant_management_open_rfp_verifier_versions vv
            JOIN consultant_management_open_rfp_verifiers v ON v.id = vv.consultant_management_open_rfp_verifier_id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.id = v.consultant_management_open_rfp_id
            JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND open_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) first_verifiers ON open_rfp.id = first_verifiers.id AND open_rfp_vv.version = first_verifiers.version
        WHERE open_rfp_v.user_id = ".$user->id." AND open_rfp_vv.status = ".ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING."
        AND open_rfp.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND open_rfp_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, open_rfp.id, rfp_rev.id, open_rfp_vv.id");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        return $result;
    }

    public static function getPendingResubmissionReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();

        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;

        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, open_rfp.id AS open_rfp_id, rfp_rev.revision, resubmission_rfp_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_rfp_resubmission_verifiers resubmission_rfp_v ON resubmission_rfp_v.consultant_management_open_rfp_id = open_rfp.id
        JOIN consultant_management_rfp_resubmission_verifier_versions resubmission_rfp_vv ON resubmission_rfp_vv.consultant_management_rfp_resubmission_verifier_id = resubmission_rfp_v.id
        INNER JOIN (
            SELECT open_rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_open_rfp open_rfp
            JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) max_rfp_revisions ON open_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
        INNER JOIN (
            SELECT open_rfp.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_rfp_resubmission_verifier_id
            FROM consultant_management_rfp_resubmission_verifier_versions vv
            JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.id = v.consultant_management_open_rfp_id
            JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) first_verifiers ON open_rfp.id = first_verifiers.id AND resubmission_rfp_vv.version = first_verifiers.version AND resubmission_rfp_v.id = first_verifiers.consultant_management_rfp_resubmission_verifier_id
        WHERE resubmission_rfp_v.user_id = ".$user->id." AND resubmission_rfp_vv.status = ".ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING."
        AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
        AND resubmission_rfp_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, open_rfp.id, rfp_rev.id, resubmission_rfp_vv.id");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        //next pending verifier inline
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, open_rfp.id AS open_rfp_id, rfp_rev.revision, prev_resubmission_rfp_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_rfp_resubmission_verifiers resubmission_rfp_v ON resubmission_rfp_v.consultant_management_open_rfp_id = open_rfp.id
        JOIN consultant_management_rfp_resubmission_verifier_versions resubmission_rfp_vv ON resubmission_rfp_vv.consultant_management_rfp_resubmission_verifier_id = resubmission_rfp_v.id
        INNER JOIN (
            SELECT open_rfp.id, MAX(vv.id) AS verifier_version_id
            FROM consultant_management_rfp_resubmission_verifier_versions vv
            JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.id = v.consultant_management_open_rfp_id
            JOIN consultant_management_rfp_revisions rfpv ON open_rfp.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            INNER JOIN (
                SELECT open_rfp.id, MAX(vv.version) AS version
                FROM consultant_management_rfp_resubmission_verifier_versions vv
                JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
                JOIN consultant_management_open_rfp open_rfp ON open_rfp.id = v.consultant_management_open_rfp_id
                JOIN consultant_management_rfp_revisions rfpv ON open_rfp.consultant_management_rfp_revision_id = rfpv.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND vv.status = ".ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED."
                AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
                GROUP BY open_rfp.id
            ) max_versions ON max_versions.id = open_rfp.id AND max_versions.version = vv.version
            WHERE v.user_id <> ".$user->id." AND v.deleted_at IS NULL AND vv.status = ".ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED."
            AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) latest_approved_verifier ON latest_approved_verifier.id = open_rfp.id AND latest_approved_verifier.verifier_version_id = (resubmission_rfp_vv.id - 1)
        JOIN consultant_management_rfp_resubmission_verifier_versions prev_resubmission_rfp_vv ON prev_resubmission_rfp_vv.id = latest_approved_verifier.verifier_version_id
        INNER JOIN (
            SELECT open_rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_open_rfp open_rfp
            JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) max_rfp_revisions ON open_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
        INNER JOIN (
            SELECT open_rfp.id, MAX(vv.version) AS version
            FROM consultant_management_rfp_resubmission_verifier_versions vv
            JOIN consultant_management_rfp_resubmission_verifiers v ON v.id = vv.consultant_management_rfp_resubmission_verifier_id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.id = v.consultant_management_open_rfp_id
            JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
            GROUP BY open_rfp.id
        ) max_versions ON open_rfp.id = max_versions.id AND resubmission_rfp_vv.version = max_versions.version AND prev_resubmission_rfp_vv.version = max_versions.version
        WHERE resubmission_rfp_v.user_id = ".$user->id." AND resubmission_rfp_vv.status = ".ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING."
        AND prev_resubmission_rfp_vv.status = ".ConsultantManagementRfpResubmissionVerifierVersion::STATUS_APPROVED."
        AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." ".$contractSql."
        AND resubmission_rfp_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, open_rfp.id, rfp_rev.id, prev_resubmission_rfp_vv.id");

        $stmt->execute();

        $result2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result2 as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result2[$idx]['days_pending'] = $then->diffInDays($now);
        }

        return array_merge($result, $result2);
    }

    public static function getPendingOpenRfpVerificationByCompanyRoleAndContract(Company $company, ConsultantManagementContract $contract, $role)
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
            

            $stmt = $pdo->prepare("SELECT open_rfp.id AS open_rfp_id, rfp.id AS rfp_id, vc.id AS vendor_category_id, vc.name AS vendor_category_name,
            TO_CHAR(open_rfp.created_at :: DATE, 'dd/mm/yyyy') AS created_at, ".$company->id." AS company_id
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
            JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
            JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_open_rfp_verifiers open_rfp_v ON open_rfp_v.consultant_management_open_rfp_id = open_rfp.id
            JOIN consultant_management_open_rfp_verifier_versions open_rfp_vv ON open_rfp_vv.consultant_management_open_rfp_verifier_id = open_rfp_v.id
            INNER JOIN (
                SELECT open_rfp.id, MAX(rfp_rev.revision) AS revision
                FROM consultant_management_open_rfp open_rfp
                JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE open_rfp.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
                GROUP BY open_rfp.id
            ) max_rfp_revisions ON open_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
            WHERE open_rfp_v.user_id IN (".implode(',', $userIds).") AND open_rfp_vv.status = ".ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING."
            AND open_rfp.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
            AND open_rfp_v.deleted_at IS NULL
            GROUP BY c.id, rfp.id, vc.id, open_rfp.id");

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public static function getPendingResubmissionRfpVerificationByCompanyRoleAndContract(Company $company, ConsultantManagementContract $contract, $role)
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
            $stmt = $pdo->prepare("SELECT open_rfp.id AS open_rfp_id, rfp.id AS rfp_id, vc.id AS vendor_category_id, vc.name AS vendor_category_name,
            TO_CHAR(open_rfp.created_at :: DATE, 'dd/mm/yyyy') AS created_at, ".$company->id." AS company_id
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
            JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
            JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            JOIN consultant_management_rfp_resubmission_verifiers resubmission_rfp_v ON resubmission_rfp_v.consultant_management_open_rfp_id = open_rfp.id
            JOIN consultant_management_rfp_resubmission_verifier_versions resubmission_rfp_vv ON resubmission_rfp_vv.consultant_management_rfp_resubmission_verifier_id = resubmission_rfp_v.id
            INNER JOIN (
                SELECT open_rfp.id, MAX(rfp_rev.revision) AS revision
                FROM consultant_management_open_rfp open_rfp
                JOIN consultant_management_rfp_revisions rfp_rev ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfp_rev.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." AND c.id = ".$contract->id."
                GROUP BY open_rfp.id
            ) max_rfp_revisions ON open_rfp.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfp_rev.revision
            WHERE resubmission_rfp_v.user_id IN (".implode(',', $userIds).")
            AND resubmission_rfp_vv.status = ".ConsultantManagementRfpResubmissionVerifierVersion::STATUS_PENDING."
            AND open_rfp.status = ". self::STATUS_RESUBMISSION_APPROVAL." AND c.id = ".$contract->id."
            AND resubmission_rfp_v.deleted_at IS NULL
            GROUP BY c.id, rfp.id, vc.id, open_rfp.id");

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }
}