<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class VariationOrderItem extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_variation_order_items';

    const TYPE_HEADER    = 1;
    const TYPE_WORK_ITEM = 2;

    const TYPE_HEADER_TEXT    = 'HEAD';
    const TYPE_WORK_ITEM_TEXT = 'ITEM';

    protected $fillable = [
        'bill_ref',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $variationOrderItem)
        {
            $variationOrderItem->is_from_rfv = true;

            $variationOrderItem->lft   = 1;
            $variationOrderItem->rgt   = 2;
            $variationOrderItem->level = 0;
        });

        static::created(function(self $variationOrderItem)
        {
            $variationOrderItem->root_id = $variationOrderItem->id;

            $variationOrderItem->save();

            $variationOrderItem->updateRootPriority();
        });

        static::saving(function(self $variationOrderItem)
        {
            if( $variationOrderItem->type == VariationOrderItem::TYPE_HEADER )
            {
                $variationOrderItem->reference_rate     = 0;
                $variationOrderItem->reference_quantity = 0;
                $variationOrderItem->reference_amount   = 0;

                $variationOrderItem->uom_id = null;
            }

            $variationOrderItem->reference_amount = $variationOrderItem->reference_rate * $variationOrderItem->reference_quantity;
        });

        static::saved(function(self $variationOrderItem)
        {
            $pdo = \DB::connection('buildspace')->getPdo();

            $stmt = $pdo->prepare("SELECT SUM(i.reference_amount) AS total
                FROM bs_variation_order_items i
                JOIN bs_variation_orders vo ON i.variation_order_id = vo.id
                WHERE vo.id = " . $variationOrderItem->variation_order_id . "
                AND i.is_from_rfv IS TRUE AND i.type = " . VariationOrderItem::TYPE_WORK_ITEM . "
                AND i.deleted_at IS NULL
                GROUP BY vo.id");

            $stmt->execute();

            $total = $stmt->fetch(\PDO::FETCH_COLUMN, 0);

            $requestForVariation = $variationOrderItem->variationOrder->getRequestForVariation();

            if( $requestForVariation )
            {
                $requestForVariation->nett_omission_addition = $total;
                $requestForVariation->save();
            }
        });

        static::deleted(function(self $variationOrderItem)
        {
            $pdo = \DB::connection('buildspace')->getPdo();

            $stmt = $pdo->prepare("UPDATE bs_variation_order_items SET priority = priority - 1
                WHERE variation_order_id =" . $variationOrderItem->variation_order_id . " AND priority >=" . $variationOrderItem->priority . " AND id = root_id
                AND id <> " . $variationOrderItem->id . " AND deleted_at IS NULL");

            $stmt->execute();

            $stmt = $pdo->prepare("UPDATE bs_variation_order_items AS i SET priority = r.priority
                FROM bs_variation_order_items AS r
                WHERE i.root_id = r.id AND i.id <> r.id AND i.priority <> r.priority
                AND i.variation_order_id = " . $variationOrderItem->variation_order_id . " AND i.deleted_at IS NULL");

            $stmt->execute();

            $stmt = $pdo->prepare("SELECT SUM(i.reference_amount) AS total
                FROM bs_variation_order_items i
                JOIN bs_variation_orders vo ON i.variation_order_id = vo.id
                WHERE vo.id = " . $variationOrderItem->variation_order_id . "
                AND i.is_from_rfv IS TRUE AND i.type = " . VariationOrderItem::TYPE_WORK_ITEM . "
                AND i.deleted_at IS NULL
                GROUP BY vo.id");

            $stmt->execute();

            $total = $stmt->fetch(\PDO::FETCH_COLUMN, 0);

            $requestForVariation = $variationOrderItem->variationOrder->getRequestForVariation();

            if( $requestForVariation )
            {
                $requestForVariation->nett_omission_addition = $total;
                $requestForVariation->save();
            }
        });
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'updated_by');
    }

    public function variationOrder()
    {
        return $this->belongsTo('PCK\Buildspace\VariationOrder', 'variation_order_id');
    }

    protected function updateRootPriority()
    {
        $pdo = \DB::connection('buildspace')->getPdo();

        $stmt = $pdo->prepare("UPDATE bs_variation_order_items SET priority = priority + 1
            WHERE variation_order_id =" . $this->variation_order_id . " AND priority >=" . $this->priority . " AND id = root_id
            AND id <> " . $this->id . " AND deleted_at IS NULL");

        $stmt->execute();

        $stmt = $pdo->prepare("UPDATE bs_variation_order_items AS i SET priority = r.priority
            FROM bs_variation_order_items AS r
            WHERE i.root_id = r.id AND i.id <> r.id AND i.priority <> r.priority
            AND i.variation_order_id = " . $this->variation_order_id . " AND i.deleted_at IS NULL");

        $stmt->execute();
    }

    public function isRoot()
    {
        return ( $this->rgt - $this->lft == 1 );
    }
}
