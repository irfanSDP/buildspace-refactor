<?php namespace PCK\TechnicalEvaluationAttachmentListItems;

use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;

class TechnicalEvaluationAttachmentListItemRepository {

    /**
     * Returns the resource with the specified id.
     * Returns null if id is not specified.
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection|null|static
     */
    public function find($id)
    {
        if( empty( $id ) ) return null;

        return TechnicalEvaluationAttachmentListItem::find($id);
    }

    /**
     * Saves a list item.
     *
     * @param TechnicalEvaluationSetReference $setReference
     * @param                                 $input
     *
     * @return bool
     */
    public function add(TechnicalEvaluationSetReference $setReference, $input)
    {
        $listItem = new TechnicalEvaluationAttachmentListItem;

        $listItem->set_reference_id = $setReference->id;
        $listItem->description      = $input['description'];
        $listItem->compulsory       = isset( $input['compulsory'] ) ? $input['compulsory'] : false;

        return $listItem->save();
    }

    /**
     * Updates a list item.
     *
     * @param TechnicalEvaluationAttachmentListItem $listItem
     * @param                                       $input
     *
     * @return bool
     */
    public function update(TechnicalEvaluationAttachmentListItem $listItem, $input)
    {
        $listItem->description = $input['description'];
        $listItem->compulsory  = isset( $input['compulsory'] ) ? $input['compulsory'] : false;

        return $listItem->save();
    }

    /**
     * Removes a list item.
     *
     * @param TechnicalEvaluationSetReference $setReference
     * @param                                 $listItemId
     *
     * @return mixed
     */
    public function delete(TechnicalEvaluationSetReference $setReference, $listItemId)
    {
        return TechnicalEvaluationAttachmentListItem::where('id', '=', $listItemId)
            ->where('set_reference_id', '=', $setReference->id)
            ->delete();
    }

}