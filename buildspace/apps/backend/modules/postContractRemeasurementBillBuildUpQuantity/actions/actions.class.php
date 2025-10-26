<?php

/**
 * postContractRemeasurementBillBuildUpQuantity actions.
 *
 * @package    buildspace
 * @subpackage postContractRemeasurementBillBuildUpQuantity
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractRemeasurementBillBuildUpQuantityActions extends BaseActions {

	public function executeGetBuildUpQuantityItemList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('bill_item_id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$query = DoctrineQuery::create()
			->select('i.id, i.description, i.sign, i.total, ifc.column_name, ifc.value, ifc.final_value')
			->from('PostContractRemeasurementBuildUpQuantityItem i')
			->leftJoin('i.FormulatedColumns ifc')
			->where('i.post_contract_bill_item_rate_id = ?', $postContractBillItemRate->id)
			->andWhere('i.bill_column_setting_id = ?', $billColumnSetting->id)
			->andWhere('i.type = ?', $request->getParameter('type'))
			->addOrderBy('i.priority ASC')
			->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

		$buildUpQuantityItems = $query->execute();

		$form = new BaseForm();

		$formulatedColumnNames = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($postContractBillItemRate->BillItem->UnitOfMeasurement);

		foreach ( $buildUpQuantityItems as $key => $buildUpQuantityItem )
		{
			$buildUpQuantityItems[$key]['sign']        = (string) $buildUpQuantityItem['sign'];
			$buildUpQuantityItems[$key]['sign_symbol'] = PostContractRemeasurementBuildUpQuantityItemTable::getSignTextBySign($buildUpQuantityItem['sign']);
			$buildUpQuantityItems[$key]['relation_id'] = $postContractBillItemRate->id;
			$buildUpQuantityItems[$key]['_csrf_token'] = $form->getCSRFToken();

			foreach ( $formulatedColumnNames as $constant )
			{
				$buildUpQuantityItems[$key][$constant . '-final_value']        = 0;
				$buildUpQuantityItems[$key][$constant . '-value']              = '';
				$buildUpQuantityItems[$key][$constant . '-has_cell_reference'] = false;
				$buildUpQuantityItems[$key][$constant . '-has_formula']        = false;
			}

			foreach ( $buildUpQuantityItem['FormulatedColumns'] as $formulatedColumn )
			{
				$buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-final_value']        = $formulatedColumn['final_value'];
				$buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-value']              = $formulatedColumn['value'];
				$buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_cell_reference'] = false;
				$buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
			}

			unset( $buildUpQuantityItem, $buildUpQuantityItems[$key]['FormulatedColumns'] );
		}

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'sign'        => (string) BillBuildUpQuantityItem::SIGN_POSITIVE,
			'sign_symbol' => BillBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
			'relation_id' => $postContractBillItemRate->id,
			'total'       => '',
			'_csrf_token' => $form->getCSRFToken()
		);

		foreach ( $formulatedColumnNames as $columnName )
		{
			$defaultLastRow[$columnName . '-final_value']        = 0;
			$defaultLastRow[$columnName . '-value']              = "";
			$defaultLastRow[$columnName . '-has_cell_reference'] = false;
			$defaultLastRow[$columnName . '-has_formula']        = false;
		}

		array_push($buildUpQuantityItems, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $buildUpQuantityItems
		));
	}

	public function executeGetBuildUpSummary(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->hasParameter('type') and
			$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantitySummary = PostContractRemeasurementBuildUpQuantitySummaryTable::createByBillItemIdAndBillColumnSettingId($postContractBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$data = array(
			'apply_conversion_factor'    => $buildUpQuantitySummary->apply_conversion_factor,
			'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
			'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
			'total_quantity'             => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
			'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
			'rounding_type'              => ( $buildUpQuantitySummary->rounding_type ) ? $buildUpQuantitySummary->rounding_type : $postContractBillItemRate->BillItem->Element->ProjectStructure->BillSetting->build_up_quantity_rounding_type
		);

		return $this->renderJson($data);
	}

	public function executeBuildUpSummaryRoundingUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantitySummary = PostContractRemeasurementBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($postContractBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$buildUpQuantitySummary->rounding_type = $request->getParameter('rounding_type');

		$buildUpQuantitySummary->save();
		$buildUpQuantitySummary->refresh();

		return $this->renderJson(array(
			'total_quantity' => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
			'final_quantity' => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
			'rounding_type'  => $buildUpQuantitySummary->rounding_type
		));
	}

	public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$value = $request->getParameter('value');

		$buildUpQuantitySummary = PostContractRemeasurementBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($postContractBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$buildUpQuantitySummary->apply_conversion_factor = $value;
		$buildUpQuantitySummary->save();

		$buildUpQuantitySummary->refresh();

		return $this->renderJson(array(
			'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
			'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
			'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', '')
		));
	}

	public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantitySummary = PostContractRemeasurementBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($postContractBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$val = $request->getParameter('val');

		switch ($request->getParameter('token'))
		{
			case 'amount':
				$conversionFactorAmount                           = strlen($val) > 0 ? floatval($val) : 0;
				$buildUpQuantitySummary->conversion_factor_amount = $conversionFactorAmount;
				break;
			case 'operator':
				$buildUpQuantitySummary->conversion_factor_operator = $val;
				break;
			default:
				break;
		}

		$buildUpQuantitySummary->save();
		$buildUpQuantitySummary->refresh();

		return $this->renderJson(array(
			'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
			'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
			'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator
		));
	}

	public function executeBuildUpQuantityItemAdd(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

		$items = array();

		$item = new PostContractRemeasurementBuildUpQuantityItem();

		$con = $item->getTable()->getConnection();

		$isFormulatedColumn = false;

		if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
		{
			$this->forward404Unless(
				$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('relation_id')) and
				$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
			);

			$formulatedColumnNames = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($postContractBillItemRate->BillItem->UnitOfMeasurement);

			$previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find($request->getParameter('prev_item_id')) : null;

			$priority = $previousItem ? $previousItem->priority + 1 : 0;

			if ( $request->hasParameter('attr_name') )
			{
				$fieldName  = $request->getParameter('attr_name');
				$fieldValue = $request->getParameter('val');

				if ( in_array($fieldName, $formulatedColumnNames) )
				{
					$isFormulatedColumn = true;
				}
				else
				{
					$item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
				}
			}
		}
		else
		{
			$this->forward404Unless($nextItem = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find($request->getParameter('before_id')));

			$postContractBillItemRate = $nextItem->PostContractBillItemRate;
			$billColumnSetting        = $nextItem->BillColumnSetting;
			$formulatedColumnNames    = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($postContractBillItemRate->BillItem->UnitOfMeasurement);

			$priority = $nextItem->priority;
		}

		try
		{
			$con->beginTransaction();

			DoctrineQuery::create()
				->update('PostContractRemeasurementBuildUpQuantityItem')
				->set('priority', 'priority + 1')
				->where('priority >= ?', $priority)
				->andWhere('post_contract_bill_item_rate_id = ?', $postContractBillItemRate->id)
				->andWhere('bill_column_setting_id = ?', $billColumnSetting->id)
				->andWhere('type = ?', $request->getParameter('type'))
				->execute();

			$item->post_contract_bill_item_rate_id = $postContractBillItemRate->id;
			$item->bill_column_setting_id          = $billColumnSetting->id;
			$item->priority                        = $priority;
			$item->type                            = $request->getParameter('type');
			$item->save($con);

			if ( $isFormulatedColumn )
			{
				$formulatedColumn              = new PostContractRemeasurementBuildUpQuantityFormulatedColumn();
				$formulatedColumn->relation_id = $item->id;
				$formulatedColumn->column_name = $fieldName;
				$formulatedColumn->setFormula($fieldValue);

				$formulatedColumn->save();
			}

			$con->commit();

			$success = true;

			$errorMsg = null;

			$data = array();

			$form = new BaseForm();

			$data['id']          = $item->id;
			$data['description'] = $item->description;
			$data['sign']        = (string) $item->sign;
			$data['sign_symbol'] = $item->getSignText();
			$data['total']       = $item->calculateTotal();
			$data['relation_id'] = $postContractBillItemRate->id;
			$data['_csrf_token'] = $form->getCSRFToken();

			foreach ( $formulatedColumnNames as $columnName )
			{
				$formulatedColumn                          = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
				$finalValue                                = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
				$data[$columnName . '-final_value']        = $finalValue;
				$data[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
				$data[$columnName . '-has_cell_reference'] = false;
				$data[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
			}

			array_push($items, $data);

			if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
			{
				$defaultLastRow = array(
					'id'          => Constants::GRID_LAST_ROW,
					'description' => '',
					'sign'        => (string) BillBuildUpQuantityItem::SIGN_POSITIVE,
					'sign_symbol' => BillBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
					'relation_id' => $postContractBillItemRate->id,
					'total'       => '',
					'_csrf_token' => $form->getCSRFToken()
				);

				foreach ( $formulatedColumnNames as $columnName )
				{
					$defaultLastRow[$columnName . '-final_value']        = "";
					$defaultLastRow[$columnName . '-value']              = "";
					$defaultLastRow[$columnName . '-has_cell_reference'] = false;
					$defaultLastRow[$columnName . '-has_formula']        = false;
				}

				array_push($items, $defaultLastRow);
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

	public function executeBuildUpQuantityItemUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$item = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find($request->getParameter('id'))
		);

		$rowData = array();
		$con     = $item->getTable()->getConnection();

		$billItem = $item->PostContractBillItemRate->BillItem;

		$formulatedColumnNames = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($billItem->UnitOfMeasurement);

		try
		{
			$con->beginTransaction();

			$fieldName  = $request->getParameter('attr_name');
			$fieldValue = $request->getParameter('val');

			$affectedNodes      = array();
			$isFormulatedColumn = false;

			if ( in_array($fieldName, $formulatedColumnNames) )
			{
				$formulatedColumnTable = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityFormulatedColumn');

				$formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($item->id, $fieldName);

				$formulatedColumn->setFormula($fieldValue);

				$formulatedColumn->save($con);

				$formulatedColumn->refresh();

				$isFormulatedColumn = true;
			}
			else
			{
				$item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
				$item->save($con);
			}

			$con->commit();

			$success = true;

			$errorMsg = null;

			$item->refresh();

			if ( $isFormulatedColumn )
			{
				$referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

				foreach ( $referencedNodes as $referencedNode )
				{
					$node = $formulatedColumnTable->find($referencedNode['node_from']);

					if ( $node )
					{
						$total = $node->BuildUpQuantityItem->calculateTotal();

						$affectedNode = array(
							'id'                        => $node->relation_id,
							$fieldName . '-final_value' => $node->final_value,
							'total'                     => $total
						);

						array_push($affectedNodes, $affectedNode);
					}
				}
			}
			else
			{
				$rowData[$fieldName] = $item->$fieldName;
			}

			foreach ( $formulatedColumnNames as $columnName )
			{
				$formulatedColumn                             = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
				$finalValue                                   = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
				$rowData[$columnName . '-final_value']        = $finalValue;
				$rowData[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
				$rowData[$columnName . '-has_cell_reference'] = false;
				$rowData[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
			}

			$rowData['sign']           = (string) $item->sign;
			$rowData['sign_symbol']    = $item->getSignText();
			$rowData['total']          = $item->calculateTotal();
			$rowData['affected_nodes'] = $affectedNodes;
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
			'data'     => $rowData
		));
	}

	public function executeBuildUpQuantityItemDelete(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$buildUpItem = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find($request->getParameter('id'))
		);

		$errorMsg = null;

		try
		{
			$item['id']    = $buildUpItem->id;
			$affectedNodes = $buildUpItem->delete();

			$success = true;
		}
		catch (Exception $e)
		{
			$errorMsg      = $e->getMessage();
			$item          = array();
			$affectedNodes = array();
			$success       = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'affected_nodes' => $affectedNodes ));
	}

	public function executeBuildUpQuantityItemPaste(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$buildUpQuantityItem = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find($request->getParameter('id'))
		);

		$data         = array();
		$lastPosition = false;
		$success      = false;
		$errorMsg     = null;

		$targetBuildUpQuantityItem = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find(intval($request->getParameter('target_id')));

		if ( !$targetBuildUpQuantityItem )
		{
			$this->forward404Unless($targetBuildUpQuantityItem = Doctrine_Core::getTable('PostContractRemeasurementBuildUpQuantityItem')->find($request->getParameter('prev_item_id')));
			$lastPosition = true;
		}

		if ( $request->getParameter('type') == 'cut' and $targetBuildUpQuantityItem->id == $buildUpQuantityItem->id )
		{
			$errorMsg = "cannot move item into itself";

			return $this->renderJson(array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() ));
		}

		switch ($request->getParameter('type'))
		{
			case 'cut':
				try
				{
					$buildUpQuantityItem->moveTo($targetBuildUpQuantityItem->priority, $lastPosition);

					$data['id'] = $buildUpQuantityItem->id;

					$success  = true;
					$errorMsg = null;
				}
				catch (Exception $e)
				{
					$errorMsg = $e->getMessage();
				}
				break;
			case 'copy':
				try
				{
					$formulatedColumnNames  = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($buildUpQuantityItem->PostContractBillItemRate->BillItem->UnitOfMeasurement);
					$newBuildUpQuantityItem = $buildUpQuantityItem->copyTo($targetBuildUpQuantityItem, $lastPosition);

					$form = new BaseForm();

					$data['id']          = $newBuildUpQuantityItem->id;
					$data['description'] = $newBuildUpQuantityItem->description;
					$data['sign']        = (string) $newBuildUpQuantityItem->sign;
					$data['sign_symbol'] = $newBuildUpQuantityItem->getSigntext();
					$data['relation_id'] = $newBuildUpQuantityItem->post_contract_bill_item_rate_id;
					$data['total']       = $newBuildUpQuantityItem->calculateTotal();
					$data['_csrf_token'] = $form->getCSRFToken();

					foreach ( $formulatedColumnNames as $constant )
					{
						$formulatedColumn                        = $newBuildUpQuantityItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
						$finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
						$data[$constant . '-final_value']        = $finalValue;
						$data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
						$data[$constant . '-has_cell_reference'] = false;
						$data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
					}

					$success  = true;
					$errorMsg = null;
				}
				catch (Exception $e)
				{
					$errorMsg = $e->getMessage();
				}
				break;
			default:
				throw new Exception('invalid paste operation');
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
	}

	public function executeGetLinkInfo(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$postContractBillItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->findOneBy('bill_item_id', array( $request->getParameter('id') )) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bcid')) and
			$request->hasParameter('t')
		);

		return $this->renderJson(array( 'has_linked_qty' => false ));
	}

}