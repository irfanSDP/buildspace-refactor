<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use PCK\RequestForVariation\RequestForVariation;
use PCK\Buildspace\PostContractClaim;
use PCK\Buildspace\VariationOrderItem;

class VariationOrder extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_variation_orders';

    const TYPE_STANDARD = 1;
    const TYPE_BUDGETARY = 2;
    const TYPE_CLAIMABLE = 4;
    const TYPE_NON_CLAIMABLE = 8;

    const TYPE_STANDARD_TEXT = "STANDARD";
    const TYPE_BUDGETARY_TEXT = "BUDGETARY";
    const TYPE_CLAIMABLE_TEXT = "CLAIMABLE";
    const TYPE_NON_CLAIMABLE_TEXT = "NON CLAIMABLE";

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $variationOrder)
        {
            $variationOrder->status = PostContractClaim::STATUS_PREPARING;

            $variationOrder->where('id', '>=', 0)
                ->where('project_structure_id', $variationOrder->project_structure_id)
                ->where('deleted_at', null)
                ->update([ 'priority' => \DB::raw('priority + 1') ]);
        });
    }

    public function getRequestForVariation()
    {
        return ($this->eproject_rfv_id) ? RequestForVariation::find($this->eproject_rfv_id) : null;
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'updated_by');
    }

    public function getEprojectUpdatedBy()
    {
        return $this->updatedBy->Profile->getEProjectUser();
    }

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function getDisplayDescriptionAttribute()
    {
        return $this->description;
    }

    public function onReview($project, $moduleId)
    {
        if( ContractManagementClaimVerifier::isApproved($project, $moduleId, $this->id) )
        {
            $this->status      = PostContractClaim::STATUS_APPROVED;
            $this->is_approved = true;

            if( $claimCertificate = $this->projectStructure->getCurrentClaimCertificate() )
            {
                $pivotRelation = VariationOrderClaimCertificate::firstOrNew(array( 'variation_order_id' => $this->id ));

                $pivotRelation->claim_certificate_id = $claimCertificate->id ?? null;
                $pivotRelation->save();
            }
        }
        elseif( ContractManagementClaimVerifier::isRejected($project, $moduleId, $this->id) )
        {
            $this->status      = PostContractClaim::STATUS_PREPARING;
            $this->is_approved = false;
        }

        return $this->save();
    }

    public function createVariationOrderItemFromLastRow($fieldName, $fieldValue, VariationOrderItem $previousItem=null)
    {
        $item = new VariationOrderItem();

        $item->variation_order_id = $this->id;

        $columns = [
            'description',
            'type',
            'uom_id',
            'reference_rate',
            'reference_quantity'
        ];

        if(in_array($fieldName, $columns))
        {
            if($fieldName == 'uom_id')
            {
                $fieldValue = ((int)$fieldValue > 0) ? $fieldValue : null;
            }
            elseif($fieldName == 'type')
            {
                $fieldValue = empty($fieldValue) ? VariationOrderItem::TYPE_WORK_ITEM : $fieldValue;
            }

            if($fieldName == 'reference_rate' or $fieldName == 'reference_quantity')
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->{$fieldName} = number_format($fieldValue,2,'.','');
            }
            else
            {
                $item->{$fieldName} = $fieldValue;
            }
        }

        if($fieldName != 'type')
        {
            $item->type = VariationOrderItem::TYPE_WORK_ITEM;
        }

        $priority = ($previousItem) ? $previousItem->priority + 1 : 0;

        $item->priority = $priority;
        $item->save();

        return $item;
    }

    public function createItem(VariationOrderItem $nextVariationOrderItem)
    {
        $item                     = new VariationOrderItem();
        $item->variation_order_id = $this->id;
        $item->type               = VariationOrderItem::TYPE_WORK_ITEM;

        $priority = $nextVariationOrderItem->priority;
        $item->priority = $priority;
        $item->save();

        return $item;
    }

    public function getTotalClaim()
    {
        $data = \DB::connection('buildspace')
            ->table('bs_variation_orders AS vo')
            ->join('bs_variation_order_claims AS c', 'c.variation_order_id', '=', 'vo.id')
            ->join('bs_variation_order_claim_items AS i', 'i.variation_order_claim_id', '=', 'c.id')
            ->join('bs_variation_order_items AS voi', 'i.variation_order_item_id', '=', 'voi.id')
            ->where('vo.id', $this->id)
            ->where('vo.is_approved', true)
            ->where('voi.type', '<>', VariationOrderItem::TYPE_HEADER)
            ->where('voi.rate', '<>', 0)
            ->where('i.up_to_date_amount', '<>', 0)
            ->whereNull('vo.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('i.deleted_at')
            ->whereNull('voi.deleted_at')
            ->select(\DB::raw('ROUND(SUM(
                CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
                    THEN -1 * ABS(i.up_to_date_amount)
                    ELSE i.up_to_date_amount
                END
                ), 2) AS amount'))
            ->first();
        
            return ($data) ? $data->amount : 0;
    }

    public function getOmissionAddition()
    {
        return \DB::connection('buildspace')
            ->table('bs_variation_order_items AS i')
            ->join('bs_variation_orders AS vo', 'i.variation_order_id', '=', 'vo.id')
            ->where('vo.id', $this->id)
            ->where('i.type', '<>', VariationOrderItem::TYPE_HEADER)
            ->where('i.rate', '<>', 0)
            ->whereNull('vo.deleted_at')
            ->whereNull('i.deleted_at')
            ->select(\DB::raw('
            ROUND(SUM(ROUND(i.total_unit * i.omission_quantity * i.rate, 2)), 2) AS omission,
            ROUND(SUM(ROUND(i.total_unit * i.addition_quantity * i.rate, 2)), 2) AS addition,
            ROUND(SUM(ROUND(i.total_unit * i.addition_quantity * i.rate, 2) - ROUND(i.total_unit * i.omission_quantity * i.rate, 2)), 2) AS nett_omission_addition
            '))
            ->first();
    }

    public static function getTotalVariationOrderByProjects(Array $projectIds)
    {
        $variationOrderByProjects = [];

        if(!empty($projectIds))
        {
            $records = \DB::connection('buildspace')
            ->table('bs_variation_order_items AS i')
            ->join('bs_variation_orders AS vo', 'i.variation_order_id', '=', 'vo.id')
            ->join('bs_project_structures AS p', 'p.id', '=', 'vo.project_structure_id')
            ->whereIn('p.id', $projectIds)
            ->where('p.type', Project::TYPE_ROOT)
            ->where('vo.is_approved', true)
            ->where('i.type', '<>', VariationOrderItem::TYPE_HEADER)
            ->where('i.rate', '<>', 0)
            ->whereNull('p.deleted_at')
            ->whereNull('vo.deleted_at')
            ->whereNull('i.deleted_at')
            ->select('p.id', \DB::raw('ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS total'))
            ->groupBy('p.id')
            ->get();

            foreach($records as $record)
            {
                $variationOrderByProjects[$record->id] = (float)$record->total;
            }
        }

        return $variationOrderByProjects;
    }
}
