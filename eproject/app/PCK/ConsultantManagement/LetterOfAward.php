<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\LetterOfAwardVerifierVersion;
use PCK\ConsultantManagement\LetterOfAwardClause;
use PCK\ConsultantManagement\LetterOfAwardSubsidiaryRunningNumber;
use PCK\Users\User;

use HTMLPurifier, HTMLPurifier_Config;

class LetterOfAward extends Model
{
    protected $table = 'consultant_management_letter_of_awards';

    protected $fillable = ['vendor_category_rfp_id', 'letterhead', 'signatory', 'status'];

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
            $user = \Confide::user();

            $vendorCategoryRfp = ConsultantManagementVendorCategoryRfp::find($model->vendor_category_rfp_id);

            $vendorCategoryCode = $vendorCategoryRfp->vendorCategory->code;

            $subsidiary = $vendorCategoryRfp->consultantManagementContract->subsidiary;
            $subsidiaryIdentifier = $subsidiary->identifier ?? null;
            $runningNumberPrefix = date('Y');
            $subsidiaryRunningNumber = LetterOfAwardSubsidiaryRunningNumber::find($subsidiary->id);

            if(!$subsidiaryRunningNumber)
            {
                $subsidiaryRunningNumber = new LetterOfAwardSubsidiaryRunningNumber();
                $subsidiaryRunningNumber->subsidiary_id = $subsidiary->id;
                $subsidiaryRunningNumber->next_running_number = 1;

                if($user)
                {
                    $subsidiaryRunningNumber->created_by = $user->id;
                    $subsidiaryRunningNumber->updated_by = $user->id;
                }
                
                $subsidiaryRunningNumber->save();
            }

            $runningNumberFormat = str_pad(($subsidiaryRunningNumber->next_running_number), 5, '0', STR_PAD_LEFT);

            $referenceNo = "{$subsidiaryIdentifier}/{$vendorCategoryCode}/{$runningNumberPrefix}/{$runningNumberFormat}";

