<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Collection;

trait ClauseRepositoryTrait {

    private $savedClausesId = array();

    /**
     * @param $clauseId
     *
     * @return Clause
     */
    private function findOrNewClause($clauseId)
    {
        $clause = Clause::find($clauseId);

        if( ! isset( $clause ) ) $clause = new Clause();

        return $clause;
    }

    private function deleteClause($clauseId, $isTemplate)
    {
        if( $clause = Clause::find($clauseId) )
        {
            if( ( ! $isTemplate ) && ( ! $clause->is_editable ) ) return false;

            return $clause->delete();
        }

        return false;
    }

    /**
     * Copies a Clause Object and its children Clause Objects into the storage and assigns it to the given tender.
     *
     * @param      $clauses
     * @param      $formOfTenderId
     * @param null $parentId
     */
    private function copyClausesFrom($clauses, $formOfTenderId, $parentId = null)
    {
        foreach($clauses as $clause)
        {
            $newClause              = $clause->replicate();
            $newClause->form_of_tender_id   = $formOfTenderId;
            $newClause->parent_id   = isset( $parentId ) ? $parentId : 0;
            $newClause->save();

            $this->copyClausesFrom($clause->children, $formOfTenderId, $newClause->id);
        }
    }

    /**
     * Saves Json clauses into storage as Clause resources.
     *
     * @param $clausesJson
     * @param $tenderId
     * @param $isTemplate
     *
     * @return array
     */
    private function saveClauses($clausesJson, $formOfTenderId, $isTemplate)
    {
        $this->savedClausesId = array();

        if( isset( $clausesJson ) ) $this->iterateThroughAndSaveObjectsAndChildren($clausesJson, $isTemplate, $formOfTenderId);

        return $this->savedClausesId;
    }

    /**
     * Saves each json clause in the array and its children into storage as Clause resources.
     *
     * @param      $arrayOfJson
     * @param      $isTemplate
     * @param      $formOfTenderId
     * @param null $parentObject
     */
    private function iterateThroughAndSaveObjectsAndChildren($arrayOfJson, $isTemplate, $formOfTenderId, $parentObject = null)
    {
        $sequenceNo = 1;
        foreach($arrayOfJson as $json)
        {
            $this->saveObjectAndChildren($isTemplate, $formOfTenderId, $parentObject, $json, $sequenceNo);
        }
    }

    /**
     * Saves the clause and its children clauses.
     *
     * @param $isTemplate
     * @param $formOfTenderId
     * @param $parentObject
     * @param $json
     * @param $sequenceNo
     */
    private function saveObjectAndChildren($isTemplate, $formOfTenderId, $parentObject, $json, &$sequenceNo)
    {
        $clause = $this->enterClauseData($json, $sequenceNo++, $isTemplate, $formOfTenderId);

        if( ! isset( $parentObject ) )
        {
            $clause->parent_id = 0;

            $saved = $clause->save();
            $clause->dissociateChildren();
            $this->saveChildren($isTemplate, $formOfTenderId, $json, $clause);
        }
        else
        {
            $saved = $parentObject->children()->save($clause);
        }

        if( $saved ) $this->savedClausesId[] = $clause->id;
    }

    /**
     * 'Populates' the Clause object.
     *
     * @param $clauseJson
     * @param $sequenceNo
     * @param $isTemplate
     * @param $formOfTenderId
     *
     * @return Clause
     */
    private function enterClauseData($clauseJson, $sequenceNo, $isTemplate, $formOfTenderId)
    {
        $content = isset( $clauseJson['content'] ) ? $clauseJson['content'] : '';

        $clause                    = $this->getClause($clauseJson);
        $clause->clause            = $content;
        $clause->sequence_number   = $sequenceNo;
        $clause->form_of_tender_id = $formOfTenderId;

        if( $isTemplate ) $clause->is_editable = isset( $clauseJson['is_editable'] );

        return $clause;
    }

    /**
     * Returns a clause resource if it exists. If it doesn't yet exist, it is created.
     *
     * @param $clauseJson
     *
     * @return Clause
     */
    private function getClause($clauseJson)
    {
        if( ! isset( $clauseJson['id'] ) ) $clauseJson['id'] = -1;

        return $this->findOrNewClause($clauseJson['id']);
    }

