<?php

class Doctrine_Template_FormulatedColumn extends Doctrine_Template
{
    public function setTableDefinition()
    {
        $this->hasColumn('relation_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('column_name', 'string', 50, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 50,
        ));
        $this->hasColumn('value', 'string', null, array(
            'type' => 'string',
            'notnull' => false,
        ));
        $this->hasColumn('final_value', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 5,
            'notnull' => false
        ));

        $this->addListener(new Doctrine_Template_Listener_FormulatedColumn());
    }

    public function getByRelationIdAndColumnNameTableProxy($relationId, $columnName)
    {
        $relationId = preg_replace('/[^0-9]/', '', $relationId);
        $relationId = (int)$relationId;

        if(empty($relationId))
        {
            return null;
        }

        $record = $this->getInvoker();

        $table = $record->getTable();
        $modelName = $table->getComponentName();

        $query = $table->createQuery();

        $rootAlias = $query->getRootAlias();

        $query->select($rootAlias.'.*')
            ->where($rootAlias.'.relation_id = ?', $relationId)
            ->andWhere($rootAlias.'.column_name = ?', $columnName)
            ->andWhere($rootAlias.'.deleted_at IS NULL')
            ->limit(1);

        if($result = $query->fetchOne())
        {
            return $result;
        }
        else
        {
            $r = new $modelName();
            $r->relation_id = $relationId;
            $r->column_name = $columnName;

            return $r;
        }
    }

    public function setFormula($value)
    {
        $evaluator = new EvalMath(true, true);
        $evaluator->suppress_errors = true;

        $record = $this->getInvoker();

        $references = $this->hasReferencesInFormula($value);

        $mathExp = $value;

        if(is_array($references))
        {
            try
            {
                foreach($references as $k => $reference)
                {
                    $reference = preg_replace("/[^a-zA-Z0-9]/", "", $reference);
                    $itemId = str_ireplace('r', '', $reference);

                    if($column = $this->getByRelationIdAndColumnNameHydrateArray($itemId, $record->column_name))
                    {
                        $finalValue = $column['final_value'];
                    }
                    else
                    {
                        $finalValue = 0;
                    }

                    $mathExp = str_replace($reference, $finalValue, $mathExp);

                    unset($references[$k], $reference, $column);
                }
            }
            catch(Exception $e)
            {
                throw $e;
            }
        }

        $evaluatedValue = $evaluator->evaluate($mathExp);

        $record->value = $value;

        if(strlen($value) > 0)
        {
            if($record->column_name == BillItem::FORMULATED_COLUMN_RATE)
            {
                $evaluatedValue = $evaluatedValue ? number_format($evaluatedValue, 2, '.', '') : 0;
            }

            $record->final_value = $evaluatedValue ? $evaluatedValue : 0;
        }
        else
        {
            $record->final_value = NULL;
        }

    }

    public function hasCellReference()
    {
        $record = $this->getInvoker();
        $pattern = '/r[\d{1,}]+/i';
        $match = strlen($record->value) > 0 ? preg_match_all($pattern, $record->value, $matches, PREG_PATTERN_ORDER) : false;

        return $match ? true : false;
    }

    public function hasFormula()
    {
        $record = $this->getInvoker();
        return $record->value != $record->final_value ? true : false;
    }

    private function checkReferenceValidity($reference, $columnName)
    {
        $itemId = str_ireplace('r', '', $reference);

        return $this->getByRelationIdAndColumnNameHydrateArray($itemId, $columnName) ? false : true;
    }

    public function checkReferenceValidityTableProxy($reference, $columnName)
    {
        return $this->checkReferenceValidity($reference, $columnName);
    }

    public function getNodesRelatedByColumnName($columnName)
    {
        $record = $this->getInvoker();
        $nodes = array();

        if($record->id > 0)
        {
            $nodes = $record->getTable()->getNodesRelatedByRelationIdAndColumnName($record->id, $columnName);
        }

        return $nodes;
    }

    public function getNodesRelatedByRelationIdAndColumnNameTableProxy($relationId, $columnName)
    {
        $nodes = array();

        if($relationId > 0)
        {
            $record = $this->getInvoker();
            $table = $record->getTable();

            $edgeTableName = str_replace('formulated_columns', 'edges', $table->getTableName());
            $pdo = $table->getConnection()->getDbh();

            /*
            * http://techportal.inviqa.com/2009/09/07/graphs-in-the-database-sql-meets-social-networks/
            */
            $stmt = $pdo->prepare("WITH RECURSIVE transitive_closure(node_to, node_from, column_name, distance, path_string) AS
            ( SELECT node_to, node_from, column_name, 1 AS distance, node_to || '.' || node_from || '.' AS path_string FROM ".$edgeTableName."
            WHERE node_to = ".$relationId." AND column_name = '".$columnName."' AND deleted_at IS NULL
            UNION ALL
            SELECT tc.node_to, e.node_from, tc.column_name, tc.distance + 1, tc.path_string || e.node_from || '.' AS path_string
            FROM ".$edgeTableName." AS e JOIN transitive_closure AS tc ON e.node_to = tc.node_from
            WHERE tc.path_string NOT LIKE '%' || e.node_from || '.%' AND e.deleted_at IS NULL)

            SELECT * FROM transitive_closure
            ORDER BY node_to, node_from, distance;");

            $stmt->execute();

            $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $nodes;
    }

    private function getByRelationIdAndColumnNameHydrateArray($relationId, $columnName)
    {
        $relationId = preg_replace('/[^0-9]/', '', $relationId);
        $relationId = (int)$relationId;
        
        if(empty($relationId))
        {
            return null;
        }

        $record = $this->getInvoker();

        $table = $record->getTable();

        $query = $table->createQuery();

        $rootAlias = $query->getRootAlias();

        return $query->select($rootAlias.'.*')
            ->where($rootAlias.'.relation_id = ?', $relationId)
            ->andWhere($rootAlias.'.column_name = ?', $columnName)
            ->andWhere($rootAlias.'.deleted_at IS NULL')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->limit(1)
            ->fetchOne();
    }

    private function hasReferencesInFormula($formula)
    {
        $pattern = '/r[\d{1,}]+/i';

        $match = preg_match_all($pattern, $formula, $matches, PREG_PATTERN_ORDER);

        return $match ? $matches[0] : false;
    }

    public function hasReferencesInFormulaTableProxy($formula)
    {
        return $this->hasReferencesInFormula($formula);
    }
}
