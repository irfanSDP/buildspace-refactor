<?php

/**
 * location actions.
 *
 * @package    buildspace
 * @subpackage location
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class locationActions extends BaseActions
{
    public function executeGetProjectStructureLocationCodes(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $pdo  = $project->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT l.id, l.description, l.lft, l.rgt, l.level, l.project_structure_id, l.updated_at
            FROM " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " l
            WHERE l.project_structure_id = " . $project->id . " AND l.deleted_at IS NULL
            ORDER BY l.priority, l.lft, l.level");

        $stmt->execute();

        $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $locations as $key => $location )
        {
            $locations[$key]['updated_at']  = date('d/m/Y H:i', strtotime($location['updated_at']));
            $locations[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($locations, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'level'       => 0,
            'lft'         => 0,
            'rgt'         => 0,
            'updated_at'  => '-',
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $locations
        ));
    }

    public function executeProjectStructureLocationCodeUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $location = Doctrine_Core::getTable('ProjectStructureLocationCode')->find(intval($request->getParameter('id'))) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $form = new BaseForm();
        $con = $project->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

            if ( $fieldName )
            {
                $columns = array_keys(ProjectStructureLocationCodeTable::getInstance()->getColumns());
                if ( in_array($fieldName, $columns))
                {
                    $location->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }

            $location->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $location->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'               => $location->id,
                'description'      => $location->description,
                'updated_at'       => date('d/m/Y H:i', strtotime($location->updated_at)),
                'level'            => $location->level,
                'lft'              => $location->lft,
                'rgt'              => $location->rgt,
                '_csrf_token'      => $form->getCSRFToken()
            )
        ));
    }

    public function executeProjectStructureLocationCodeAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $items    = array();
        $nextItem = null;
        $con      = $project->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $asRoot   = true;

            $location = new ProjectStructureLocationCode();

            $location->project_structure_id = $project->id;

            $previousLocation = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ProjectStructureLocationCode')->find(intval($request->getParameter('prev_item_id'))) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                if ( $fieldName )
                {
                    $columns = array_keys(ProjectStructureLocationCodeTable::getInstance()->getColumns());
                    if ( in_array($fieldName, $columns))
                    {
                        $location->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                    }
                }

                $priority = 0;

                if ( $previousLocation )
                {
                    if ( $previousLocation->node->isRoot() )
                    {
                        $priority = $previousLocation->priority + 1;
                    }
                    else
                    {
                        $asRoot = false;
                        $location->node->insertAsNextSiblingOf($previousLocation);

                        $priority = $previousLocation->priority;
                    }
                }

                $location->priority = $priority;

                $location->save($con);
            }
            else
            {
                $this->forward404Unless($nextLocation = Doctrine_Core::getTable('ProjectStructureLocationCode')->find(intval($request->getParameter('before_id'))));

                if ( $nextLocation->node->isRoot() )
                {
                    $priority = $nextLocation->priority;

                    $location->priority = $priority;

                    $location->save($con);

                    $asRoot = true;
                }
                else
                {
                    $asRoot = false;

                    $location->node->insertAsPrevSiblingOf($nextLocation);
                    $location->priority = $nextLocation->priority;
                }
            }

            if($asRoot)
            {
                $node = $location->node;

                if ( $node->isValidNode() )
                {
                    $node->makeRoot($location->id);
                }
                else
                {
                    $location->getTable()->getTree()->createRoot($location);
                }

                $location->updateRootPriority($priority, $location->id);
            }

            $location->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $form = new BaseForm();

            $location->refresh();

            array_push($items, array(
                'id'          => $location->id,
                'description' => $location->description,
                'updated_at'  => date('d/m/Y H:i', strtotime($location->updated_at)),
                'lft'         => $location->lft,
                'rgt'         => $location->rgt,
                'level'       => $location->level,
                '_csrf_token' => $form->getCSRFToken()
            ));

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'updated_at'  => '-',
                    'lft'         => 0,
                    'rgt'         => 0,
                    'level'       => 0,
                    '_csrf_token' => $form->getCSRFToken()
                ));
            }
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeProjectStructureLocationCodeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $location = Doctrine_Core::getTable('ProjectStructureLocationCode')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;

        $con = $location->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('l.id')
                ->from('ProjectStructureLocationCode l')
                ->where('l.root_id = ?', $location->root_id)
                ->andWhere('l.project_structure_id = ?', $location->project_structure_id)
                ->andWhere('l.lft >= ? AND l.rgt <= ?', array( $location->lft, $location->rgt ))
                ->addOrderBy('l.priority, l.lft, l.level')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $location->delete($con);
            
            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeProjectStructureLocationCodeIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $location = Doctrine_Core::getTable('ProjectStructureLocationCode')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();

        try
        {
            if ( $location->indent() )
            {
                $data['id']         = $location->id;
                $data['level']      = $location->level;
                $data['lft']        = $location->lft;
                $data['rgt']        = $location->rgt;
                $data['updated_at'] = date('d/m/Y H:i', strtotime($location->updated_at));

                $children = DoctrineQuery::create()->select('l.id, l.level, l.lft, l.rgt, l.updated_at')
                    ->from('ProjectStructureLocationCode l')
                    ->where('l.root_id = ?', $location->root_id)
                    ->andWhere('l.project_structure_id = ?', $location->project_structure_id)
                    ->andWhere('l.lft > ? AND l.rgt < ?', array( $location->lft, $location->rgt ))
                    ->addOrderBy('l.priority, l.lft, l.level')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                foreach($children as $key => $child)
                {
                    $children[$key]['updated_at'] = date('d/m/Y H:i', strtotime($child['updated_at']));
                }

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeProjectStructureLocationCodeOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $location = Doctrine_Core::getTable('ProjectStructureLocationCode')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();

        try
        {
            if ( $location->outdent() )
            {
                $data['id']         = $location->id;
                $data['level']      = $location->level;
                $data['lft']        = $location->lft;
                $data['rgt']        = $location->rgt;
                $data['updated_at'] = date('d/m/Y H:i', strtotime($location->updated_at));

                $children = DoctrineQuery::create()->select('l.id, l.level, l.lft, l.rgt, l.updated_at')
                    ->from('ProjectStructureLocationCode l')
                    ->where('l.root_id = ?', $location->root_id)
                    ->andWhere('l.project_structure_id = ?', $location->project_structure_id)
                    ->andWhere('l.lft > ? AND l.rgt < ?', array( $location->lft, $location->rgt ))
                    ->addOrderBy('l.priority, l.lft, l.level')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                foreach($children as $key => $child)
                {
                    $children[$key]['updated_at'] = date('d/m/Y H:i', strtotime($child['updated_at']));
                }

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeProjectStructureLocationCodePaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $location = Doctrine_Core::getTable('ProjectStructureLocationCode')->find($request->getParameter('id'))
        );

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        if(!$targetItem = Doctrine_Core::getTable('ProjectStructureLocationCode')->find(intval($request->getParameter('target_id'))))
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('ProjectStructureLocationCode')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetItem->root_id == $location->root_id and $targetItem->lft >= $location->lft and $targetItem->rgt <= $location->rgt )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $location->moveTo($targetItem, $lastPosition);

                    $children = Doctrine_Query::create()->select('l.id, l.level, l.lft, l.rgt, l.updated_at')
                        ->from('ProjectStructureLocationCode l')
                        ->where('l.root_id = ?', $location->root_id)
                        ->andWhere('l.project_structure_id = ?', $location->project_structure_id)
                        ->andWhere('l.lft > ? AND l.rgt < ?', array( $location->lft, $location->rgt ))
                        ->addOrderBy('l.priority, l.lft, l.level')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['updated_at']  = date('d/m/Y H:i', strtotime($child['updated_at']));
                    }

                    $data['id']         = $location->id;
                    $data['level']      = $location->level;
                    $data['lft']        = $location->lft;
                    $data['rgt']        = $location->rgt;
                    $data['updated_at'] = date('d/m/Y H:i', strtotime($location->updated_at));

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $newItem = $location->copyTo($targetItem, $lastPosition);

                    $form     = new BaseForm();
                    $children = Doctrine_Query::create()
                        ->select('l.id, l.description, l.lft, l.rgt, l.level, l.project_structure_id, l.updated_at')
                        ->from('ProjectStructureLocationCode l')
                        ->where('l.root_id = ?', $newItem->root_id)
                        ->andWhere('l.project_structure_id = ?', $newItem->project_structure_id)
                        ->andWhere('l.lft > ? AND l.rgt < ?', array( $newItem->lft, $newItem->rgt ))
                        ->addOrderBy('l.priority, l.lft, l.level')
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

                    foreach ( $children as $key => $child )
                    {
                        $children[$key]['updated_at']  = date('d/m/Y H:i', strtotime($child['updated_at']));
                        $children[$key]['_csrf_token'] = $form->getCSRFToken();
                    }

                    $data['id']                   = $newItem->id;
                    $data['description']          = $newItem->description;
                    $data['project_structure_id'] = $newItem->project_structure_id;
                    $data['lft']                  = $newItem->lft;
                    $data['rgt']                  = $newItem->rgt;
                    $data['level']                = $newItem->level;
                    $data['updated_at']           = date('d/m/Y H:i', strtotime($newItem->updated_at));
                    $data['_csrf_token']          = $form->getCSRFToken();

                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }

                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => $children ));
    }

    public function executeGetPredefinedLocationCode(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->hasParameter('t')
        );

        $codes = DoctrineQuery::create()->select('c.id, c.name, c.type')
            ->from('PreDefinedLocationCode c')
            ->where('c.type = ?', intval($request->getParameter('t')))
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $codes
        ));
    }

    public function executeGetLocationCodeByLevel(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->hasParameter('t') and
            $request->hasParameter('s') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $parentIds = $request->getParameter('parent_id');

        $sql = null;

        switch ($request->getParameter('t')){
            case LocationAssignment::SEQUENCE_TYPE_TRADE:
                if(intval($request->getParameter('s')) == 0)
                {
                    $sql = "SELECT DISTINCT loc.id, loc.name, loc.priority, loc.root_id, loc.lft, loc.rgt, loc.level
                        FROM " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " loc
                        WHERE loc.id = loc.root_id
                        AND loc.deleted_at IS NULL
                        ORDER BY loc.priority ASC";
                }
                else
                {
                    if(is_array($parentIds) && !empty($parentIds))
                    {
                        $sql = "SELECT DISTINCT loc.id, loc.name, loc.priority, loc.root_id, loc.lft, loc.rgt, loc.level
                        FROM " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " loc
                        JOIN " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " loc_p ON
                        (loc.lft BETWEEN loc_p.lft AND loc_p.rgt AND loc.level = (loc_p.level + 1) AND loc.root_id = loc_p.root_id AND loc_p.deleted_at IS NULL)
                        WHERE loc_p.id IN (".implode(',', array_fill(0, count($parentIds), '?')).")
                        AND loc.deleted_at IS NULL
                        ORDER BY loc.priority, loc.lft, loc.level ASC";
                    }
                }

                break;
            case LocationAssignment::SEQUENCE_TYPE_LOCATION:
                if(intval($request->getParameter('s')) == 0)
                {
                    $sql = "SELECT DISTINCT loc.id, loc.description AS name, loc.priority, loc.root_id, loc.lft, loc.rgt, loc.level
                        FROM " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " loc
                        WHERE loc.id = loc.root_id AND loc.project_structure_id = ".$project->id."
                        AND loc.deleted_at IS NULL
                        ORDER BY loc.priority ASC";
                }
                else
                {
                    if(is_array($parentIds) && !empty($parentIds))
                    {
                        $sql = "SELECT DISTINCT loc.id, loc.description AS name, loc.priority, loc.root_id, loc.lft, loc.rgt, loc.level
                        FROM " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " loc
                        JOIN " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " loc_p ON
                        (loc.lft BETWEEN loc_p.lft AND loc_p.rgt AND loc.level = (loc_p.level + 1) AND loc.root_id = loc_p.root_id AND loc_p.deleted_at IS NULL)
                        WHERE loc_p.id IN (".implode(',', array_fill(0, count($parentIds), '?')).")
                        AND loc_p.project_structure_id = ".$project->id." AND loc.deleted_at IS NULL
                        ORDER BY loc.priority, loc.lft, loc.level ASC";
                    }
                }

                break;
            default:
                throw new Exception("Invalid type from request parameter");

        }

        $codes = array();

        if($sql)
        {
            $stmt = $pdo->prepare($sql);

            if(is_array($parentIds) && !empty($parentIds))
            {
                $stmt->execute($parentIds);
            }
            else
            {
                $stmt->execute();
            }

            $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $codes
        ));
    }

    public function executeGetLocationCodeLevels(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $requestParams = $request->getParameterHolder()->getAll();

        $i = new ArrayIterator($requestParams);
        $predefinedLocationCodesFilter = array();
        $projectStructureLocationCodesFilter = array();
        $columnTypesFilter = array();

        while ($i->valid()) {
            if (strpos($i->key(), 't-') === 0) {
                $level = substr($i->key(), strlen('t-'));
                $predefinedLocationCodesFilter[intval($level)] = explode(",", $i->current());
            }

            if (strpos($i->key(), 'l-') === 0) {
                $level = substr($i->key(), strlen('l-'));
                $projectStructureLocationCodesFilter[intval($level)] = explode(",", $i->current());
            }

            if($i->key() == 'column_type') {
                $columnTypesFilter = explode(",", $i->current());
            }

            $i->next();
        }

        // we only need the last level from the location request params
        // query will return location codes children based on the last location levels from the request
        if(!empty($predefinedLocationCodesFilter))
        {
            end($predefinedLocationCodesFilter);
            $predefinedLocationCodesFilter = array(
                key($predefinedLocationCodesFilter) => end($predefinedLocationCodesFilter)
            );
        }

        if(!empty($projectStructureLocationCodesFilter))
        {
            end($projectStructureLocationCodesFilter);
            $projectStructureLocationCodesFilter = array(
                key($projectStructureLocationCodesFilter) => end($projectStructureLocationCodesFilter)
            );
        }

        $records = PreDefinedLocationCodeTable::getAssignedCodesByProjectId($project->id);

        $predefinedLocationCodes = array();

        $filteredLocationRootIds = array();

        $takenCodes = array();

        foreach($records as $idx => $record)
        {
            foreach($record as $idx2 => $code)
            {
                if(!array_key_exists($code['id'], $takenCodes))
                {
                    if(!array_key_exists($code['level'], $predefinedLocationCodes))
                        $predefinedLocationCodes[$code['level']] = array();

                    $predefinedLocationCodes[$code['level']][] = $code;

                    $takenCodes[$code['id']] = $code;

                    unset($record[$idx2]);

                    if(!empty($predefinedLocationCodesFilter) and array_key_exists($code['level'], $predefinedLocationCodesFilter) &&
                        in_array($code['id'], $predefinedLocationCodesFilter[$code['level']]))
                    {
                        if(!array_key_exists($code['root_id'], $filteredLocationRootIds))
                            $filteredLocationRootIds[$code['root_id']] = array();

                        $filteredLocationRootIds[$code['root_id']][] = array(
                            'id'    => $code['id'],
                            'level' => $code['level'],
                            'lft'   => $code['lft'],
                            'rgt'   => $code['rgt']
                        );
                    }
                }
            }

            unset($records[$idx]);
        }

        unset($takenCodes);

        if(!empty($predefinedLocationCodesFilter))
        {
            $filteredRecords = array();
            $takenCodes = array();

            foreach($predefinedLocationCodes as $records)
            {
                foreach($records as $record)
                {
                    if(!array_key_exists($record['id'], $takenCodes) && array_key_exists($record['root_id'], $filteredLocationRootIds))
                    {
                        foreach($filteredLocationRootIds[$record['root_id']] as $data)
                        {
                            if(!array_key_exists($record['id'], $takenCodes) && $record['lft'] > $data['lft'] and $record['rgt'] < $data['rgt'] and
                                $record['level'] > $data['level'])
                            {
                                if(!array_key_exists($record['level'], $filteredRecords))
                                    $filteredRecords[$record['level']] = array();

                                $filteredRecords[$record['level']][] = $record;

                                $takenCodes[$record['id']] = $record;
                            }
                        }
                    }
                }
            }

            $predefinedLocationCodes = $filteredRecords;

            unset($takenCodes, $filteredRecords);
        }

        $records = ProjectStructureLocationCodeTable::getAssignedCodesByProjectId($project->id);

        $projectStructureLocationCodes = array();

        $filteredLocationRootIds = array();

        $takenCodes = array();

        foreach($records as $idx => $record)
        {
            foreach($record as $idx2 => $code)
            {
                if(!array_key_exists($code['id'], $takenCodes))
                {
                    if(!array_key_exists($code['level'], $projectStructureLocationCodes))
                        $projectStructureLocationCodes[$code['level']] = array();

                    if(!array_key_exists('name', $code))
                        $code['name'] = $code['description'];

                    $projectStructureLocationCodes[$code['level']][] = $code;

                    $takenCodes[$code['id']] = $code;

                    unset($record[$idx2]);

                    if(!empty($projectStructureLocationCodesFilter) and array_key_exists($code['level'], $projectStructureLocationCodesFilter) &&
                        in_array($code['id'], $projectStructureLocationCodesFilter[$code['level']]))
                    {
                        if(!array_key_exists($code['root_id'], $filteredLocationRootIds))
                            $filteredLocationRootIds[$code['root_id']] = array();

                        $filteredLocationRootIds[$code['root_id']][] = array(
                            'id'    => $code['id'],
                            'level' => $code['level'],
                            'lft'   => $code['lft'],
                            'rgt'   => $code['rgt']
                        );
                    }
                }

            }

            unset($records[$idx]);
        }

        unset($takenCodes);

        if(!empty($projectStructureLocationCodesFilter))
        {
            $filteredRecords = array();
            $takenCodes = array();

            foreach($projectStructureLocationCodes as $records)
            {
                foreach($records as $record)
                {
                    if(!array_key_exists($record['id'], $takenCodes) && array_key_exists($record['root_id'], $filteredLocationRootIds))
                    {
                        foreach($filteredLocationRootIds[$record['root_id']] as $data)
                        {
                            if(!array_key_exists($record['id'], $takenCodes) && $record['lft'] > $data['lft'] and $record['rgt'] < $data['rgt'] and
                                $record['level'] > $data['level'])
                            {
                                if(!array_key_exists($record['level'], $filteredRecords))
                                    $filteredRecords[$record['level']] = array();

                                $filteredRecords[$record['level']][] = $record;

                                $takenCodes[$record['id']] = $record;
                            }
                        }
                    }
                }
            }

            $projectStructureLocationCodes = $filteredRecords;

            unset($takenCodes, $filteredRecords);
        }

        $columnTypes = BillColumnSettingTable::getAllBillColumnSettingsInProject($project);

        $standardClaimTypeReferences = PostContractStandardClaimTypeReferenceTable::getStandardClaimTypeReferences($project);

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT b.id AS bill_id, b.title AS bill_title, l.id AS location_assignment_id, l.pre_defined_location_code_id, l.project_structure_location_code_id, c.id, c.description, c.type, c.uom_id, c.lft, c.level, uom.name AS uom_name, uom.symbol AS uom_symbol
            FROM " . LocationAssignmentTable::getInstance()->getTableName() . " l
            JOIN " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " plc ON l.pre_defined_location_code_id = plc.id
            JOIN " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " pslc ON l.project_structure_location_code_id = pslc.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " c ON c.id = l.bill_item_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON e.project_structure_id = b.id
            WHERE b.root_id = " . $project->id." AND pslc.project_structure_id = b.root_id");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $columnUnits = array();
        foreach($columnTypes as $key => $columnType)
        {
            // Remove column types that are not tagged to any item.
            if(!array_key_exists($columnType['bill_id'], $items))
            {
                unset($columnTypes[$key]);
                continue;
            }

            // Filter by column types only if filter is applied.
            if(!empty($columnTypesFilter))
            {
                if(!in_array($columnType['id'], $columnTypesFilter)) continue;
            }

            for($i = 1; $i <= $columnType['quantity']; $i++)
            {
                $columnUnits[] = array(
                    'id'                       => "{$columnType['bill_id']}-{$columnType['id']}-{$i}",
                    'counter'                  => $i,
                    'bill_id'                  => $columnType['bill_id'],
                    'bill_column_structure_id' => $columnType['id'],
                    'name'                     => $standardClaimTypeReferences[ $columnType['id'] ][ $i ]['new_name'] ?? "Unit {$i}",
                );
            }
        }

        // Re-index the array so that json can be rendered properly.
        $columnTypes = array_values($columnTypes);

        return $this->renderJson(array(
            'predefined_location_codes'        => $predefinedLocationCodes,
            'project_structure_location_codes' => $projectStructureLocationCodes,
            'column_types'                     => $columnTypes,
            'column_units'                     => $columnUnits,
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id'))
        );

        $pdo = $element->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT c.id, c.description, c.type, c.uom_id, c.lft, c.level,
            c.uom_id, uom.symbol AS uom_symbol
            FROM " . BillItemTable::getInstance()->getTableName() . " c
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
            WHERE c.element_id = " . $element->id . "
            AND c.deleted_at IS NULL AND c.project_revision_deleted_at IS NULL
            AND uom.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT c.id, c.name, c.quantity, c.remeasurement_quantity_enabled, c.use_original_quantity
            FROM " . BillColumnSettingTable::getInstance()->getTableName() . " c
            WHERE c.project_structure_id = ".$element->project_structure_id."
            AND c.deleted_at IS NULL ORDER BY c.id ASC");

        $stmt->execute();

        $billColumnSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($billItems))
        {
            $stmt = $pdo->prepare("SELECT l.bill_column_setting_id, l.use_original_qty
            FROM " . LocationBQSettingTable::getInstance()->getTableName() . " l
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bc ON l.bill_column_setting_id = bc.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON bc.project_structure_id = b.id
            WHERE b.id = ".$element->project_structure_id." AND b.type = ".ProjectStructure::TYPE_BILL." AND b.deleted_at IS NULL
            AND bc.deleted_at IS NULL
            ORDER BY b.priority, b.lft, b.level, bc.id");

            $stmt->execute();

            $locationBQSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $quantities = array();

            foreach($billColumnSettings as $billColumnSetting)
            {
                $quantityFieldName = (array_key_exists($billColumnSetting['id'], $locationBQSettings) && !$locationBQSettings[$billColumnSetting['id']]) ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT;

                $stmt = $pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value
                        FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                        JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id
                        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
                        WHERE e.id = " . $element->id . " AND r.bill_column_setting_id = " . $billColumnSetting['id'] . "
                        AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                        AND r.deleted_at IS NULL AND fc.deleted_at IS NULL
                        AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
                        AND e.deleted_at IS NULL");

                $stmt->execute();

                $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $quantities[$billColumnSetting['id']] = $data;
            }

            $stmt = $pdo->prepare("SELECT l.bill_column_setting_id, la.bill_item_id, SUM(COALESCE(l.percentage, 0)) AS percentage
            FROM " . LocationAssignmentTable::getInstance()->getTableName() . " la
            JOIN " . LocationBillItemQuantityProrateTable::getInstance()->getTableName() . " l ON l.location_assignment_id = la.id
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bc ON l.bill_column_setting_id = bc.id
            WHERE bc.project_structure_id = ".$element->project_structure_id."
            AND bc.deleted_at IS NULL
            GROUP BY l.bill_column_setting_id, la.bill_item_id");

            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $proratedRecords = array();

            foreach($records as $record)
            {
                if(!array_key_exists($record['bill_column_setting_id'], $proratedRecords))
                {
                    $proratedRecords[$record['bill_column_setting_id']] = array();
                }

                $proratedRecords[$record['bill_column_setting_id']][$record['bill_item_id']] = $record['percentage'];
            }

            $stmt = $pdo->prepare("SELECT DISTINCT l.bill_item_id
            FROM " . LocationAssignmentTable::getInstance()->getTableName() . " l
            JOIN ". BillItemTable::getInstance()->getTableName()." i ON l.bill_item_id = i.id
            WHERE i.element_id = ".$element->id." AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL");

            $stmt->execute();

            $billItemsWithLocations = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ( $billItems as $key => $billItem )
            {
                $billItems[$key]['type']         = (string) $billItem['type'];
                $billItems[$key]['uom_id']       = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
                $billItems[$key]['uom_symbol']   = $billItem['uom_id'] > 0 ? $billItem['uom_symbol'] : '';
                $billItems[$key]['has_location'] = in_array($billItem['id'], $billItemsWithLocations);
                $billItems[$key]['_csrf_token']  = $form->getCSRFToken();

                foreach($billColumnSettings as $billColumnSetting)
                {
                    $qty = 0;
                    $proratedPercentage = 0;

                    if(array_key_exists($billColumnSetting['id'], $quantities) and array_key_exists($billItem['id'], $quantities[$billColumnSetting['id']]))
                    {
                        $qty = $quantities[$billColumnSetting['id']][$billItem['id']];
                    }

                    if(array_key_exists($billColumnSetting['id'], $proratedRecords) and array_key_exists($billItem['id'], $proratedRecords[$billColumnSetting['id']]))
                    {
                        $proratedPercentage = $proratedRecords[$billColumnSetting['id']][$billItem['id']];
                    }

                    $proRatedQty = ($proratedPercentage != 0) ? ($proratedPercentage / 100) * $qty : 0;

                    $billItems[$key][$billColumnSetting['id'].'-qty'] = $qty;
                    $billItems[$key][$billColumnSetting['id'].'-percentage'] = $proratedPercentage;
                    $billItems[$key][$billColumnSetting['id'].'-prorated_qty'] = number_format($proRatedQty, 2, '.', '');
                }

                unset($billItem);
            }
        }

        $lastRow = array(
            'id'           => Constants::GRID_LAST_ROW,
            'description'  => '',
            'type'         => (string) BillItem::TYPE_WORK_ITEM,
            'uom_id'       => '-1',
            'uom_symbol'   => '',
            'has_location' => false,
            '_csrf_token'  => $form->getCSRFToken(),
            'level'        => 0
        );

        foreach($billColumnSettings as $billColumnSetting)
        {
            $lastRow[$billColumnSetting['id'].'-qty'] = 0;
            $lastRow[$billColumnSetting['id'].'-percentage'] = 0;
            $lastRow[$billColumnSetting['id'].'-prorated_qty'] = 0;
        }

        array_push($billItems, $lastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billItems
        ));
    }

    public function executeLocationAssignmentUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot() and
            $request->hasParameter('t') and
            $request->hasParameter('l') and
            $request->hasParameter('bid')
        );

        $items    = array();
        $errorMsg = null;

        $con = $project->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $billItemIds = $request->getParameter('bid');

            LocationAssignmentTable::createLocationAssignment($request->getParameter('t'), $request->getParameter('l'), $billItemIds, $con);

            $con->commit();

            $stmt = $con->getDbh()->prepare("SELECT DISTINCT l.bill_item_id
            FROM " . LocationAssignmentTable::getInstance()->getTableName() . " l
            WHERE l.bill_item_id IN (".implode(',', array_fill(0, count($billItemIds), '?')).")");

            $stmt->execute($billItemIds);

            $billItemsWithLocations = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($billItemIds as $billItemId)
            {
                $items[] = array(
                    'id' => $billItemId,
                    'has_location' => in_array($billItemId, $billItemsWithLocations)
                );
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetBillByLocations(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        ini_set('memory_limit','512M');

        $predefinedLocationCodes       = PreDefinedLocationCodeTable::getAssignedCodesByProjectId($project->id);
        $projectStructureLocationCodes = ProjectStructureLocationCodeTable::getAssignedCodesByProjectId($project->id);
        $form                          = new BaseForm();

        $items = ProjectStructureTable::getBillByLocations($project, $request->getParameterHolder()->getAll(), $request->getParameter("description"));

        $lastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'bill_title'            => "",
            'description'           => "",
            'uom'                   => "",
            'column_name'           => "",
            'column_unit'           => "",
            'qty'                   => 0,
            'percentage'            => 0,
            'prorated_qty'          => 0,
            'previous_percentage'   => 0,
            'previous_quantity'     => 0,
            'current_percentage'    => 0,
            'current_quantity'      => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_quantity'   => 0,
            'has_prorate_qty'       => false,
            '_csrf_token'           => $form->getCSRFToken()
        );

        foreach($predefinedLocationCodes as $records)
        {
            foreach ($records as $idx => $predefinedLocationCode)
            {
                $lastRow[$idx."-predefined_location_code"] = "";
            }
        }

        foreach($projectStructureLocationCodes as $records)
        {
            foreach($records as $idx => $projectStructureLocationCode)
            {
                $lastRow[$idx."-project_structure_location_code"] = "";
            }
        }

        $items[] = $lastRow;

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeExportProgressClaims(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot(),
            strlen($filename = $request->getParameter('filename')) > 0
        );

        $sfItemExport = new sfLocationProgressClaimReportGenerator($project, null, $filename);

        $items = ProjectStructureTable::getBillByLocations($project, $request->getParameterHolder()->getAll(), $request->getParameter("description"));

        $sfItemExport->setParameters($request->getParameterHolder()->getAll());

        $sfItemExport->process($items, false, $project->title, null, 'Progress Claim', null, null);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

    public function executeGetBillProperties(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        $tenderAlternative = $project->getAwardedTenderAlternative();
        $tenderAlternativeJoinSql = "";
        $tenderAlternativeWhereSql = "";

        if($tenderAlternative)
        {
            $tenderAlternativeJoinSql = " JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON ta.project_structure_id = p.id
                JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." tax ON tax.tender_alternative_id = ta.id AND tax.project_structure_id = bill.id ";

            $tenderAlternativeWhereSql = " AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL ";
        }

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT bill.id AS bill_id, s.title AS bill_title, c.id, c.name, c.quantity, c.remeasurement_quantity_enabled, c.use_original_quantity
        FROM ".ProjectStructureTable::getInstance()->getTableName()." p
        JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON bill.root_id = p.id
        JOIN ".BillColumnSettingTable::getInstance()->getTableName()." c ON c.project_structure_id = bill.id
        JOIN ".BillSettingTable::getInstance()->getTableName()." s ON s.project_structure_id = bill.id
        ".$tenderAlternativeJoinSql."
        WHERE p.id = ".$project->id." AND bill.type = ".ProjectStructure::TYPE_BILL."
        ".$tenderAlternativeWhereSql."
        AND p.deleted_at IS NULL AND c.deleted_at IS NULL AND bill.deleted_at IS NULL AND s.deleted_at IS NULL
        ORDER BY bill.priority, bill.lft, bill.level, c.id");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $form  = new BaseForm();
        $items = array();

        $stmt = $pdo->prepare("SELECT l.bill_column_setting_id, l.use_original_qty
        FROM ".LocationBQSettingTable::getInstance()->getTableName()." l
        JOIN ".BillColumnSettingTable::getInstance()->getTableName()." bc ON l.bill_column_setting_id = bc.id
        JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON bc.project_structure_id = bill.id
        WHERE bill.root_id = ".$project->id." AND bill.type = ".ProjectStructure::TYPE_BILL."
        AND bc.deleted_at IS NULL AND bill.deleted_at IS NULL
        ORDER BY bill.priority, bill.lft, bill.level, bc.id");

        $stmt->execute();

        $locationBillSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($records as $billId => $billColumnSettings)
        {
            $item = array();
            $item['bill_id']     = $billId;
            $item['_csrf_token'] = $form->getCSRFToken();

            foreach($billColumnSettings as $billColumnSetting)
            {
                $item['bill_title']             = $billColumnSetting['bill_title'];

                if(!array_key_exists($billColumnSetting['id'], $locationBillSettings) && !array_key_exists('location_bill_settings', $billColumnSetting))
                {
                    $useOriginalQty = true;
                }
                else
                {
                    $useOriginalQty = $locationBillSettings[$billColumnSetting['id']];
                }

                $billColumnSetting['location_bill_settings'] = array(
                    'use_original_qty' => $useOriginalQty,
                );

                $item['bill_column_settings'][] = $billColumnSetting;
            }

            $items[] = $item;
        }

        return $this->renderJson($items);
    }

    public function executeLocationBillSettingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('bid'))) and
            $bill->type == ProjectStructure::TYPE_BILL
        );

        $errorMsg = null;

        $con = $bill->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $parameters = $request->getParameterHolder()->getAll();

            foreach($parameters as $idx => $value)
            {
                if(substr($idx, 0, strlen("bill_column_setting-")) === "bill_column_setting-")
                {
                    $billColumnSettingId = substr($idx, strpos($idx, "-") + 1);

                    if(!$locationBQSetting = LocationBQSettingTable::getInstance()->find(intval($billColumnSettingId)))
                    {
                        $locationBQSetting = new LocationBQSetting();
                        $locationBQSetting->bill_column_setting_id = $billColumnSettingId;
                    }

                    $locationBQSetting->use_original_qty = intval($value);
                    $locationBQSetting->save($con);
                }
            }

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg));
    }

    public function executeBillItemQtyByLocationUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter("id")
        );

        $ids = explode("-", $request->getParameter("id"));

        $this->forward404Unless(count($ids) == 4 and
            $locationAssignment = LocationAssignmentTable::getInstance()->find(intval($ids[0])) and
            $billColumnSetting = BillColumnSettingTable::getInstance()->find(intval($ids[1])) and
            $billItem = BillItemTable::getInstance()->find(intval($ids[3]))
        );

        $typeUnit = intval($ids[2]);

        $errorMsg = null;

        $con = $billItem->getTable()->getConnection();

        $item = array();

        try
        {
            $con->beginTransaction();

            $pdo = $con->getDbh();

            $stmt = $pdo->prepare("SELECT l.use_original_qty
            FROM " . LocationBQSettingTable::getInstance()->getTableName() . " l
            WHERE l.bill_column_setting_id = ".$billColumnSetting->id);

            $stmt->execute();

            $locationBQSetting = $stmt->fetch(PDO::FETCH_ASSOC);

            $useOriginalQty = ($locationBQSetting) ? $locationBQSetting['use_original_qty'] : true;

            $quantityFieldName = $useOriginalQty ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

            $stmt = $pdo->prepare("SELECT COALESCE(fc.final_value, 0) AS value
            FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
            JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
            JOIN " . LocationAssignmentTable::getInstance()->getTableName() . " a ON a.bill_item_id = r.bill_item_id
            WHERE a.id = ".$locationAssignment->id." AND r.bill_column_setting_id = " . $billColumnSetting->id . "
            AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
            AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

            $stmt->execute();

            $billItemQty = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            $billItemQty = !empty($billItemQty) ? $billItemQty : 0;

            $locationBillItemQuantityProrate = Doctrine_Query::create()
                ->from('LocationBillItemQuantityProrate l')
                ->where('l.location_assignment_id = ?', $locationAssignment->id)
                ->andWhere('l.bill_column_setting_id = ?', $billColumnSetting->id)
                ->andWhere('l.unit = ?', $typeUnit)
                ->fetchOne();

            if(!$locationBillItemQuantityProrate)
            {
                $locationBillItemQuantityProrate = new LocationBillItemQuantityProrate();

                $locationBillItemQuantityProrate->location_assignment_id = $locationAssignment->id;
                $locationBillItemQuantityProrate->bill_column_setting_id = $billColumnSetting->id;
                $locationBillItemQuantityProrate->unit                   = $typeUnit;
            }

            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? floatval($request->getParameter('val')) : null;

            if ( $fieldName )
            {
                if($fieldName == "prorated_qty")
                {
                    $fieldValue = !empty($billItemQty) ? ($fieldValue / $billItemQty) * 100 : 0;
                    $fieldName = "percentage";
                }

                $columns = array_keys(LocationBillItemQuantityProrateTable::getInstance()->getColumns());
                if ( in_array($fieldName, $columns))
                {
                    $locationBillItemQuantityProrate->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }

            $locationBillItemQuantityProrate->save($con);

            $con->commit();

            if($fieldName != 'prorated_qty')
            {
                $item[$fieldName] = $locationBillItemQuantityProrate->{'get' . sfInflector::camelize($fieldName)}();
            }

            $proRatedQty = ($locationBillItemQuantityProrate->percentage != 0) ? ($locationBillItemQuantityProrate->percentage / 100) * $billItemQty : 0;
            $item["prorated_qty"] = number_format($proRatedQty, 2, '.', '');
            $item["percentage"]   = number_format($locationBillItemQuantityProrate->percentage, 5, '.', '');

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $item
        ));
    }

    public function executeQtyBulkUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter("ids")
        );

        $ids = explode(",", $request->getParameter("ids"));

        $locationAssignmentIds = array();
        $billColumnSettingIds  = array();
        $billItemIds           = array();

        foreach($ids as $id)
        {
            $parts = explode("-", $id);

            if(!array_key_exists($parts[0], $locationAssignmentIds))
            {
                $locationAssignmentIds[$parts[0]] = $parts[3];
            }

            if(!in_array($parts[1], $billColumnSettingIds))
            {
                $billColumnSettingIds[] = $parts[1];
            }

            if(!in_array($parts[3], $billItemIds))
            {
                $billItemIds[] = $parts[3];
            }
        }

        $con = BillItemTable::getInstance()->getConnection();
        $pdo = $con->getDbh();

        $items = array();

        try
        {
            $pdo->beginTransaction();

            $locationBQSettings = array();

            if(!empty($billColumnSettingIds))
            {
                $stmt = $pdo->prepare("SELECT l.bill_column_setting_id, l.use_original_qty
                FROM " . LocationBQSettingTable::getInstance()->getTableName() . " l
                WHERE l.bill_column_setting_id IN (".implode(',', $billColumnSettingIds).")");

                $stmt->execute();

                $locationBQSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            foreach($billColumnSettingIds as $billColumnSettingId)
            {
                //set the billColumnSetting info into $locationBQSetting if no record in locationBQSetting table
                // because we wil use $locationBQSetting for the saving process
                if(!array_key_exists($billColumnSettingId, $locationBQSettings))
                {
                    $locationBQSettings[$billColumnSettingId] = true;
                }
            }

            $quantities = array();

            foreach($locationBQSettings as $billColumnSettingId => $useOriginalQty)
            {
                $quantityFieldName = ($useOriginalQty) ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $stmt = $pdo->prepare("SELECT r.bill_column_setting_id, r.bill_item_id, COALESCE(fc.final_value, 0) AS value
                    FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
                    JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
                    WHERE r.bill_item_id IN (".implode(',', $billItemIds).") AND r.bill_column_setting_id = " . $billColumnSettingId . "
                    AND fc.column_name = '" . $quantityFieldName . "' AND fc.final_value <> 0
                    AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                $stmt->execute();

                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($records as $record)
                {
                    if(!array_key_exists($record['bill_column_setting_id'], $quantities))
                    {
                        $quantities[$record['bill_column_setting_id']] = array();
                    }

                    $quantities[$record['bill_column_setting_id']][$record['bill_item_id']] = $record['value'];
                }
            }

            $itemsToDelete = array();
            $insertValues  = array();
            $questionMarks = array();

            $isPercentage = strtolower($request->getParameter("field")) == "percentage" ? true : false;

            $val = floatval($request->getParameter("val"));

            $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

            foreach($ids as $id)
            {
                $parts = explode("-", $id);

                if(array_key_exists($parts[1], $quantities) &&
                    array_key_exists($parts[0], $locationAssignmentIds) &&
                    array_key_exists($locationAssignmentIds[$parts[0]], $quantities[$parts[1]]))
                {

                    $itemsToDelete[] = "(".intval($parts[0]).", ".intval($parts[1]).", ".intval($parts[2]).")";

                    $billItemQty = $quantities[$parts[1]][$locationAssignmentIds[$parts[0]]];

                    if(!$isPercentage)
                        $fieldValue = !empty($billItemQty) ? ($val / $billItemQty) * 100 : 0;
                    else
                        $fieldValue = $val;

                    $data = array(
                        intval($parts[0]),
                        intval($parts[1]),
                        intval($parts[2]),
                        $fieldValue,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                        $userId,
                        $userId
                    );

                    $insertValues = array_merge($insertValues, $data);

                    $questionMarks[] = '('.implode(',', array_fill(0, count($data), '?')).')';
                }
            }

            if(!empty($insertValues))
            {
                $stmt = $pdo->prepare("DELETE FROM ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()."
                    WHERE (location_assignment_id, bill_column_setting_id, unit) IN (".implode(',', $itemsToDelete).") ");

                $stmt->execute();

                $stmt = $pdo->prepare("INSERT INTO ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()."
                    (location_assignment_id, bill_column_setting_id, unit, percentage, created_at, updated_at, created_by, updated_by)
                    VALUES " . implode(',', $questionMarks));

                $stmt->execute($insertValues);

                $pdo->commit();

                $stmt = $pdo->prepare("SELECT location_assignment_id, bill_column_setting_id, unit, percentage FROM ".LocationBillItemQuantityProrateTable::getInstance()->getTableName()."
                    WHERE (location_assignment_id, bill_column_setting_id, unit) IN (".implode(',', $itemsToDelete).") ");

                $stmt->execute();

                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $locationBillItemQtyProrates = array();

                foreach($records as $record)
                {
                    $locationBillItemQtyProrates[$record['location_assignment_id'].'-'.$record['bill_column_setting_id'].'-'.$record['unit']] = $record['percentage'];
                }

                unset($records);

                foreach($ids as $id)
                {
                    $parts = explode("-", $id);

                    if(array_key_exists($parts[1], $quantities) &&
                        array_key_exists($parts[0].'-'.$parts[1].'-'.$parts[2], $locationBillItemQtyProrates) &&
                        array_key_exists($parts[3], $quantities[$parts[1]]))
                    {
                        $percentage = $locationBillItemQtyProrates[$parts[0].'-'.$parts[1].'-'.$parts[2]];
                        $billItemQty = $quantities[$parts[1]][$parts[3]];
                        $proRatedQty = ($percentage != 0) ? ($percentage / 100) * $billItemQty : 0;

                        $items[] = array(
                            'id' => $id,
                            'percentage' => number_format($percentage, 5, '.', ''),
                            'prorated_qty' => number_format($proRatedQty, 2, '.', '')
                        );

                    }
                }
            }

            $success = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            $errorMsg = $e->getMessage();
            $success = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'items'    => $items
        ));
    }

    public function executeGetLocationCodeListByLevel(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->hasParameter('t') and
            $request->hasParameter('s') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        switch ($request->getParameter('t')){
            case LocationAssignment::SEQUENCE_TYPE_TRADE:

                $records = PreDefinedLocationCodeTable::getAssignedCodesByProjectId($project->id);

                break;
            case LocationAssignment::SEQUENCE_TYPE_LOCATION:

                $records = ProjectStructureLocationCodeTable::getAssignedCodesByProjectId($project->id);

                break;
            default:
                throw new Exception("Invalid type from request parameter");

        }

        $items = array();
        $takenCodes = array();

        foreach($records as $record)
        {
            foreach($record as $code)
            {
                if(!array_key_exists($code['id'], $takenCodes))
                {
                    if($request->hasParameter("root_id") and !in_array($code['root_id'], $request->getParameter("root_id")))
                        continue;

                    if($code['level'] == intval($request->getParameter('s')))
                    {
                        if(!array_key_exists('name', $code))
                            $code['name'] = $code['description'];

                        $items[] = $code;

                        $takenCodes[$code['id']] = $code;
                    }
                }
            }

        }

        unset($takenCodes, $records);

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $items
        ));
    }

    public function executeGetBillItemLocations(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'))
        );

        $pdo = $billItem->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT l.id AS location_assignment_id, loc_p.root_id, loc_p.id, loc_p.name, loc_p.priority, loc_p.lft, loc_p.rgt, loc_p.level
            FROM " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " loc
            JOIN " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " loc_p ON (loc.lft BETWEEN loc_p.lft AND loc_p.rgt AND loc.root_id = loc_p.root_id AND loc_p.deleted_at IS NULL)
            JOIN " . LocationAssignmentTable::getInstance()->getTableName() . " l ON loc.id = l.pre_defined_location_code_id
            WHERE l.bill_item_id = ".$billItem->id."
            AND loc.deleted_at IS NULL
            ORDER BY loc_p.priority, loc_p.lft, loc_p.level");

        $stmt->execute();

        $predefinedLocationCodes = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT DISTINCT l.id AS location_assignment_id, loc_p.root_id, loc_p.id, loc_p.description AS name, loc_p.priority, loc_p.lft, loc_p.rgt, loc_p.level
            FROM " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " loc
            JOIN " . ProjectStructureLocationCodeTable::getInstance()->getTableName() . " loc_p ON (loc.lft BETWEEN loc_p.lft AND loc_p.rgt AND loc.root_id = loc_p.root_id AND loc_p.deleted_at IS NULL)
            JOIN " . LocationAssignmentTable::getInstance()->getTableName() . " l ON loc.id = l.project_structure_location_code_id
            WHERE l.bill_item_id = ".$billItem->id."
            AND loc.deleted_at IS NULL
            ORDER BY loc_p.priority, loc_p.lft, loc_p.level");

        $stmt->execute();

        $projectStructureLocationCodes = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $locationAssignments = array();

        foreach($predefinedLocationCodes as $locationAssignmentId => $predefinedLocationCode)
        {
            if(!array_key_exists($locationAssignmentId, $locationAssignments))
            {
                $locationAssignments[$locationAssignmentId] = array();
            }

            $locationAssignments[$locationAssignmentId]['predefined_location_code'] = $predefinedLocationCode;
        }

        unset($predefinedLocationCodes);

        foreach($projectStructureLocationCodes as $locationAssignmentId => $projectStructureLocationCode)
        {
            $locationAssignments[$locationAssignmentId]['project_structure_location_code'] = $projectStructureLocationCode;
        }

        unset($projectStructureLocationCodes);

        return $this->renderJson($locationAssignments);
    }

    public function executeRemoveAssignedLocation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $locationAssignment = Doctrine_Core::getTable('LocationAssignment')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;

        $pdo = $locationAssignment->getTable()->getConnection()->getDbh();

        try
        {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("DELETE FROM ".LocationAssignmentTable::getInstance()->getTableName()." WHERE id = ".$locationAssignment->id);

            $stmt->execute();

            $pdo->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeRemoveAssignedLocations(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('ids')
        );

        $errorMsg = null;

        $pdo = LocationAssignmentTable::getInstance()->getConnection()->getDbh();

        try
        {
            $ids = explode(",", $request->getParameter("ids"));

            $pdo->beginTransaction();

            if(!empty($ids))
            {
                $stmt = $pdo->prepare("DELETE FROM ".LocationAssignmentTable::getInstance()->getTableName()." WHERE id IN (".implode(",", $ids).")");

                $stmt->execute();
            }

            $pdo->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetOpenClaimRevision(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $currentProjectRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($project->PostContract);

        return $this->renderJson($currentProjectRevision);
    }

    public function executeProgressClaimUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot() and
            $request->hasParameter("id")
        );

        $ids = explode("-", $request->getParameter("id"));

        $this->forward404Unless(count($ids) == 4 and
            $locationAssignment = LocationAssignmentTable::getInstance()->find(intval($ids[0])) and
            $billColumnSetting = BillColumnSettingTable::getInstance()->find(intval($ids[1])) and
            $billItem = BillItemTable::getInstance()->find(intval($ids[3]))
        );

        $typeUnit = intval($ids[2]);

        $errorMsg = null;

        $con = $billItem->getTable()->getConnection();

        $item = array();

        try
        {
            $con->beginTransaction();

            $pdo = $con->getDbh();

            $currentProjectRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($project->PostContract);

            $locationBillItemQuantityProrate = Doctrine_Query::create()
                ->from('LocationBillItemQuantityProrate l')
                ->where('l.location_assignment_id = ?', $locationAssignment->id)
                ->andWhere('l.bill_column_setting_id = ?', $billColumnSetting->id)
                ->andWhere('l.unit = ?', $typeUnit)
                ->fetchOne();

            if($locationBillItemQuantityProrate && $currentProjectRevision)
            {
                $locationProgressClaim = Doctrine_Query::create()
                    ->from('LocationProgressClaim c')
                    ->where('c.post_contract_claim_revision_id = ?', $currentProjectRevision['id'])
                    ->andWhere('c.location_bill_item_quantity_prorates_id = ?', $locationBillItemQuantityProrate->id)
                    ->fetchOne();

                if(!$locationProgressClaim)
                {
                    $locationProgressClaim =  new LocationProgressClaim();
                    $locationProgressClaim->post_contract_claim_revision_id = $currentProjectRevision['id'];
                    $locationProgressClaim->location_bill_item_quantity_prorates_id = $locationBillItemQuantityProrate->id;

                    $locationProgressClaim->save($con);
                }

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                if ( $fieldName )
                {
                    $columns = array_keys(LocationProgressClaimTable::getInstance()->getColumns());
                    if ( in_array($fieldName, $columns))
                    {
                        $locationProgressClaim->{'update' . sfInflector::camelize($fieldName)}($fieldValue);

                        $locationProgressClaim->save($con);

                        $locationProgressClaim->updateStandardBillClaim();
                    }
                }

                $con->commit();

                $item = array(
                    'current_quantity'      => number_format((float)$locationProgressClaim->current_quantity, 2, '.', ''),
                    'current_percentage'    => number_format((float)$locationProgressClaim->current_percentage, 2, '.', ''),
                    'up_to_date_quantity'   => number_format((float)$locationProgressClaim->up_to_date_quantity, 2, '.', ''),
                    'up_to_date_percentage' => number_format((float)$locationProgressClaim->up_to_date_percentage, 2, '.', '')
                );
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $item
        ));
    }
}
