<?php namespace PCK\TemplateTenderDocumentFolders;

use Baum\Move;

class TemplateTenderDocumentFolderMove extends Move {

    /**
     * Runs the SQL query associated with the update of the indexes affected
     * by the move operation.
     *
     * @return int
     */
    public function updateStructure()
    {
        list( $a, $b, $c, $d ) = $this->boundaries();

        // select the rows between the leftmost & the rightmost boundaries and apply a lock
        $this->applyLockBetween($a, $d);

        $connection = $this->node->getConnection();
        $grammar    = $connection->getQueryGrammar();

        $currentId     = $this->quoteIdentifier($this->node->getKey());
        $parentId      = $this->quoteIdentifier($this->parentId());
        $leftColumn    = $this->node->getLeftColumnName();
        $rightColumn   = $this->node->getRightColumnName();
        $parentColumn  = $this->node->getParentColumnName();
        $wrappedLeft   = $grammar->wrap($leftColumn);
        $wrappedRight  = $grammar->wrap($rightColumn);
        $wrappedParent = $grammar->wrap($parentColumn);
        $wrappedId     = $grammar->wrap($this->node->getKeyName());

        $lftSql = "CASE
		WHEN $wrappedLeft BETWEEN $a AND $b THEN $wrappedLeft + $d - $b
		WHEN $wrappedLeft BETWEEN $c AND $d THEN $wrappedLeft + $a - $c
		ELSE $wrappedLeft END";

        $rgtSql = "CASE
		WHEN $wrappedRight BETWEEN $a AND $b THEN $wrappedRight + $d - $b
		WHEN $wrappedRight BETWEEN $c AND $d THEN $wrappedRight + $a - $c
		ELSE $wrappedRight END";

        $parentSql = "CASE
		WHEN $wrappedId = $currentId THEN $parentId
		ELSE $wrappedParent END";

        $updateConditions = array(
            $leftColumn   => $connection->raw($lftSql),
            $rightColumn  => $connection->raw($rgtSql),
            $parentColumn => $connection->raw($parentSql)
        );

        if( $this->node->timestamps )
        {
            $updateConditions[ $this->node->getUpdatedAtColumn() ] = $this->node->freshTimestamp();
        }

        return $this->node
            ->newNestedSetQuery()
            ->where(function ($query) use ($leftColumn, $rightColumn, $a, $d)
            {
                $query->whereBetween($leftColumn, array( $a, $d ))
                    ->orWhereBetween($rightColumn, array( $a, $d ));
            })
            ->update($updateConditions);
    }

}