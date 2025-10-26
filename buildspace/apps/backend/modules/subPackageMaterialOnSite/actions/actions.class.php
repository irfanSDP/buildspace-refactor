<?php

/**
 * subPackageMaterialOnSite actions.
 *
 * @package    buildspace
 * @subpackage subPackageMaterialOnSite
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageMaterialOnSiteActions extends BaseActions {

	public function executeGetMaterialOnSiteList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$form      = new BaseForm();
		$formToken = $form->getCSRFToken();

		$materialOnSites = Doctrine_Query::create()
			->select('id, description, status, reduction_percentage, total, total_after_reduction, updated_at')
			->from('SubPackageMaterialOnSite')
			->andWhere('sub_package_id = ?', $subPackage->id)
			->addOrderBy('priority ASC')
			->fetchArray();

		foreach ( $materialOnSites as $key => $materialOnSite )
		{
			$materialOnSites[$key]['updated_at']  = date('d/m/Y H:i', strtotime($materialOnSite['updated_at']));
			$materialOnSites[$key]['relation_id'] = $subPackage->id;
			$materialOnSites[$key]['_csrf_token'] = $formToken;
		}

		array_push($materialOnSites, array(
			'id'                   => Constants::GRID_LAST_ROW,
			'description'          => '',
			'status'               => true,
			'reduction_percentage' => 0,
			'total'                => 0,
			'relation_id'          => $subPackage->id,
			'updated_at'           => '-',
			'_csrf_token'          => $formToken,
		));

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $materialOnSites,
		));
	}

	public function executeMaterialOnSiteAdd(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

		$materialOnSite = new SubPackageMaterialOnSite();
		$con            = $materialOnSite->getTable()->getConnection();

		if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
		{
			$prevMaterialOnSite = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('prev_item_id')) : null;

			$priority     = $prevMaterialOnSite ? $prevMaterialOnSite->priority + 1 : 0;
			$subPackageId = $request->getParameter('relation_id');

			if ( $request->hasParameter('attr_name') )
			{
				$fieldName  = $request->getParameter('attr_name');
				$fieldValue = $request->getParameter('val');

				if ( $fieldName == 'reduction_percentage' )
				{
					$fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
				}

				$materialOnSite->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
			}
		}
		else
		{
			$this->forward404Unless($nextMaterialOnSite = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('before_id')));

			$priority     = $nextMaterialOnSite->priority;
			$subPackageId = $nextMaterialOnSite->sub_package_id;
		}

		$items = array();

		try
		{
			$con->beginTransaction();

			DoctrineQuery::create()
				->update('SubPackageMaterialOnSite')
				->set('priority', 'priority + 1')
				->where('priority >= ?', $priority)
				->andWhere('sub_package_id = ?', $subPackageId)
				->execute();

			$materialOnSite->sub_package_id = $subPackageId;
			$materialOnSite->priority       = $priority;

			$materialOnSite->save();

			$con->commit();

			$success = true;

			$errorMsg = null;

			$item = array();

			$form      = new BaseForm();
			$formToken = $form->getCSRFToken();

			$item['id']                    = $materialOnSite->id;
			$item['description']           = $materialOnSite->description;
			$item['status']                = SubPackageMaterialOnSite::STATUS_TYPE_THIS_CLAIM;
			$item['relation_id']           = $materialOnSite->sub_package_id;
			$item['reduction_percentage']  = 0;
			$item['total']                 = 0;
			$item['total_after_reduction'] = 0;
			$item['updated_at']            = date('d/m/Y H:i', strtotime($materialOnSite->updated_at));
			$item['_csrf_token']           = $formToken;

			array_push($items, $item);

			if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
			{
				// default last row
				array_push($items, array(
					'id'                   => Constants::GRID_LAST_ROW,
					'description'          => '',
					'status'               => SubPackageMaterialOnSite::STATUS_TYPE_THIS_CLAIM,
					'relation_id'          => $materialOnSite->sub_package_id,
					'reduction_percentage' => 0,
					'total'                => 0,
					'updated_at'           => '-',
					'_csrf_token'          => $formToken,
				));
			}
		}
		catch (Exception $e)
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

	public function executeMaterialOnSiteUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$materialOnSite = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('id'))
		);

		$rowData = array();
		$con     = $materialOnSite->getTable()->getConnection();

		try
		{
			$con->beginTransaction();

			$fieldName  = $request->getParameter('attr_name');
			$fieldValue = $request->getParameter('val');

			if ( $fieldName == 'reduction_percentage' )
			{
				$fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
			}

			$materialOnSite->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

			$materialOnSite->save($con);

			$con->commit();

			$success = true;

			$errorMsg = null;

			$materialOnSite->refresh();

			$value = $materialOnSite->$fieldName;

			$rowData = array(
				$fieldName              => $value,
				'total'                 => $materialOnSite->total,
				'total_after_reduction' => $materialOnSite->total_after_reduction,
				'updated_at'            => date('d/m/Y H:i', strtotime($materialOnSite->updated_at))
			);
		}
		catch (Exception $e)
		{
			$con->rollback();
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
	}

	public function executeMaterialOnSiteDelete(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$materialOnSite = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('id'))
		);

		$errorMsg = null;

		$item['id'] = $materialOnSite->id;

		try
		{
			$materialOnSite->delete();

			$success = true;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
			$item     = array();
			$success  = false;
		}

		// return items need to be in array since grid js expect an array of items for delete operation (delete from store)
		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
	}

	public function executeGetMaterialOnSiteItemList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$mos = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('id'))
		);

		$pdo       = $mos->getTable()->getConnection()->getDbh();
		$form      = new BaseForm();
		$formToken = $form->getCSRFToken();

		$stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.delivered_qty, i.used_qty, i.balance_qty, i.rate,
		i.amount, i.priority, i.lft, i.level, i.rate, uom.id AS uom_id, uom.symbol AS uom_symbol
		FROM " . SubPackageMaterialOnSiteItemTable::getInstance()->getTableName() . " i
		LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
		WHERE i.sp_material_on_site_id = " . $mos->id . " AND i.deleted_at IS NULL
		ORDER BY i.priority, i.lft, i.level ASC");

		$stmt->execute();

		$mosItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $mosItems as $key => $mosItem )
		{
			$mosItems[$key]['rate-value']  = $mosItem['rate'];
			$mosItems[$key]['type']        = (string) $mosItem['type'];
			$mosItems[$key]['uom_id']      = $mosItem['uom_id'] > 0 ? (string) $mosItem['uom_id'] : '-1';
			$mosItems[$key]['uom_symbol']  = $mosItem['uom_id'] > 0 ? $mosItem['uom_symbol'] : '';
			$mosItems[$key]['relation_id'] = $mos->id;
			$mosItems[$key]['_csrf_token'] = $formToken;
		}

		array_push($mosItems, array(
			'id'            => Constants::GRID_LAST_ROW,
			'description'   => '',
			'type'          => (string) ResourceItem::TYPE_WORK_ITEM,
			'uom_id'        => '-1',
			'uom_symbol'    => '',
			'relation_id'   => $mos->id,
			'delivered_qty' => 0,
			'used_qty'      => 0,
			'balance_qty'   => 0,
			'amount'        => 0,
			'level'         => 0,
			'rate-value'    => 0,
			'_csrf_token'   => $formToken,
		));

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $mosItems
		));
	}

	public function executeMaterialOnSiteItemAdd(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

		$items = array();

		$con = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->getConnection();

		try
		{
			$con->beginTransaction();

			if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
			{
				$previousItem     = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('prev_item_id')) : null;
				$materialOnSiteId = $request->getParameter('relation_id');

				$fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
				$fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

				if ( in_array($fieldName, [ 'delivered_qty', 'used_qty' ]) )
				{
					$fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
				}

				$item = SubPackageMaterialOnSiteItemTable::createItemFromLastRow($previousItem, $materialOnSiteId, $fieldName, $fieldValue);
			}
			else
			{
				$this->forward404Unless($nextItem = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('before_id')));

				$materialOnSiteId = $nextItem->sp_material_on_site_id;

				$item = SubPackageMaterialOnSiteItemTable::createItem($nextItem, $materialOnSiteId);
			}

			$con->commit();

			$success = true;

			$errorMsg = null;

			$data = array();

			$form      = new BaseForm();
			$formToken = $form->getCSRFToken();

			$item->refresh();

			$data['id']            = $item->id;
			$data['description']   = $item->description;
			$data['type']          = (string) $item->type;
			$data['uom_id']        = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
			$data['uom_symbol']    = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
			$data['relation_id']   = $materialOnSiteId;
			$data['delivered_qty'] = $item->delivered_qty;
			$data['used_qty']      = $item->used_qty;
			$data['balance_qty']   = $item->balance_qty;
			$data['amount']        = $item->amount;
			$data['rate-value']    = $item->rate;
			$data['_csrf_token']   = $formToken;
			$data['level']         = $item->level;

			array_push($items, $data);

			if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
			{
				array_push($items, array(
					'id'                      => Constants::GRID_LAST_ROW,
					'description'             => '',
					'type'                    => (string) ResourceItem::TYPE_WORK_ITEM,
					'uom_id'                  => '-1',
					'uom_symbol'              => '',
					'relation_id'             => $materialOnSiteId,
					'rate-value'              => 0,
					'delivered_qty'           => 0,
					'used_qty'                => false,
					'addition_quantity-value' => 0,
					'balance_qty'             => 0,
					'amount'                  => 0,
					'level'                   => 0,
					'_csrf_token'             => $formToken
				));
			}
		}
		catch (Exception $e)
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

	public function executeMaterialOnSiteItemUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$item = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('id'))
		);

		$rowData = array();

		$con = $item->getTable()->getConnection();

		try
		{
			$con->beginTransaction();

			$fieldName  = $request->getParameter('attr_name');
			$fieldValue = $request->getParameter('val');

			$fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

			if ( $fieldName == 'rate' )
			{
				$fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
				$item->rate = number_format($fieldValue, 2, '.', '');

				$fieldName = 'rate-value';
			}
			else if ( in_array($fieldName, [ 'delivered_qty', 'used_qty' ]) )
			{
				$fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
				$item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
			}
			else
			{
				$item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
			}

			$item->save($con);

			$con->commit();

			$success = true;

			$errorMsg = null;

			$item->refresh();

			$rowData[$fieldName] = $item->{$request->getParameter('attr_name')};

			$rowData['type']          = (string) $item->type;
			$rowData['uom_id']        = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
			$rowData['uom_symbol']    = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
			$rowData['delivered_qty'] = $item->delivered_qty;
			$rowData['used_qty']      = $item->used_qty;
			$rowData['balance_qty']   = $item->balance_qty;
			$rowData['amount']        = $item->amount;
		}
		catch (Exception $e)
		{
			$con->rollback();
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
	}

	public function executeMaterialOnSiteItemDelete(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$item = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('id'))
		);

		$errorMsg = null;
		$con      = $item->getTable()->getConnection();

		try
		{
			$con->beginTransaction();

			$items = Doctrine_Query::create()->select('i.id')
				->from('SubPackageMaterialOnSiteItem i')
				->andWhere('i.root_id = ?', $item->root_id)
				->andWhere('i.sp_material_on_site_id = ?', $item->sp_material_on_site_id)
				->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
				->addOrderBy('i.lft')
				->fetchArray();

			$item->delete($con);

			$con->commit();

			$success = true;
		}
		catch (Exception $e)
		{
			$con->rollback();

			$items    = array();
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => array() ));
	}

	public function executeMaterialOnSiteItemIndent(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('id')));

		$success  = false;
		$children = array();
		$errorMsg = null;
		$data     = array();

		try
		{
			if ( $item->indent() )
			{
				$data['id']    = $item->id;
				$data['level'] = $item->level;

				$children = Doctrine_Query::create()->select('i.id, i.level')
					->from('SubPackageMaterialOnSiteItem i')
					->where('i.root_id = ?', $item->root_id)
					->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
					->addOrderBy('i.lft')
					->fetchArray();

				$success = true;
			}
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
	}

	public function executeMaterialOnSiteItemOutdent(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('id')));

		$success  = false;
		$children = array();
		$errorMsg = null;
		$data     = array();

		try
		{
			if ( $item->outdent() )
			{
				$data['id']    = $item->id;
				$data['level'] = $item->level;

				$children = Doctrine_Query::create()->select('i.id, i.level')
					->from('SubPackageMaterialOnSiteItem i')
					->where('i.root_id = ?', $item->root_id)
					->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
					->addOrderBy('i.lft')
					->fetchArray();

				$success = true;
			}
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
	}

	public function executeMaterialOnSiteItemPaste(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('id')));

		$data         = array();
		$children     = array();
		$lastPosition = false;
		$success      = false;
		$errorMsg     = null;

		$targetItem = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find(intval($request->getParameter('target_id')));

		if ( !$targetItem )
		{
			$this->forward404Unless($targetItem = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('prev_item_id')));
			$lastPosition = true;
		}

		if ( $targetItem->root_id == $item->root_id and $targetItem->lft >= $item->lft and $targetItem->rgt <= $item->rgt )
		{
			$errorMsg = "cannot move item into itself";

			return $this->renderJson(array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() ));
		}

		try
		{
			$item->moveTo($targetItem, $lastPosition);

			$children = DoctrineQuery::create()
				->select('i.id, i.level, i.updated_at')
				->from('SubPackageMaterialOnSiteItem i')
				->andWhere('i.root_id = ?', $item->root_id)
				->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
				->addOrderBy('i.lft')
				->fetchArray();

			foreach ( $children as $key => $child )
			{
				$children[$key]['updated_at'] = date('d/m/Y H:i', strtotime($child['updated_at']));

				unset( $child );
			}

			$data['id']         = $item->id;
			$data['level']      = $item->level;
			$data['updated_at'] = date('d/m/Y H:i', strtotime($item->updated_at));

			$success  = true;
			$errorMsg = null;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => $children ));
	}

	public function executeImportStockOutItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$mos = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('sp_material_on_site_id'))
		);

		$conn     = $mos->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

			SubPackageMaterialOnSiteItemTable::importStockOutItems($conn, $request->getParameter('id'), $mos, $ids);

			$conn->commit();

			$success = true;
		}
		catch (Exception $e)
		{
			$conn->rollback();

			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
	}

	public function executeImportMaterialOnSiteItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$mos = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($request->getParameter('sp_material_on_site_id'))
		);

		$conn     = $mos->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids = explode(',', $request->getParameter('ids'));

			SubPackageMaterialOnSiteItemTable::importMaterialOnSiteItems($conn, Utilities::array_filter_integer($request->getParameter('id')), $mos, $ids);

			$mos->updateAmount();

			$conn->commit();

			$success = true;
		}
		catch (Exception $e)
		{
			$conn->rollback();

			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
	}

	public function executeGetUnits(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$options = array();
		$values  = array();

		array_push($values, '-1');
		array_push($options, '---');

		$units = Doctrine_Query::create()->select('u.id, u.symbol')
			->from('UnitOfMeasurement u')
			->where('u.display IS TRUE')
			->addOrderBy('u.symbol ASC')
			->fetchArray();

		foreach ( $units as $record )
		{
			array_push($values, (string) $record['id']); //damn, dojo store handles ids in string format

			array_push($options, $record['symbol']);
		}

		unset( $units );

		return $this->renderJson(array(
			'values'  => $values,
			'options' => $options
		));
	}

	public function executeGetDescendantsForImport(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$mosItem = Doctrine_Core::getTable('SubPackageMaterialOnSiteItem')->find($request->getParameter('id'))
		);

		try
		{
			$items = DoctrineQuery::create()
				->select('i.id')
				->from('SubPackageMaterialOnSiteItem i')
				->andWhere('i.root_id = ?', $mosItem->root_id)
				->andWhere('i.lft >= ? AND i.rgt <= ?', array( $mosItem->lft, $mosItem->rgt ))
				->addOrderBy('i.lft')
				->fetchArray();

			$success  = true;
			$errorMsg = null;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
			$success  = false;
			$items    = array();
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
	}

	public function executeGetPrintSettingForm(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		// create print setting if not available
		$printSetting = SubPackageMaterialOnSitePrintSettingTable::createNewInstance($subPackage->SubPackageMaterialOnSitePrintSetting);

		$form     = new SubPackageMaterialOnSitePrintSettingForm($printSetting);
		$formName = 'sub_package_material_on_site_print_setting';

		$data['printSettingForm'] = array(
			"{$formName}[project_name]"                        => $form->getObject()->project_name,
			"{$formName}[site_belonging_address]"              => $form->getObject()->site_belonging_address,
			"{$formName}[original_finished_date]"              => $form->getObject()->original_finished_date,
			"{$formName}[contract_duration]"                   => $form->getObject()->contract_duration,
			"{$formName}[contract_original_amount]"            => $form->getObject()->contract_original_amount,
			"{$formName}[payment_revision_no]"                 => $form->getObject()->payment_revision_no,
			"{$formName}[evaluation_date]"                     => $form->getObject()->evaluation_date,
			"{$formName}[total_text]"                          => $form->getObject()->total_text,
			"{$formName}[percentage_of_material_on_site_text]" => $form->getObject()->percentage_of_material_on_site_text,
			"{$formName}[carried_to_final_summary_text]"       => $form->getObject()->carried_to_final_summary_text,
			"_csrf_token"                                      => $form->getCSRFToken(),
		);

		return $this->renderJson($data);
	}

	public function executeSavePrintSetting(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$form = new SubPackageMaterialOnSitePrintSettingForm($subPackage->SubPackageMaterialOnSitePrintSetting);

		if ( $this->isFormValid($request, $form) )
		{
			$form->updateObject();

			$form->getObject()->save();

			$form    = $form->save();
			$id      = $form->getId();
			$success = true;
			$errors  = array();
		}
		else
		{
			$id      = $request->getPostParameter('branchId');
			$errors  = $form->getErrors();
			$success = false;
		}

		$data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

}