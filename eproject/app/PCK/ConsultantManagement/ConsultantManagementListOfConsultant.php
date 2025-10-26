<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantCompany;
use PCK\ConsultantManagement\ConsultantManagementRfpRevision;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;

class ConsultantManagementListOfConsultant extends Model
{
    protected $table = 'consultant_management_list_of_consultants';

    protected $fillable = ['consultant_management_rfp_revision_id', 'proposed_fee', 'calling_rfp_date', 'closing_rfp_date', 'remarks', 'status'];

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
            $model->status = ConsultantManagementListOfConsultant::STATUS_DRAFT;
        });

        self::created(function(self $model)
        {
            $user = \Confide::user();
            $rev  = $model->consultantManagementRfpRevision;

            $consultantData = [];

            if($rev->revision > 1)
            {
                $prevRevision = ConsultantManagementRfpRevision::where('vendor_category_rfp_id', '=', $rev->vendor_category_rfp_id)
                ->where('revision', '=', ($rev->revision - 1 ))
                ->first();

                $consultants = $prevRevision->listOfConsultant->consultants;

                foreach($consultants as $consultant)
                {
                    $consultantData[] = [
                        'consultant_management_list_of_consultant_id' => $model->id,
                        'company_id' => $consultant->company_id,
                        'status'     => $consultant->status,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
            else
            {
                $consultants = $rev->consultantManagementVendorCategoryRfp->recommendationOfConsultantCompanies;

                foreach($consultants as $consultant)
                {
                    $consultantData[] = [
                        'consultant_management_list_of_consultant_id' => $model->id,
                        'company_id' => $consultant->company_id,
                        'status'     => $consultant->status,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }

            if(!empty($consultantData))
            {
                ConsultantManagementListOfConsultantCompany::insert($consultantData);
            }
        });

        self::saved(function(self $model)
        {
            $rfpRevision = $model->consultantManagementRfpRevision;

            if($model->status == ConsultantManagementListOfConsultant::STATUS_APPROVED && $rfpRevision && !$rfpRevision->callingRfp)
            {
                $user = \Confide::user();

                $callingRfp = new ConsultantManagementCallingRfp;

                $callingRfp->consultant_management_rfp_revision_id = $model->consultant_management_rfp_revision_id;
                $callingRfp->calling_rfp_date = $model->calling_rfp_date;
                $callingRfp->closing_rfp_date = $model->closing_rfp_date;
                $callingRfp->created_by = $user->id;
                $callingRfp->updated_by = $user->id;
                $callingRfp->created_at = date('Y-m-d H:i:s');
                $callingRfp->updated_at = date('Y-m-d H:i:s');

                $callingRfp->save();
            }
        });
    }

    public function consultantManagementRfpRevision()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementRfpRevision', 'consultant_management_rfp_revision_id');
    }

    public function consultants()
    {
        return $this->hasMany('PCK\ConsultantManagement\ConsultantManagementListOfConsultantCompany', 'consultant_management_list_of_consultant_id');
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
        if($this->status == self::STATUS_DRAFT && $user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
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

        $latestVersion = ConsultantManagementListOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $this->id)
            ->groupBy('consultant_management_list_of_consultants.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }

        $latestVerifierLog = ConsultantManagementListOfConsultantVerifierVersion::select("consultant_management_loc_verifier_versions.id AS id", "consultant_management_loc_verifier_versions.status AS status", "consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id")
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $this->id)
            ->where('consultant_management_loc_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_list_of_consultant_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        $previousVerifierLog = ConsultantManagementListOfConsultantVerifierVersion::select("consultant_management_list_of_consultant_verifiers.id AS id", "consultant_management_loc_verifier_versions.status")
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $this->id)
            ->where('consultant_management_loc_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_list_of_consultant_verifiers.id', '<', $latestVerifierLog->consultant_management_list_of_consultant_verifier_id)
            ->orderBy('consultant_management_list_of_consultant_verifiers.id', 'desc')
            ->first();
        
        if(!$previousVerifierLog || $previousVerifierLog->status == ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED)
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

    public static function getPendingReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();

        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;
        //latest first verifier
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, cmloc.id AS loc_id, rfpv.revision, cmlocvv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfpv ON rfpv.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_list_of_consultants cmloc ON cmloc.consultant_management_rfp_revision_id = rfpv.id
        JOIN consultant_management_list_of_consultant_verifiers cmlocv ON cmlocv.consultant_management_list_of_consultant_id = cmloc.id
        JOIN consultant_management_loc_verifier_versions cmlocvv ON cmlocvv.consultant_management_list_of_consultant_verifier_id = cmlocv.id
        INNER JOIN (
            SELECT loc.id, MAX(rfpv.revision) AS revision
            FROM consultant_management_list_of_consultants loc
            JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE loc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loc.id
        ) max_rfp_revisions ON cmloc.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfpv.revision
        INNER JOIN (
            SELECT loc.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_list_of_consultant_verifier_id
            FROM consultant_management_loc_verifier_versions vv
            JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
            JOIN consultant_management_list_of_consultants loc ON loc.id = v.consultant_management_list_of_consultant_id
            JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND loc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loc.id
        ) first_verifiers ON cmloc.id = first_verifiers.id AND cmlocvv.version = first_verifiers.version AND cmlocv.id = first_verifiers.consultant_management_list_of_consultant_verifier_id
        WHERE cmlocv.user_id = ".$user->id." AND cmlocvv.status = ".ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING."
        AND cmloc.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND cmlocv.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, cmloc.id, rfpv.id, cmlocvv.id");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        //next pending verifier inline
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, cmloc.id AS loc_id, rfpv.revision, prev_cmlocvv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_rfp_revisions rfpv ON rfpv.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_list_of_consultants cmloc ON cmloc.consultant_management_rfp_revision_id = rfpv.id
        JOIN consultant_management_list_of_consultant_verifiers cmlocv ON cmlocv.consultant_management_list_of_consultant_id = cmloc.id
        JOIN consultant_management_loc_verifier_versions cmlocvv ON cmlocvv.consultant_management_list_of_consultant_verifier_id = cmlocv.id
        INNER JOIN (
            SELECT loc.id, MAX(vv.id) AS verifier_version_id
            FROM consultant_management_loc_verifier_versions vv
            JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
            JOIN consultant_management_list_of_consultants loc ON loc.id = v.consultant_management_list_of_consultant_id
            JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            INNER JOIN (
                SELECT loc.id, MAX(vv.version) AS version
                FROM consultant_management_loc_verifier_versions vv
                JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
                JOIN consultant_management_list_of_consultants loc ON loc.id = v.consultant_management_list_of_consultant_id
                JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND vv.status = ".ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED."
                AND loc.status = ". self::STATUS_APPROVAL." ".$contractSql."
                GROUP BY loc.id
            ) max_versions ON max_versions.id = loc.id AND max_versions.version = vv.version
            WHERE v.user_id <> ".$user->id." AND v.deleted_at IS NULL AND vv.status = ".ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED."
            AND loc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loc.id
        ) latest_approved_verifier ON latest_approved_verifier.id = cmloc.id AND latest_approved_verifier.verifier_version_id = (cmlocvv.id - 1)
        JOIN consultant_management_loc_verifier_versions prev_cmlocvv ON prev_cmlocvv.id = latest_approved_verifier.verifier_version_id
        INNER JOIN (
            SELECT loc.id, MAX(rfpv.revision) AS revision
            FROM consultant_management_list_of_consultants loc
            JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE loc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loc.id
        ) max_rfp_revisions ON cmloc.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfpv.revision
        INNER JOIN (
            SELECT loc.id, MAX(vv.version) AS version
            FROM consultant_management_loc_verifier_versions vv
            JOIN consultant_management_list_of_consultant_verifiers v ON v.id = vv.consultant_management_list_of_consultant_verifier_id
            JOIN consultant_management_list_of_consultants loc ON loc.id = v.consultant_management_list_of_consultant_id
            JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.user_id = ".$user->id." AND v.deleted_at IS NULL AND loc.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loc.id
        ) max_versions ON cmloc.id = max_versions.id AND cmlocvv.version = max_versions.version AND prev_cmlocvv.version = max_versions.version
        WHERE cmlocv.user_id = ".$user->id." AND cmlocvv.status = ".ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING."
        AND prev_cmlocvv.status = ".ConsultantManagementListOfConsultantVerifierVersion::STATUS_APPROVED."
        AND cmloc.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND cmlocv.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, cmloc.id, rfpv.id, prev_cmlocvv.id");

        $stmt->execute();

        $result2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result2 as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result2[$idx]['days_pending'] = $then->diffInDays($now);
        }

        return array_merge($result, $result2);
    }

    public static function getPendingVerificationByCompanyAndContract(Company $company, ConsultantManagementContract $contract)
    {
        $pdo = \DB::getPdo();

        $companyUsers = $contract->getUsersByRoleAndCompany(ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT, $company);

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
            $stmt = $pdo->prepare("SELECT cmloc.id AS loc_id, rfp.id AS rfp_id, vc.id AS vendor_category_id, vc.name AS vendor_category_name,
            TO_CHAR(cmloc.created_at :: DATE, 'dd/mm/yyyy') AS created_at, ".$company->id." AS company_id
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
            JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
            JOIN consultant_management_rfp_revisions rfpv ON rfpv.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_list_of_consultants cmloc ON cmloc.consultant_management_rfp_revision_id = rfpv.id
            JOIN consultant_management_list_of_consultant_verifiers cmlocv ON cmlocv.consultant_management_list_of_consultant_id = cmloc.id
            JOIN consultant_management_loc_verifier_versions cmlocvv ON cmlocvv.consultant_management_list_of_consultant_verifier_id = cmlocv.id
            INNER JOIN (
                SELECT loc.id, MAX(rfpv.revision) AS revision
                FROM consultant_management_list_of_consultants loc
                JOIN consultant_management_rfp_revisions rfpv ON loc.consultant_management_rfp_revision_id = rfpv.id
                JOIN consultant_management_vendor_categories_rfp rfp ON rfpv.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE loc.status = ". self::STATUS_APPROVAL." AND c.id = ".$contract->id."
                GROUP BY loc.id
            ) max_rfp_revisions ON cmloc.id = max_rfp_revisions.id AND max_rfp_revisions.revision = rfpv.revision
            WHERE cmlocv.user_id IN (".implode(',', $userIds).") AND c.id = ".$contract->id."
            AND cmlocvv.status = ".ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING."
            AND cmloc.status = ". self::STATUS_APPROVAL."
            AND cmlocv.deleted_at IS NULL
            GROUP BY c.id, rfp.id, vc.id, cmloc.id");

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public static function hasInProgressRecords(User $user)
    {
        $query = "WITH loc_pending_verifiers_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY loc.id ORDER BY vv.version DESC) AS rank, loc.id, vv.user_id
                      FROM consultant_management_list_of_consultants loc
                      INNER JOIN consultant_management_list_of_consultant_verifiers v ON v.consultant_management_list_of_consultant_id = loc.id
                      INNER JOIN consultant_management_loc_verifier_versions vv ON vv.consultant_management_list_of_consultant_verifier_id = v.id
                      WHERE v.deleted_at IS NULL
                      AND loc.status = " . self::STATUS_APPROVAL . "
                      AND vv.status = " . ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING . "
                      AND v.user_id = " . $user->id . "
                  )
                  SELECT *
                  FROM loc_pending_verifiers_cte
                  WHERE rank = 1";

        $pdo  = \DB::getPdo();
        $stmt = $pdo->prepare($query);

        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return (count($data) > 0);
    }
}