    /**
     * Saves the children of the json clause.
     *
     * @param $isTemplate
     * @param $formOfTenderId
     * @param $json
     * @param $clause
     */
    private function saveChildren($isTemplate, $formOfTenderId, $json, $clause)
    {
        if( isset( $json['children'] ) ) $this->iterateThroughAndSaveObjectsAndChildren($json['children'], $isTemplate, $formOfTenderId, $clause);
    }

    /**
     * Deletes clauses and their children from storage.
     *
     * @param $clausesJson
     */
    private function deleteClauses($clausesJson, $isTemplate)
    {
        if( isset( $clausesJson ) ) $this->iterateThroughAndDeleteObjectsAndChildren($clausesJson, $isTemplate);
    }

    /**
     * Deletes a clause and its children from storage.
     *
     * @param $arrayOfJson
     */
    private function iterateThroughAndDeleteObjectsAndChildren($arrayOfJson, $isTemplate)
    {
        foreach($arrayOfJson as $key => $json)
        {
            $this->deleteClauseIfExists($json, $isTemplate);
            $this->deleteChildren($json, $isTemplate);
        }
    }

    /**
     * Deletes a Clause resource from storage.
     *
     * @param $clauseJson
     */
    private function deleteClauseIfExists($clauseJson, $isTemplate)
    {
        if( isset( $clauseJson['id'] ) ) $this->deleteClause($clauseJson['id'], $isTemplate);
    }

    /**
     * Deletes the children of a clause.
     *
     * @param $json
     */
    private function deleteChildren($json, $isTemplate)
    {
        if( isset( $json['children'] ) ) $this->iterateThroughAndDeleteObjectsAndChildren($json['children'], $isTemplate);
    }

    /**
     * Find a Clause resource by tender id.
     *
     * @param $formOfTenderId
     *
     * @return mixed
     */
    private function findClausesByFormOfTenderId($formOfTenderId)
    {
        return Clause::where('form_of_tender_id', '=', $formOfTenderId)
            ->where('parent_id', '=', 0)
            ->orderBy('sequence_number', 'asc')
            ->with('children')
            ->get();
    }

    /**
     * Updates the clauses resources and logs the update.
     *
     * @param $formOfTenderId
     * @param $isTemplate
     * @param $input
     */
    public function updateClauses($formOfTenderId, $isTemplate, $input)
    {
        $tenderAlternativesMarkerPositions = isset( $input['tenderAlternativesMarkerPositions'] ) ? $input['tenderAlternativesMarkerPositions'] : array();
        $inactiveClauses                   = isset( $input['inactiveClauses'] ) ? $input['inactiveClauses'] : array();
        $clauses                           = isset( $input['clauses'] ) ? $input['clauses'] : array();

        $this->saveTenderAlternativesPositions($formOfTenderId, $tenderAlternativesMarkerPositions);

        $this->deleteClauses($inactiveClauses, $isTemplate);

        $savedClausesId = $this->saveClauses($clauses, $formOfTenderId, $isTemplate);

        if( ! $isTemplate ) $this->appendUnEditableClauses($formOfTenderId, $savedClausesId);

        $this->addLogEntry($formOfTenderId);
    }

    private function appendUnEditableClauses($formOfTenderId, array $savedClausesId)
    {
        $currentSequenceNumber = Clause::where('form_of_tender_id', '=', $formOfTenderId)
            ->where('is_editable', '=', true)
            ->where('parent_id', '=', 0)
            ->max('sequence_number');

        foreach($this->getUnEditableClauses($formOfTenderId, $savedClausesId) as $unEditableClause)
        {
            $unEditableClause->parent_id       = 0;
            $unEditableClause->sequence_number = ++$currentSequenceNumber;
            $unEditableClause->save();
        }
    }

    private function getUnEditableClauses($formOfTenderId, $savedClausesId)
    {
        return Clause::where('form_of_tender_id', '=', $formOfTenderId)
            ->where('is_editable', '=', false)
            ->whereNotIn('id', $savedClausesId)
            ->get();
    }
}