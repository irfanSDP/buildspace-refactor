<?php
class Doctrine_Template_Listener_FormulatedColumn extends Doctrine_Record_Listener
{
    public function postSave(Doctrine_Event $event)
    {
        $invoker = $event->getInvoker();
        $table = $invoker->getTable();
        $modelName = $table->getComponentName();

        $edgeModelName = str_replace('FormulatedColumn', 'Edge', $modelName);
        $edgeTable = Doctrine_Core::getTable($edgeModelName);

        $references = $this->hasReferencesInFormula($invoker->value);

        try
        {
            $pdo = $table->getConnection()->getDbh();

            $stmt = $pdo->prepare("DELETE FROM ".$edgeTable->getTableName()."
                WHERE column_name = '".$invoker->column_name."' AND node_from IN
                (SELECT f.id FROM ".$table->getTableName()." AS f
                WHERE f.relation_id = ".$invoker->relation_id.")");

            $stmt->execute();

            if(is_array($references))
            {
                foreach($references as $reference)
                {
                    $referenceId = str_ireplace('r', '', $reference);

                    $formulatedColumn = $table->getByRelationIdAndColumnName($referenceId, $invoker->column_name);

                    if(!$formulatedColumn->isNew())
                    {
                        $this->insertReference($invoker, $formulatedColumn->id);
                    }
                }
            }

            $this->updateReferencedNodesFinalValue($invoker);
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    private function insertReference($invoker, $nodeTo)
    {
        $table = $invoker->getTable();
        $modelName = $table->getComponentName();

        $edgeModelName = str_replace('FormulatedColumn', 'Edge', $modelName);
        $edgeTable = Doctrine_Core::getTable($edgeModelName);

        $pdo = $table->getConnection()->getDbh();

        $stmt = $pdo->prepare("WITH RECURSIVE p(id) AS (
            SELECT node_to
                FROM ".$edgeTable->getTableName()."
                WHERE node_from = ".$nodeTo." AND column_name = '".$invoker->column_name."'
                AND deleted_at IS NULL
            UNION
            SELECT node_to
                FROM p, ".$edgeTable->getTableName()." d
                WHERE p.id = d.node_from AND d.column_name = '".$invoker->column_name."'
                AND d.deleted_at IS NULL
            )
        SELECT * FROM p WHERE id = ".$invoker->id);

        $stmt->execute();

        $count = $stmt->rowCount();

        if($count == 0 and $nodeTo != $invoker->id)
        {
            $edge = new $edgeModelName();
            $edge->node_to = $nodeTo;
            $edge->node_from = $invoker->id;
            $edge->column_name = $invoker->column_name;
            $edge->save();
        }
        else//Circular dependencies detected
        {
            $invoker->final_value = 0;
            //we update and save the final value using sql since we cannot call save function here or we'll end up with death loop
            Doctrine_Query::create()
                ->update($modelName)
                ->set('final_value', 0)
                ->where('id = ?',$invoker->id)
                ->execute();
        }
    }

    private function updateReferencedNodesFinalValue($invoker)
    {
        $nodes = $invoker->getNodesRelatedByColumnName($invoker->column_name);

        $table = $invoker->getTable();

        if(is_array($nodes))
        {
            foreach($nodes as $node)
            {
                if($referencedNode = $table->find($node['node_from']))
                {
                    $referencedNode->setFormula($referencedNode->value);
                    $referencedNode->save();
                }
            }
        }
    }

    private function hasReferencesInFormula($formula)
    {
        $pattern = '/r[\d{1,}]+/i';

        $match = preg_match_all($pattern, $formula, $matches, PREG_PATTERN_ORDER);

        return $match ? $matches[0] : false;
    }
}