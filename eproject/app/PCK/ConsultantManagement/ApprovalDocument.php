<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ApprovalDocumentVerifier;
use PCK\ConsultantManagement\ApprovalDocumentVerifierVersion;
use PCK\ConsultantManagement\ApprovalDocumentSectionA;
use PCK\ConsultantManagement\ApprovalDocumentSectionB;
use PCK\ConsultantManagement\ApprovalDocumentSectionC;
use PCK\ConsultantManagement\ApprovalDocumentSectionD;
use PCK\ConsultantManagement\ApprovalDocumentSectionE;
use PCK\ConsultantManagement\ApprovalDocumentSectionAppendix;
use PCK\ConsultantManagement\LetterOfAward;

use PCK\Users\User;
use PCK\Companies\Company;

class ApprovalDocument extends Model
{
    protected $table = 'consultant_management_approval_documents';

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
            $model->status = ApprovalDocument::STATUS_DRAFT;
        });

        self::created(function(self $model)
        {
            $sectionA = new ApprovalDocumentSectionA;
            $sectionA->consultant_management_approval_document_id = $model->id;
            $sectionA->approving_authority = ApprovalDocumentSectionA::APPROVING_AUTHORITY_EMPTY;

            $sectionB = new ApprovalDocumentSectionB;
            $sectionB->consultant_management_approval_document_id = $model->id;

            $sectionC = new ApprovalDocumentSectionC;
            $sectionC->consultant_management_approval_document_id = $model->id;

            $sectionD = new ApprovalDocumentSectionD;
            $sectionD->consultant_management_approval_document_id = $model->id;

            $sectionE = new ApprovalDocumentSectionE;
            $sectionE->consultant_management_approval_document_id = $model->id;

            $sectionAppendix = new ApprovalDocumentSectionAppendix;
            $sectionAppendix->consultant_management_approval_document_id = $model->id;

            $user = \Confide::user();

            if($user)
            {
                $sectionA->created_by = $user->id;
                $sectionA->updated_by = $user->id;

                $sectionB->created_by = $user->id;
                $sectionB->updated_by = $user->id;

                $sectionC->created_by = $user->id;
                $sectionC->updated_by = $user->id;

                $sectionD->created_by = $user->id;
                $sectionD->updated_by = $user->id;

                $sectionE->created_by = $user->id;
                $sectionE->updated_by = $user->id;

                $sectionAppendix->created_by = $user->id;
                $sectionAppendix->updated_by = $user->id;
            }

            $sectionA->save();
            $sectionB->save();
            $sectionC->save();
            $sectionD->save();
            $sectionE->save();
            $sectionAppendix->save();
        });

        self::saving(function(self $model)
        {
            $model->document_reference_no = mb_strtoupper($model->document_reference_no);
        });

        self::saved(function(self $model)
        {
            $user = \Confide::user();

            if($model->status == ApprovalDocument::STATUS_APPROVED && $user && !$model->consultantManagementVendorCategoryRfp->letterOfAward)
            {
                $letterOfAward = new LetterOfAward;

                $letterOfAward->vendor_category_rfp_id = $model->vendor_category_rfp_id;
                $letterOfAward->status = LetterOfAward::STATUS_DRAFT;
                $letterOfAward->created_by = $user->id;
                $letterOfAward->updated_by = $user->id;

                $letterOfAward->save();
            }
        });
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function sectionA()
    {
        return $this->hasOne(ApprovalDocumentSectionA::class, 'consultant_management_approval_document_id');
    }

    public function sectionB()
    {
        return $this->hasOne(ApprovalDocumentSectionB::class, 'consultant_management_approval_document_id');
    }

    public function sectionC()
    {
        return $this->hasOne(ApprovalDocumentSectionC::class, 'consultant_management_approval_document_id');
    }

    public function sectionD()
    {
        return $this->hasOne(ApprovalDocumentSectionD::class, 'consultant_management_approval_document_id');
    }

    public function sectionE()
    {
        return $this->hasOne(ApprovalDocumentSectionE::class, 'consultant_management_approval_document_id');
    }

    public function sectionAppendix()
    {
        return $this->hasOne(ApprovalDocumentSectionAppendix::class, 'consultant_management_approval_document_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function needApprovalFromUser(User $user)
    {
        if($this->status != self::STATUS_APPROVAL)
        {
            return false;
        }

        $latestVersion = ApprovalDocumentVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $this->id)
            ->groupBy('consultant_management_approval_documents.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }

        $latestVerifierLog = ApprovalDocumentVerifierVersion::select("consultant_management_approval_document_verifier_versions.id AS id", "consultant_management_approval_document_verifier_versions.status AS status", "consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id")
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $this->id)
            ->where('consultant_management_approval_document_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_approval_document_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == ApprovalDocumentVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        $previousVerifierLog = ApprovalDocumentVerifierVersion::select("consultant_management_approval_document_verifiers.id AS id", "consultant_management_approval_document_verifier_versions.status")
            ->join('consultant_management_approval_document_verifiers', 'consultant_management_approval_document_verifiers.id', '=', 'consultant_management_approval_document_verifier_versions.consultant_management_approval_document_verifier_id')
            ->join('consultant_management_approval_documents', 'consultant_management_approval_documents.id', '=', 'consultant_management_approval_document_verifiers.consultant_management_approval_document_id')
            ->where('consultant_management_approval_documents.id', '=', $this->id)
            ->where('consultant_management_approval_document_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_approval_document_verifiers.id', '<', $latestVerifierLog->consultant_management_approval_document_verifier_id)
            ->orderBy('consultant_management_approval_document_verifiers.id', 'desc')
            ->first();
        
        if(!$previousVerifierLog || $previousVerifierLog->status == ApprovalDocumentVerifierVersion::STATUS_APPROVED)
        {
            return true;
        }

        return false;
    }

    public static function getPendingReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();
        
        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;
        //latest first verifier
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, approval_document.id AS approval_document_id, approval_document_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_approval_documents approval_document ON approval_document.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_approval_document_verifiers approval_document_v ON approval_document_v.consultant_management_approval_document_id = approval_document.id
        JOIN consultant_management_approval_document_verifier_versions approval_document_vv ON approval_document_vv.consultant_management_approval_document_verifier_id = approval_document_v.id
        INNER JOIN (
            SELECT approval_document.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_approval_document_verifier_id
            FROM consultant_management_approval_document_verifier_versions vv
            JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
            JOIN consultant_management_approval_documents approval_document ON approval_document.id = v.consultant_management_approval_document_id
            JOIN consultant_management_vendor_categories_rfp rfp ON approval_document.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND approval_document.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY approval_document.id
        ) first_verifiers ON approval_document.id = first_verifiers.id AND approval_document_vv.version = first_verifiers.version AND approval_document_v.id = first_verifiers.consultant_management_approval_document_verifier_id
        WHERE approval_document_v.user_id = ".$user->id." AND approval_document_vv.status = ".ApprovalDocumentVerifierVersion::STATUS_PENDING."
        AND approval_document.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND approval_document_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, approval_document.id, approval_document_vv.id");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        //next pending verifier inline
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, approval_document.id AS approval_document_id, prev_approval_document_vv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_approval_documents approval_document ON approval_document.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_approval_document_verifiers approval_document_v ON approval_document_v.consultant_management_approval_document_id = approval_document.id
        JOIN consultant_management_approval_document_verifier_versions approval_document_vv ON approval_document_vv.consultant_management_approval_document_verifier_id = approval_document_v.id
        INNER JOIN (
            SELECT approval_document.id, MAX(vv.id) AS verifier_version_id
            FROM consultant_management_approval_document_verifier_versions vv
            JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
            JOIN consultant_management_approval_documents approval_document ON approval_document.id = v.consultant_management_approval_document_id
            JOIN consultant_management_vendor_categories_rfp rfp ON approval_document.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            INNER JOIN (
                SELECT approval_document.id, MAX(vv.version) AS version
                FROM consultant_management_approval_document_verifier_versions vv
                JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
                JOIN consultant_management_approval_documents approval_document ON approval_document.id = v.consultant_management_approval_document_id
                JOIN consultant_management_vendor_categories_rfp rfp ON approval_document.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND vv.status = ".ApprovalDocumentVerifierVersion::STATUS_APPROVED."
                AND approval_document.status = ". self::STATUS_APPROVAL." ".$contractSql."
                GROUP BY approval_document.id
            ) max_versions ON max_versions.id = approval_document.id AND max_versions.version = vv.version
            WHERE v.user_id <> ".$user->id." AND v.deleted_at IS NULL AND vv.status = ".ApprovalDocumentVerifierVersion::STATUS_APPROVED."
            AND approval_document.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY approval_document.id
        ) latest_approved_verifier ON latest_approved_verifier.id = approval_document.id AND latest_approved_verifier.verifier_version_id = (approval_document_vv.id - 1)
        JOIN consultant_management_approval_document_verifier_versions prev_approval_document_vv ON prev_approval_document_vv.id = latest_approved_verifier.verifier_version_id
        INNER JOIN (
            SELECT approval_document.id, MAX(vv.version) AS version
            FROM consultant_management_approval_document_verifier_versions vv
            JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
            JOIN consultant_management_approval_documents approval_document ON approval_document.id = v.consultant_management_approval_document_id
            JOIN consultant_management_vendor_categories_rfp rfp ON approval_document.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND approval_document.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY approval_document.id
        ) max_versions ON approval_document.id = max_versions.id AND approval_document_vv.version = max_versions.version AND prev_approval_document_vv.version = max_versions.version
        WHERE approval_document_v.user_id = ".$user->id." AND approval_document_vv.status = ".ApprovalDocumentVerifierVersion::STATUS_PENDING."
        AND prev_approval_document_vv.status = ".ApprovalDocumentVerifierVersion::STATUS_APPROVED."
        AND approval_document.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND approval_document_v.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, approval_document.id, prev_approval_document_vv.id");

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
            $stmt = $pdo->prepare("SELECT approval_document.id AS approval_document_id, rfp.id AS rfp_id, vc.id AS vendor_category_id, vc.name AS vendor_category_name,
            TO_CHAR(approval_document.created_at :: DATE, 'dd/mm/yyyy') AS created_at, ".$company->id." AS company_id
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
            JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
            JOIN consultant_management_approval_documents approval_document ON approval_document.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_approval_document_verifiers approval_document_v ON approval_document_v.consultant_management_approval_document_id = approval_document.id
            JOIN consultant_management_approval_document_verifier_versions approval_document_vv ON approval_document_vv.consultant_management_approval_document_verifier_id = approval_document_v.id
            INNER JOIN (
                SELECT approval_document.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_approval_document_verifier_id
                FROM consultant_management_approval_document_verifier_versions vv
                JOIN consultant_management_approval_document_verifiers v ON v.id = vv.consultant_management_approval_document_verifier_id
                JOIN consultant_management_approval_documents approval_document ON approval_document.id = v.consultant_management_approval_document_id
                JOIN consultant_management_vendor_categories_rfp rfp ON approval_document.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND approval_document.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
                GROUP BY approval_document.id
            ) first_verifiers ON approval_document.id = first_verifiers.id AND approval_document_vv.version = first_verifiers.version AND approval_document_v.id = first_verifiers.consultant_management_approval_document_verifier_id
            WHERE approval_document_v.user_id IN (".implode(',', $userIds).")
            AND approval_document_vv.status = ".ApprovalDocumentVerifierVersion::STATUS_PENDING."
            AND approval_document.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
            AND approval_document_v.deleted_at IS NULL
            GROUP BY c.id, rfp.id, vc.id, approval_document.id");

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public static function hasInProgressRecords(User $user)
    {
        $query = "WITH app_doc_pending_verifiers_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY ad.id ORDER BY vv.version DESC) AS rank, ad.id, vv.user_id
                      FROM consultant_management_approval_documents ad
                      INNER JOIN consultant_management_approval_document_verifiers v ON v.consultant_management_approval_document_id = ad.id
                      INNER JOIN consultant_management_approval_document_verifier_versions vv ON vv.consultant_management_approval_document_verifier_id = v.id
                      WHERE v.deleted_at IS NULL
                      AND ad.status = " . self::STATUS_APPROVAL . "
                      AND vv.status = " . ApprovalDocumentVerifierVersion::STATUS_PENDING . "
                      AND v.user_id = " . $user->id . "
                  )
                  SELECT * 
                  FROM app_doc_pending_verifiers_cte
                  WHERE rank = 1";

        $pdo  = \DB::getPdo();
        $stmt = $pdo->prepare($query);

        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return (count($data) > 0);
    }
}