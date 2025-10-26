<?php
class BQElement extends BaseBQElement
{
    public function moveTo($priority, $lastPosition=false)
    {
        $priority = $lastPosition ? $priority+1 : $priority;

        $con = $this->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $this->priority = $priority;
            $this->save();

            if(!$lastPosition)
            {
                $this->updatePriority($priority, $this->id);
            }

            $con->commit();

            return true;
        }
        catch(Exception $e)
        {
            $con->rollback();
            throw $e;
        }

    }

    public function copyTo($targetItem, $lastPosition=false)
    {
        $con = $this->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $priorityToUpdate = $lastPosition ? $targetItem->priority + 1 : $targetItem->priority;

            $cloneElement = $this->copy();
            $cloneElement->priority = $priorityToUpdate;
            $cloneElement->save($con);

            if(!$lastPosition)
            {
                $this->updatePriority($priorityToUpdate, $cloneElement->id);
            }

            $roots = Doctrine_Core::getTable('BQItem')
                ->createQuery('i')
                ->select('i.*')
                ->where('i.root_id = i.id')
                ->addWhere('i.element_id = ?', $this->id)
                ->orderBy('i.priority ASC')
                ->execute();

            $scopeContainer = new BQItem();

            foreach($roots as $root)
            {
                $newRoot = $root->copy();

                $newRoot->element_id = $cloneElement->id;
                $newRoot->save($con);

                $children = Doctrine_Core::getTable('BQItem')
                    ->createQuery('i')
                    ->select('i.*')
                    ->where('i.root_id = ?', $root->id)
                    ->addWhere('i.lft > ? AND i.rgt < ?', array($root->lft, $root->rgt))
                    ->orderBy('i.lft ASC')
                    ->execute();

                foreach($children as $child)
                {
                    $newChild = $child->copy();
                    $newChild->root_id = $newRoot->id;
                    $newChild->element_id = $cloneElement->id;
                    $newChild->save($con);
                    $newChild->refresh();

                    array_push($scopeContainer->itemContainerAfterCopy, array(
                        'id' => $newChild->id,
                        'origin' => $child->id
                    ));

                    $newChild->copyFormulatedColumnsFromItem($child, $scopeContainer);
                    $newChild->copyBuildUpRatesFromItem($child);

                    unset($newChild, $child);
                }

                $newRoot->root_id = $newRoot->id;
                $newRoot->save($con);
                $newRoot->refresh();

                array_push($scopeContainer->itemContainerAfterCopy, array(
                    'id' => $newRoot->id,
                    'origin' => $root->id
                ));

                $newRoot->copyFormulatedColumnsFromItem($root, $scopeContainer);
                $newRoot->copyBuildUpRatesFromItem($root);

                $newRoot->free();
                unset($newRoot, $root);
            }

            $scopeContainer->updateItemRowLinkingAfterCopy();

            $con->commit();

            unset($scopeContainer);

            return $cloneElement;
        }
        catch(Exception $e)
        {
            $con->rollback();
            throw $e;
        }
    }

    private function updatePriority($priority, $excludeId)
    {
        $records = DoctrineQuery::create()->select('t.id')
            ->from('BQElement t')
            ->where('t.library_id = ?', $this->library_id)
            ->andWhere('t.priority >= ?',$priority)
            ->addOrderBy('t.priority ASC')
            ->execute();

        $priorityToUpdate = $priority + 1;

        foreach($records as $record)
        {
            if($record->id != $excludeId){
                $record->priority = $priorityToUpdate;
                $record->save();
            }
            $priorityToUpdate++;
        }
    }

    public static function getMaxPriorityByLibraryId($libraryId)
    {
        $queryResult = DoctrineQuery::create()->select('max(e.priority)')
            ->from('BQElement e')
            ->where('e.library_id = ?', $libraryId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        return $queryResult['max'];
    }
}