            $model->reference_number = $referenceNo;
            $model->running_number   = $subsidiaryRunningNumber->next_running_number;
            $model->status           = LetterOfAward::STATUS_DRAFT;
        });

        self::created(function(self $model)
        {
            $subsidiaryId = $model->consultantManagementVendorCategoryRfp->consultantManagementContract->subsidiary_id;
            $subsidiaryRunningNumber = LetterOfAwardSubsidiaryRunningNumber::find($subsidiaryId);

            $subsidiaryRunningNumber->next_running_number = $subsidiaryRunningNumber->getHighestRunningNumber()+1;
            $subsidiaryRunningNumber->save();
        });
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function clauses()
    {
        return $this->hasMany(LetterOfAwardClause::class, 'template_id')->orderBy('sequence_number', 'asc');
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
        if($this->status != ApprovalDocument::STATUS_APPROVAL)
        {
            return false;
        }

        $latestVersion = LetterOfAwardVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $this->id)
            ->groupBy('consultant_management_letter_of_awards.id')
            ->first();

        if(!$latestVersion)
        {
            return false;
        }

        $latestVerifierLog = LetterOfAwardVerifierVersion::select("consultant_management_letter_of_award_verifier_versions.id AS id", "consultant_management_letter_of_award_verifier_versions.status AS status", "consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id")
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $this->id)
            ->where('consultant_management_letter_of_award_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_letter_of_award_verifiers.user_id', '=', $user->id)
            ->first();
        
        if(!$latestVerifierLog || $latestVerifierLog->status == LetterOfAwardVerifierVersion::STATUS_APPROVED)
        {
            return false;
        }

        $previousVerifierLog = LetterOfAwardVerifierVersion::select("consultant_management_letter_of_award_verifiers.id AS id", "consultant_management_letter_of_award_verifier_versions.status")
            ->join('consultant_management_letter_of_award_verifiers', 'consultant_management_letter_of_award_verifiers.id', '=', 'consultant_management_letter_of_award_verifier_versions.consultant_management_letter_of_award_verifier_id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.id', '=', 'consultant_management_letter_of_award_verifiers.consultant_management_letter_of_award_id')
            ->where('consultant_management_letter_of_awards.id', '=', $this->id)
            ->where('consultant_management_letter_of_award_verifier_versions.version', '=', $latestVersion->version)
            ->where('consultant_management_letter_of_award_verifiers.id', '<', $latestVerifierLog->consultant_management_letter_of_award_verifier_id)
            ->orderBy('consultant_management_letter_of_award_verifiers.id', 'desc')
            ->first();
        
        if(!$previousVerifierLog || $previousVerifierLog->status == LetterOfAwardVerifierVersion::STATUS_APPROVED)
        {
            return true;
        }

        return false;
    }

    public function getStructuredClauses()
    {
        $rootClauses = LetterOfAwardClause::getRootClauses($this);

        $clausesArray = [];

        $config = HTMLPurifier_Config::createDefault();
        $config->loadArray([
            'Core.Encoding' => 'UTF-8',
            'HTML.Doctype' => 'XHTML 1.0 Strict',
            'HTML.Allowed' => 'div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true
        ]);

        $purifier = new HTMLPurifier($config);

        foreach($rootClauses as $clause)
        {
            $html = $purifier->purify($clause->content);

            $data = [
                'id'               => $clause->id,
                'content'          => $html,
                'displayNumbering' => $clause->display_numbering,
                'sequenceNumber'   => $clause->sequence_number,
                'parentId'         => $clause->parent_id,
                'children'         => $this->getChildrenOfNode($clause->id),
            ];

            array_push($clausesArray, $data);
        }

        return $clausesArray;
    }

    private function getChildrenOfNode($parentId)
    {
        $childrenArray = [];

        $children = LetterOfAwardClause::where('template_id', $this->id)
            ->where('parent_id', $parentId)
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $children->isEmpty() ) return $childrenArray;

        foreach($children as $child)
        {
            $data = [
                'id'               => $child->id,
                'content'          => $child->content,
                'displayNumbering' => $child->display_numbering,
                'sequenceNumber'   => $child->sequence_number,
                'parentId'         => $child->parent_id,
                'children'         => $this->getChildrenOfNode($child->id),
            ];

            array_push($childrenArray, $data);
        }

        return $childrenArray;
    }

    public function updateOrCreateClauses(Array $data, $sequenceNumber, LetterOfAwardClause $parent = null)
    {
        $isExistingClause = array_key_exists('id', $data);
        $hasChildren      = array_key_exists('children', $data);

        if( $isExistingClause )
        {
            $clause = LetterOfAwardClause::find($data['id']);
            if(!$clause)
            {
                return false;
            }
        }
        else
        {
            $clause              = new LetterOfAwardClause();
            $clause->template_id = $this->id;
        }

        $clause->content           = isset( $data['content'] ) ? $data['content'] : '';
        $clause->display_numbering = ($data['displayNumbering']);
        $clause->sequence_number   = $sequenceNumber;

        if($parent)
        {
            $clause->parent_id = $parent->id;
        }

        $clause->save();

        if( $hasChildren )
        {
            $sequenceNumber = 1;

            foreach($data['children'] as $childData)
            {
                $this->updateOrCreateClauses($childData, $sequenceNumber++, $clause);
            }
        }

    }

    public function deleteClauses(Array $data)
    {
        $clause = LetterOfAwardClause::find($data['id']);

        if(!$clause)
        {
            return false;
        }

        $children = LetterOfAwardClause::getChildrenOf($clause)->toArray();

        foreach($children as $child)
        {
            $this->deleteClauses($child);
        }

        $clause->delete();
    }

    public static function getPendingReviewsByUser(User $user, ConsultantManagementContract $contract=null)
    {
        $pdo = \DB::getPdo();
        $now = Carbon::now();

        $contractSql = ($contract) ? " AND c.id = ".$contract->id." " : null;
        //latest first verifier
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, loa.id AS loa_id, loavv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_letter_of_awards loa ON loa.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_letter_of_award_verifiers loav ON loav.consultant_management_letter_of_award_id = loa.id
        JOIN consultant_management_letter_of_award_verifier_versions loavv ON loavv.consultant_management_letter_of_award_verifier_id = loav.id
        INNER JOIN (
            SELECT loa.id, MAX(vv.version) AS version, MIN(v.id) AS consultant_management_letter_of_award_verifier_id
            FROM consultant_management_letter_of_award_verifier_versions vv
            JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
            JOIN consultant_management_letter_of_awards loa ON loa.id = v.consultant_management_letter_of_award_id
            JOIN consultant_management_vendor_categories_rfp rfp ON loa.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND loa.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loa.id
        ) first_verifiers ON loa.id = first_verifiers.id AND loavv.version = first_verifiers.version AND loav.id = first_verifiers.consultant_management_letter_of_award_verifier_id
        WHERE loav.user_id = ".$user->id." AND loavv.status = ".LetterOfAwardVerifierVersion::STATUS_PENDING."
        AND loa.status = ". self::STATUS_APPROVAL."
        ".$contractSql."
        AND loav.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, loa.id, loavv.id
        ");

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $idx => $data)
        {
            $then = Carbon::parse($data['updated_at']);
            $result[$idx]['days_pending'] = $then->diffInDays($now);
        }

        //next pending verifier inline
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.reference_no, rfp.id AS rfp_id, vc.name AS rfp_title, loa.id AS loa_id, prev_loavv.updated_at
        FROM consultant_management_contracts c
        JOIN consultant_management_vendor_categories_rfp rfp ON c.id = rfp.consultant_management_contract_id
        JOIN vendor_categories vc ON rfp.vendor_category_id = vc.id
        JOIN consultant_management_letter_of_awards loa ON loa.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_letter_of_award_verifiers loav ON loav.consultant_management_letter_of_award_id = loa.id
        JOIN consultant_management_letter_of_award_verifier_versions loavv ON loavv.consultant_management_letter_of_award_verifier_id = loav.id
        INNER JOIN (
            SELECT loa.id, MAX(vv.id) AS verifier_version_id
            FROM consultant_management_letter_of_award_verifier_versions vv
            JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
            JOIN consultant_management_letter_of_awards loa ON loa.id = v.consultant_management_letter_of_award_id
            JOIN consultant_management_vendor_categories_rfp rfp ON loa.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            INNER JOIN (
                SELECT loa.id, MAX(vv.version) AS version
                FROM consultant_management_letter_of_award_verifier_versions vv
                JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
                JOIN consultant_management_letter_of_awards loa ON loa.id = v.consultant_management_letter_of_award_id
                JOIN consultant_management_vendor_categories_rfp rfp ON loa.vendor_category_rfp_id = rfp.id
                JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
                WHERE v.deleted_at IS NULL AND vv.status = ".LetterOfAwardVerifierVersion::STATUS_APPROVED."
                AND loa.status = ". self::STATUS_APPROVAL." ".$contractSql."
                GROUP BY loa.id
            ) max_versions ON max_versions.id = loa.id AND max_versions.version = vv.version
            WHERE v.user_id <> ".$user->id." AND v.deleted_at IS NULL AND vv.status = ".LetterOfAwardVerifierVersion::STATUS_APPROVED."
            AND loa.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loa.id
        ) latest_approved_verifier ON latest_approved_verifier.id = loa.id AND latest_approved_verifier.verifier_version_id = (loavv.id - 1)
        JOIN consultant_management_letter_of_award_verifier_versions prev_loavv ON prev_loavv.id = latest_approved_verifier.verifier_version_id
        INNER JOIN (
            SELECT loa.id, MAX(vv.version) AS version
            FROM consultant_management_letter_of_award_verifier_versions vv
            JOIN consultant_management_letter_of_award_verifiers v ON v.id = vv.consultant_management_letter_of_award_verifier_id
            JOIN consultant_management_letter_of_awards loa ON loa.id = v.consultant_management_letter_of_award_id
            JOIN consultant_management_vendor_categories_rfp rfp ON loa.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_contracts c ON rfp.consultant_management_contract_id = c.id
            WHERE v.deleted_at IS NULL AND loa.status = ". self::STATUS_APPROVAL." ".$contractSql."
            GROUP BY loa.id
        ) max_versions ON loa.id = max_versions.id AND loavv.version = max_versions.version AND prev_loavv.version = max_versions.version
        WHERE loav.user_id = ".$user->id." AND loavv.status = ".LetterOfAwardVerifierVersion::STATUS_PENDING."
        AND prev_loavv.status = ".LetterOfAwardVerifierVersion::STATUS_APPROVED."
        AND loa.status = ". self::STATUS_APPROVAL." ".$contractSql."
        AND loav.deleted_at IS NULL
        GROUP BY c.id, rfp.id, vc.id, loa.id, prev_loavv.id");
        
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