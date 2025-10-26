<?php namespace PCK\ClauseItems;

use Illuminate\Database\Eloquent\Model;

class AttachedClauseItem extends Model {

    protected $fillable = [ 'no', 'description', 'priority', 'origin_id' ];

    protected $table = 'attached_clause_items';

    public function attachable()
    {
        return $this->morphTo();
    }

    public static function syncClauses(Model $object, array $clauseItemIds)
    {
        AttachedClauseItem::where('attachable_id', '=', $object->id)
            ->where('attachable_type', '=', get_class($object))
            ->delete();

        foreach(ClauseItem::whereIn('id', $clauseItemIds)->get() as $clauseItem)
        {
            $attachedClauseItem = new AttachedClauseItem(array(
                'no'          => $clauseItem->no,
                'description' => $clauseItem->description,
                'priority'    => $clauseItem->priority,
                'origin_id'   => $clauseItem->id,
            ));

            $attachedClauseItem->attachable()->associate($object);
            $attachedClauseItem->save();
        }
    }

}