<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifier;
use PCK\ConsultantManagement\ConsultantManagementRfpRevision;

class ConsultantManagementRecommendationOfConsultant extends Model
{
    protected $table = 'consultant_management_recommendation_of_consultants';

    protected $fillable = ['vendor_category_rfp_id', 'proposed_fee', 'calling_rfp_proposed_date', 'closing_rfp_proposed_date', 'remarks', 'status'];

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
            $model->status = ConsultantManagementRecommendationOfConsultant::STATUS_DRAFT;
        });

        self::saved(function(self $model)
        {
            if($model->status == ConsultantManagementRecommendationOfConsultant::STATUS_APPROVED && !$model->consultantManagementVendorCategoryRfp->revisions()->count())
            {
                $user = \Confide::user();

                $rfpRevision = new ConsultantManagementRfpRevision;

                $rfpRevision->vendor_category_rfp_id = $model->vendor_category_rfp_id;
                $rfpRevision->created_by = $user->id;
                $rfpRevision->updated_by = $user->id;
                $rfpRevision->created_at = date('Y-m-d H:i:s');
                $rfpRevision->updated_at = date('Y-m-d H:i:s');

                $rfpRevision->save();
            }
        });
        
        self::deleting(function(self $model)
        {
            /* 
             * need to hard delete roc verifiers data in here because
             * roc verifiers and roc verifier versions are soft delete (log purposes)
             */
            foreach($model->verifiers as $verifier)
            {
                $verifier->version()->forceDelete();
                $verifier->forceDelete();
            }
        });
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function verifiers()
    {
        return $this->hasMany(ConsultantManagementRecommendationOfConsultantVerifier::class, 'consultant_management_recommendation_of_consultant_id');
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
        $consultantManagementContract = $this->consultantManagementVendorCategoryRfp->consultantManagementContract;
        if($this->status == self::STATUS_DRAFT && $user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT))
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

        $latestVersion = ConsultantManagementRecommendationOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $this->id)
            ->groupBy('consultant_management_recommendation_of_consultants.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }

        $latestVerifierLog = ConsultantManagementRecommendationOfConsultantVerifierVersion::select("consultant_management_roc_verifier_versions.id AS id", "consultant_management_roc_verifier_versions.status AS status", "consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id")
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $this->id)
            ->where('consultant_management_roc_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_recommendation_of_consultant_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        $previousVerifierLog = ConsultantManagementRecommendationOfConsultantVerifierVersion::select("consultant_management_recommendation_of_consultant_verifiers.id AS id", "consultant_management_roc_verifier_versions.status")
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $this->id)
            ->where('consultant_management_roc_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_recommendation_of_consultant_verifiers.id', '<', $latestVerifierLog->consultant_management_recommendation_of_consultant_verifier_id)
            ->orderBy('consultant_management_recommendation_of_consultant_verifiers.id', 'desc')
            ->first();
        
        if(!$previousVerifierLog || $previousVerifierLog->status == ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED)
        {
            return true;
        }

        return false;
    }

    public function getCallingRfpProposedDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getClosingRfpProposedDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public static function getPendingReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();

        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;
        //latest first verifier
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, cmroc.id AS roc_id, cmrocvv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_recommendation_of_consultants cmroc ON cmroc.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_recommendation_of_consultant_verifiers cmrocv ON cmrocv.consultant_management_recommendation_of_consultant_id = cmroc.id
        JOIN consultant_management_roc_verifier_versions cmrocvv ON cmrocvv.consultant_management_recommendation_of_consultant_verifier_id = cmrocv.id
        INNER JOIN (
            SELECT roc.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_recommendation_of_consultant_verifier_id
            FROM consultant_management_roc_verifier_versions vv
            JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
            JOIN consultant_management_recommendation_of_consultants roc ON roc.id = v.consultant_management_recommendation_of_consultant_id
            JOIN consultant_management_vendor_categories_rfp rfp ON roc.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND roc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY roc.id
        ) first_verifiers ON cmroc.id = first_verifiers.id AND cmrocvv.version = first_verifiers.version AND cmrocv.id = first_verifiers.consultant_management_recommendation_of_consultant_verifier_id
        WHERE cmrocv.user_id = ".$user->id." AND cmrocvv.status = ".ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING."
        AND cmroc.status = ". self::STATUS_APPROVAL."
        ".$contractSql."
        AND cmrocv.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, cmroc.id, cmrocvv.id
        ");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        //next pending verifier inline
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, cmroc.id AS roc_id, prev_cmrocvv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_recommendation_of_consultants cmroc ON cmroc.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_recommendation_of_consultant_verifiers cmrocv ON cmrocv.consultant_management_recommendation_of_consultant_id = cmroc.id
        JOIN consultant_management_roc_verifier_versions cmrocvv ON cmrocvv.consultant_management_recommendation_of_consultant_verifier_id = cmrocv.id
        INNER JOIN (
            SELECT roc.id, MAX(vv.id) AS verifier_version_id
            FROM consultant_management_roc_verifier_versions vv
            JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
            JOIN consultant_management_recommendation_of_consultants roc ON roc.id = v.consultant_management_recommendation_of_consultant_id
            JOIN consultant_management_vendor_categories_rfp rfp ON roc.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            INNER JOIN (
                SELECT roc.id, MAX(vv.version) AS version
                FROM consultant_management_roc_verifier_versions vv
                JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
                JOIN consultant_management_recommendation_of_consultants roc ON roc.id = v.consultant_management_recommendation_of_consultant_id
                JOIN consultant_management_vendor_categories_rfp rfp ON roc.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND vv.status = ".ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED."
                AND roc.status = ". self::STATUS_APPROVAL." ".$contractSql."
                GROUP BY roc.id
            ) max_versions ON max_versions.id = roc.id AND max_versions.version = vv.version
            WHERE v.user_id <> ".$user->id." AND v.deleted_at IS NULL AND vv.status = ".ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED."
            AND roc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY roc.id
        ) latest_approved_verifier ON latest_approved_verifier.id = cmroc.id AND latest_approved_verifier.verifier_version_id = (cmrocvv.id - 1)
        JOIN consultant_management_roc_verifier_versions prev_cmrocvv ON prev_cmrocvv.id = latest_approved_verifier.verifier_version_id
        INNER JOIN (
            SELECT roc.id, MAX(vv.version) AS version
            FROM consultant_management_roc_verifier_versions vv
            JOIN consultant_management_recommendation_of_consultant_verifiers v ON v.id = vv.consultant_management_recommendation_of_consultant_verifier_id
            JOIN consultant_management_recommendation_of_consultants roc ON roc.id = v.consultant_management_recommendation_of_consultant_id
            JOIN consultant_management_vendor_categories_rfp rfp ON roc.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND roc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY roc.id
        ) max_versions ON cmroc.id = max_versions.id AND cmrocvv.version = max_versions.version AND prev_cmrocvv.version = max_versions.version
        WHERE cmrocv.user_id = ".$user->id." AND cmrocvv.status = ".ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING."
        AND prev_cmrocvv.status = ".ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_APPROVED."
        AND cmroc.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND cmrocv.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, cmroc.id, prev_cmrocvv.id");

        $stmt->execute();

        $result2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result2 as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result2[$idx]['days_pending'] = $then->diffInDays($now);
        }

        return array_merge($result, $result2);
    }
}