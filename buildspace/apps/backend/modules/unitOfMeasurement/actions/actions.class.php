<?php

/**
 * unitOfMeasurement actions.
 *
 * @package    buildspace
 * @subpackage unitOfMeasurement
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class unitOfMeasurementActions extends BaseActions {

    public function executeGetUnitOfMeasurements(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->hasParameter('type')
        );

        $results = DoctrineQuery::create()->select('u.id, u.type, u.name, u.symbol, u.updated_at, d.id, d.name, x.id')
            ->from('UnitOfMeasurement u')
            ->leftJoin('u.UnitOfMeasurementDimensions x')
            ->leftJoin('x.Dimension d')
            ->where('u.type = ?', $request->getParameter('type'))
            ->andWhere('u.display IS TRUE')
            ->orderBy('u.id ASC')
            ->addOrderBy('x.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $results as $key => $unit )
        {
            $dimensions = array();

            foreach ( $unit['UnitOfMeasurementDimensions'] as $xref )
            {
                if ( $xref['Dimension'] )
                {
                    $dimensions[] = $xref['Dimension']['name'];
                }
            }

            $results[$key]['dimensions']  = implode(', ', $dimensions);
            $results[$key]['updated_at']  = date('d/m/Y H:i', strtotime($unit['updated_at']));
            $results[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $results[$key]['UnitOfMeasurementDimensions'] );
        }

        array_push($results, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'symbol'         => '',
            'type'           => $request->getParameter('type'),
            'updated_at'     => '-',
            'can_be_deleted' => false,
            'dimensions'     => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $results
        ));
    }

    public function executeUnitOfMeasurementForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( !$uom = Doctrine_Core::getTable('UnitOfMeasurement')->find($request->getParameter('id')) )
        {
            $this->forward404Unless($request->hasParameter('t'));

            $uom  = new UnitOfMeasurement();
            $type = $request->getParameter('t');
        }

        $form = new UnitOfMeasurementForm($uom);

        return $this->renderJson(array(
            'id'                               => $uom->isNew() ? - 1 : $uom->id,
            'unit_of_measurement[name]'        => $uom->isNew() ? '' : $uom->name,
            'unit_of_measurement[symbol]'      => $uom->isNew() ? '' : $uom->symbol,
            'unit_of_measurement[type]'        => $uom->isNew() ? $type : $uom->type,
            'unit_of_measurement[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeUnitOfMeasurementDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $uom = Doctrine_Core::getTable('UnitOfMeasurement')->find($request->getParameter('id'))
        );

        try
        {
            $uom->delete();

            $errorMsg = null;
            $success  = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUnitOfMeasurementUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $form = new UnitOfMeasurementForm(Doctrine_Core::getTable('UnitOfMeasurement')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $uom = $form->save();

            $errors  = null;
            $success = true;
            $values  = array(
                'id'                               => $uom->id,
                'unit_of_measurement[name]'        => $uom->name,
                'unit_of_measurement[symbol]'      => $uom->symbol,
                'unit_of_measurement[type]'        => $uom->type,
                'unit_of_measurement[_csrf_token]' => $form->getCSRFToken()
            );
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $values  = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values ));
    }

    public function executeGetUnitOfMeasurementDimensions(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $records = array();
        $form    = new BaseForm();

        if ( $uom = Doctrine_Core::getTable('UnitOfMeasurement')->find($request->getParameter('id')) )
        {
            $records = DoctrineQuery::create()->select('x.id, x.dimension_id, x.priority, d.id, d.name')
                ->from('UnitOfMeasurementDimensions x')
                ->leftJoin('x.Dimension d')
                ->where('x.unit_of_measurement_id = ?', $uom->id)
                ->addOrderBy('x.priority')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            foreach ( $records as $key => $record )
            {
                if ( $record['Dimension'] )
                {
                    $records[$key]['name']           = $record['Dimension']['name'];
                    $records[$key]['up']             = $record['id'];
                    $records[$key]['down']           = $record['id'];
                    $records[$key]['delete']         = $record['id'];
                    $records[$key]['can_be_deleted'] = UnitOfMeasurementDimensionsTable::canBeDeletedByUnitOfMeasurementIdAndDimensionId($uom->id, $record['Dimension']['id']);
                    $records[$key]['_csrf_token']    = $form->getCSRFToken();
                }

                unset( $records[$key]['Dimension'], $record );
            }
        }

        array_push($records, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'dimension_id'   => - 1,
            'up'             => - 1,
            'down'           => - 1,
            'delete'         => - 1,
            'can_be_deleted' => false,
            '_csrf_token'    => $form->getCSRFToken(),
            'priority'       => null
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $records
        ));
    }

    public function executeGetDimensions(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $dimensions = DoctrineQuery::create()->select('p.id, p.name, p.updated_at')
            ->from('Dimension p')
            ->orderBy('id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $dimensions as $key => $dimension )
        {
            $dimensions[$key]['can_be_deleted'] = DimensionTable::canBeDeletedById($dimension['id']);
            $dimensions[$key]['updated_at']     = date('d/m/Y H:i', strtotime($dimension['updated_at']));
            $dimensions[$key]['_csrf_token']    = $form->getCSRFToken();

            unset( $dimension );
        }

        array_push($dimensions, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'updated_at'     => '-',
            'can_be_deleted' => false,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $dimensions
        ));
    }

    public function executeDimensionForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( !$dimension = Doctrine_Core::getTable('Dimension')->find($request->getParameter('id')) )
        {
            $dimension = new Dimension();
        }

        $form = new DimensionForm($dimension);

        return $this->renderJson(array(
            'id'                     => $dimension->isNew() ? - 1 : $dimension->id,
            'dimension[name]'        => $dimension->isNew() ? '' : $dimension->name,
            'dimension[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeDimensionUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $form = new DimensionForm(Doctrine_Core::getTable('Dimension')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $dimension = $form->save();

            $errors  = null;
            $success = true;
            $values  = array(
                'id'                     => $dimension->id,
                'dimension[name]'        => $dimension->name,
                'dimension[_csrf_token]' => $form->getCSRFToken()
            );
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $values  = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values ));
    }

    public function executeDimensionDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $dimension = Doctrine_Core::getTable('Dimension')->find($request->getParameter('id'))
        );

        try
        {
            $dimension->delete();

            $errorMsg = null;
            $success  = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUnitOfMeasurementDimensionDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $xref = Doctrine_Core::getTable('UnitOfMeasurementDimensions')->find($request->getParameter('id'))
        );

        $con = $xref->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $xref->delete($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $con->rollback();

            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUnitOfMeasurementDimensionPriorityUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('dir') and
            $xref = Doctrine_Core::getTable('UnitOfMeasurementDimensions')->find($request->getParameter('id'))
        );

        $con = $xref->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $xref->movePriority($request->getParameter('dir'));

            $con->commit();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $con->rollback();

            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUnitOfMeasurementDimensionAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $uom = Doctrine_Core::getTable('UnitOfMeasurement')->find($request->getParameter('uid')) and
            $dimension = Doctrine_Core::getTable('Dimension')->find($request->getParameter('did'))
        );

        if ( !$xref = UnitOfMeasurementDimensionsTable::getByUnitOfMeasurementIdAndDimensionId($uom->id, $dimension->id) )
        {
            $xref = new UnitOfMeasurementDimensions();
        }

        $con = $uom->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $xref->unit_of_measurement_id = $uom->id;
            $xref->dimension_id           = $dimension->id;
            $xref->save($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetDimensionSelectList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->hasParameter('uid')
        );

        $uomId = $request->getParameter('uid');
        $pdo   = DimensionTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT d.id, d.name FROM " . DimensionTable::getInstance()->getTableName() . " d WHERE id NOT IN
        (SELECT dimension_id FROM " . UnitOfMeasurementDimensionsTable::getInstance()->getTableName() . " WHERE unit_of_measurement_id = " . $uomId . ")
        AND d.deleted_at IS NULL");

        $stmt->execute();

        $dimensions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $dimensions as $key => $dimension )
        {
            $dimensions[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $dimension );
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $dimensions
        ));
    }

}