<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use PCK\Users\User;
use Carbon\Carbon;
use PCK\Buildspace\VariationOrder as BsVariationOrder;
use PCK\Buildspace\VariationOrderItem as BsVariationOrderItem;
use PCK\RequestForVariation\RequestForVariationUserPermission;
use PCK\Verifier\Verifier;
use PCK\Verifier\Verifiable;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class RequestForVariation extends Model implements Verifiable {

    use SoftDeletingTrait;

    protected $table = 'request_for_variations';

    const STATUS_NEW_RFV = 0;
    const STATUS_PENDING_COST_ESTIMATE = 1;
    const STATUS_PENDING_VERIFICATION = 2;
    const STATUS_VERIFIED = 3;
    const STATUS_PENDING_APPROVAL = 4;
    const STATUS_APPROVED = 5;
    const STATUS_REJECTED = 6;

    const STATUS_NEW_RFV_TEXT = "New Request for Variation";
    const STATUS_PENDING_COST_ESTIMATE_TEXT = 'Pending Cost Estimate';
    const STATUS_PENDING_VERIFICATION_TEXT = 'Pending Verification';
    const STATUS_VERIFIED_TEXT = 'Verified';
    const STATUS_PENDING_APPROVAL_TEXT = 'Pending Approval';
    const STATUS_APPROVED_TEXT = 'Approved';
    const STATUS_REJECTED_TEXT = 'Rejected';

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $requestForVariation)
        {
            $rfvNumber = 1;
            if($requestForVariation->project_id){
                $latestRfvNumber = \DB::table('request_for_variations')
                ->select('rfv_number')
                ->where('project_id', $requestForVariation->project_id)
                ->orderBy('rfv_number', 'DESC')
                ->first();
                $rfvNumber = $latestRfvNumber ? (intval($latestRfvNumber->rfv_number) + 1) : 1;
            }

            $requestForVariation->rfv_number = $rfvNumber;
            $requestForVariation->initiated_by = \Confide::user()->id;
            $requestForVariation->status = RequestForVariation::STATUS_PENDING_COST_ESTIMATE;
            $requestForVariation->permission_module_in_charge = RequestForVariation::getRfvStatusModuleMapping(RequestForVariation::STATUS_PENDING_COST_ESTIMATE);
        });

        static::updating(function(self $requestForVariation)
        {
            $requestForVariation->permission_module_in_charge = RequestForVariation::getRfvStatusModuleMapping($requestForVariation->status);

            $buildspaceProjectId = $requestForVariation->project->getBsProjectMainInformation()->project_structure_id;

            $variationOrder = BsVariationOrder::where('eproject_rfv_id' , '=', $requestForVariation->id)->where('project_structure_id', '=', $buildspaceProjectId)->first();
            if($variationOrder)
            {
                $variationOrder->description = 'VO Number: '.$requestForVariation->rfv_number." - ".$requestForVariation->description;
                $variationOrder->save();
            }
        });
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function userPermissionGroup()
    {
        return $this->belongsTo('PCK\RequestForVariation\RequestForVariationUserPermissionGroup', 'request_for_variation_user_permission_group_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'initiated_by');
    }

    public function submittedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'submitted_by');
    }

    public function requestForVariationCategory()
    {
        return $this->belongsTo('PCK\RequestForVariation\RequestForVariationCategory');
    }

    public function actionLogs()
    {
        return $this->hasMany('PCK\RequestForVariation\RequestForVariationActionLog')
                    ->orderBy('created_at', 'ASC');
    }

    public function isPendingForApproval()
    {
        return ($this->status == self::STATUS_PENDING_APPROVAL);
    }

    public function isApproved()
    {
        return ($this->status == self::STATUS_APPROVED);
    }

    public static function getRfvStatusModuleMapping($rfvStatus)
    {
        $mapping = [
            self::STATUS_NEW_RFV                  => RequestForVariationUserPermission::ROLE_SUBMIT_RFV,
            self::STATUS_PENDING_COST_ESTIMATE    => RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION,
            self::STATUS_PENDING_VERIFICATION     => RequestForVariationUserPermission::ROLE_SUBMIT_RFV,
            self::STATUS_VERIFIED                 => RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL,
            self::STATUS_PENDING_APPROVAL         => RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL,
            self::STATUS_APPROVED                 => 0,
            self::STATUS_REJECTED                 => 0,
        ];

        return $mapping[$rfvStatus];
    }

    public function getStatusText()
    {
        switch($this->status)
        {
            case self::STATUS_NEW_RFV:
                return self::STATUS_NEW_RFV_TEXT;
            case self::STATUS_PENDING_COST_ESTIMATE:
                return self::STATUS_PENDING_COST_ESTIMATE_TEXT;
            case self::STATUS_PENDING_VERIFICATION:
                return self::STATUS_PENDING_VERIFICATION_TEXT;
            case self::STATUS_VERIFIED:
                return self::STATUS_VERIFIED_TEXT;
            case self::STATUS_PENDING_APPROVAL:
                return self::STATUS_PENDING_APPROVAL_TEXT;
            case self::STATUS_APPROVED:
                return self::STATUS_APPROVED_TEXT;
            case self::STATUS_REJECTED:
                return self::STATUS_REJECTED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function canUserEditCostEstimation(User $user)
    {
        if($this->status != self::STATUS_PENDING_COST_ESTIMATE)
        {
            return false;
        }

        $userPermissions = $user->getRequestForVariationUserPermissionsByProject($this->project);

        if($userPermissions)
        {
            $permissionModules = array_column($userPermissions->toArray(), 'module_id');

            return (in_array(RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION, $permissionModules));
        }

        return false;
    }

    public function canUserUploadDeleteFiles(User $user)
    {
        if($this->isApproved() || $this->isPendingForApproval()) return false;

        $userPermissions = $user->getRequestForVariationUserPermissionsByProject($this->project);
        $canUploadFiles = false;

        if($userPermissions)
        {
            $permissionModules = array_column($userPermissions->toArray(), 'module_id');
            $canUploadFiles = in_array(RequestForVariationUserPermission::ROLE_SUBMIT_RFV, $permissionModules) || in_array(RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION, $permissionModules);
        }

        return $canUploadFiles;
    }

    public function canUserApprovePendingVerification(User $user)
    {
        if($this->status != self::STATUS_PENDING_VERIFICATION)
        {
            return false;
        }

        $userPermissions = $user->getRequestForVariationUserPermissionsByProject($this->project);

        if($userPermissions)
        {
            $permissionModules = array_column($userPermissions->toArray(), 'module_id');

            return (in_array(RequestForVariationUserPermission::ROLE_SUBMIT_RFV, $permissionModules));
        }

        return false;
    }

    public function canUserAssignVerifiers(User $user)
    {
        if($this->status != self::STATUS_VERIFIED)
        {
            return false;
        }

        $userPermissions = $user->getRequestForVariationUserPermissionsByProject($this->project);

        if($userPermissions)
        {
            foreach ($userPermissions as $userPermission)
            {
                if($userPermission->module_id == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL && $userPermission->is_editor)
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAllUserIdsInModule($moduleId, $editorsOnly = false)
    {
        $query = \DB::table('request_for_variation_user_permissions AS p')
            ->join('request_for_variation_user_permission_groups AS g', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('p.id', 'p.user_id')
            ->where('g.id', $this->request_for_variation_user_permission_group_id)
            ->where('p.module_id', '=', $moduleId);
        
        if($editorsOnly)
        {
            $query->where('p.is_editor', true);
        }

        return $query->distinct('p.user_id')
            ->orderBy('p.id')
            ->lists('p.user_id');
    }

    public function canUserVerifyPendingApproval(User $user)
    {
        if($this->status != self::STATUS_PENDING_APPROVAL)
        {
            return false;
        }

        return Verifier::isCurrentVerifier($user, $this);
    }

    public function costEstimateItemAdd(Array $data, BsVariationOrderItem $nextVariationOrderItem=null)
    {
        $buildspaceProjectId = $this->project->getBsProjectMainInformation()->project_structure_id;

        $variationOrder = BsVariationOrder::where('eproject_rfv_id' , '=', $this->id)->where('project_structure_id', '=', $buildspaceProjectId)->first();

        if(!$variationOrder)
        {
            $variationOrder = $this->createBuildspaceVariationOrder();
        }

        if($nextVariationOrderItem)
        {
            $variationOrderItem = $variationOrder->createItem($nextVariationOrderItem);
        }
        else
        {
            $previousItem = $data['prev_item_id'] > 0 ? BsVariationOrderItem::find($data['prev_item_id']) : null;

            $variationOrderItem = $variationOrder->createVariationOrderItemFromLastRow($data['field'], $data['value'], $previousItem);
        }

        return $variationOrderItem;
    }

    public function getUsersCanBeAssignedAsVerifiers()
    {
        $verifierIds = $this->getAllUserIdsInModule(RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL);

        return !empty($verifierIds) ? User::whereIn('id', $verifierIds)->get() : new Collection();
    }

    public function showFinancialStanding()
    {
        return in_array($this->status, [self::STATUS_VERIFIED, self::STATUS_PENDING_APPROVAL, self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }

    public function showKpiLimitTable()
    {
        // for existing data that was approved before approved_category_amount column was introduced
        if(($this->status == self::STATUS_APPROVED) && is_null($this->approved_category_amount))
        {
            return false;
        }

        return $this->requestForVariationCategory->isKpiLimitEnabled() && in_array($this->status, [self::STATUS_VERIFIED, self::STATUS_PENDING_APPROVAL, self::STATUS_APPROVED]);
    }

    public function createBuildspaceVariationOrder()
    {
        $buildspaceProjectId = $this->project->getBsProjectMainInformation()->project_structure_id;

        $variationOrder = new BsVariationOrder();

        $variationOrder->description = 'VO Number: '.$this->rfv_number." - ".$this->description;
        $variationOrder->project_structure_id = $buildspaceProjectId;
        $variationOrder->eproject_rfv_id = $this->id;
        $variationOrder->type = BsVariationOrder::TYPE_BUDGETARY;
        $variationOrder->priority = 0;

        $variationOrder->save();

        return $variationOrder;
    }

    public function canUserViewCostEstimateAmount(User $user)
    {
        $count = \DB::table('request_for_variation_user_permissions AS p')
            ->join('request_for_variation_user_permission_groups AS g', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
            ->select('p.id')
            ->where('g.id', $this->request_for_variation_user_permission_group_id)
            ->where('p.can_view_cost_estimate', true)
            ->where('p.user_id', $user->id)
            ->count();
            
        return $count > 0;
    }

    /**
     * get cumulative rfv amount by category, excluding self
     */
    public function getCumulativeRfvAmountByStatusAndCategory(array $statuses, $categoryId)
    {
        $pdo = \DB::getPdo();

        $query = "SELECT COALESCE(SUM(r.nett_omission_addition), 0) AS total
                    FROM request_for_variations r
                    WHERE r.project_id = " . $this->project_id . "
                    AND r.status IN(" . implode(',', $statuses). ") 
                    AND r.request_for_variation_category_id = " . $categoryId . "
                    AND r.id != " . $this->id . "
                    AND r.deleted_at IS NULL
                    GROUP BY r.project_id";

        $stmt = $pdo->prepare($query);

        $stmt->execute();

        $amount = $stmt->fetch(\PDO::FETCH_COLUMN, 0);

        return $amount ? $amount : number_format(0.0, 2, '.', '');
    }

    public function getCostEstimateItems()
    {
        $pdo = \DB::connection('buildspace')->getPdo();

        $buildspaceProjectId = $this->project->getBsProjectMainInformation()->project_structure_id;

        $stmt = $pdo->prepare("SELECT i.id, i.bill_ref, i.description, i.type, i.reference_quantity, i.reference_rate, i.reference_amount, i.priority, i.remarks,
            uom.id AS uom_id, uom.symbol AS uom_symbol
            FROM bs_variation_order_items i
            LEFT JOIN bs_unit_of_measurements uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN bs_variation_orders vo ON i.variation_order_id = vo.id
            WHERE vo.eproject_rfv_id = " . $this->id . " AND vo.project_structure_id = " . $buildspaceProjectId . "
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // gets the omisssion addition by rounding each cost estimate item, then adding them to get the final amount
    // this is to mitigate the rounding issues
    public function getAdjustedNettOmissionAddition()
    {
        $variationOrderItems = $this->getCostEstimateItems();

        $referenceAmounts = array_map(function($amount) {
            return number_format($amount, 2, '.', '');
        }, array_column($variationOrderItems, 'reference_amount'));

        return array_sum($referenceAmounts);
    }

    /**Verifiable stuffs**/

    public function getOnApprovedView()
    {
    }

    public function getOnRejectedView()
    {
    }

    public function getOnPendingView()
    {
    }

    public function getRoute()
    {
        $user = \Confide::user();

        if($user->getAssignedCompany($this->project))
        {
            return route('requestForVariation.form.show', [$this->project->id, $this->id]);
        }
        else if($user->isTopManagementVerifier())
        {
            return route('topManagementVerifiers.requestForVariation.form.show', [$this->project->id, $this->id]);
        }

        return null;
    }

    public function getViewData($locale)
    {
    }

    public function getOnApprovedNotifyList()
    {
    }

    public function getOnRejectedNotifyList()
    {
    }

    public function getOnApprovedFunction()
    {
    }

    public function getOnRejectedFunction()
    {
    }

    public function onReview()
    {
    }

    public function getEmailSubject($locale)
    {
        return "";
    }

    public function getSubmitterId()
    {
        return $this->submitted_by;
    }

    public function getModuleName()
    {
        return trans('modules.requestForVariation');
    }

    public function getObjectDescription()
    {
        return $this->description;
    }

    public function getDaysPendingAttribute()
    {
        $then = Carbon::parse($this->updated_at);
        $now = Carbon::now();

        return $then->diffInDays($now);
    }

    public function getProject()
    {
        return $this->project;
    }
}


