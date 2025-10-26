<?php

/**
 * postContractSubPackageRemeasurementBillBuildUpQuantity actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageRemeasurementBillBuildUpQuantity
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageRemeasurementBillBuildUpQuantityActions extends BaseActions {

	public function executeGetBuildUpQuantityItemList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$spPcBillItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('bill_item_id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantityItems = DoctrineQuery::create()
			->select('i.id, i.description, i.sign, i.total, ifc.column_name, ifc.value, ifc.final_value')
			->from('PostContractSubPackageRemeasurementBuildUpQuantityItem i')
			->leftJoin('i.FormulatedColumns ifc')
			->where('i.sub_package_post_contract_bill_item_rate_id = ?', $spPcBillItemRate->id)
			->andWhere('i.bill_column_setting_id = ?', $billColumnSetting->id)
			->andWhere('i.type = ?', $request->getParameter('type'))
			->addOrderBy('i.priority ASC')
			->fetchArray();

		$form = new BaseForm();

		$formulatedColumnNames = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($spPcBillItemRate->BillItem->UnitOfMeasurement);

		foreach ( $buildUpQuantityItems as $key => $buildUpQuantityItem )
		{
			$buildUpQuantityItems[$key]['sign']        = (string) $buildUpQuantityItem['sign'];
			$buildUpQuantityItems[$key]['sign_symbol'] = PostContractRemeasurementBuildUpQuantityItemTable::getSignTextBySign($buildUpQuantityItem['sign']);
			$buildUpQuantityItems[$key]['relation_id'] = $spPcBillItemRate->id;
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
			'relation_id' => $spPcBillItemRate->id,
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

		$data = array(
			'identifier' => 'id',
			'items'      => $buildUpQuantityItems
		);

		return $this->renderJson($data);
	}

	public function executeGetBuildUpSummary(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->hasParameter('type') and
			$spPcBillItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantitySummary = PostContractSubPackageRemeasurementBuildUpQuantitySummaryTable::createByBillItemIdAndBillColumnSettingId($spPcBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$data = array(
			'apply_conversion_factor'    => $buildUpQuantitySummary->apply_conversion_factor,
			'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
			'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
			'total_quantity'             => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
			'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
			'rounding_type'              => ( $buildUpQuantitySummary->rounding_type ) ? $buildUpQuantitySummary->rounding_type : $spPcBillItemRate->BillItem->Element->ProjectStructure->BillSetting->build_up_quantity_rounding_type
		);

		return $this->renderJson($data);
	}

	public function executeBuildUpSummaryRoundingUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$spPcBillItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantitySummary = PostContractSubPackageRemeasurementBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($spPcBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$buildUpQuantitySummary->rounding_type = $request->getParameter('rounding_type');

		$buildUpQuantitySummary->save();
		$buildUpQuantitySummary->refresh();

		$data = array(
			'total_quantity' => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
			'final_quantity' => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
			'rounding_type'  => $buildUpQuantitySummary->rounding_type
		);

		return $this->renderJson($data);
	}

	public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$spPcBillItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$value = $request->getParameter('value');

		$buildUpQuantitySummary = PostContractSubPackageRemeasurementBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($spPcBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

		$buildUpQuantitySummary->apply_conversion_factor = $value;
		$buildUpQuantitySummary->save();

		$buildUpQuantitySummary->refresh();

		$data = array(
			'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
			'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
			'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', '')
		);

		return $this->renderJson($data);
	}

	public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$spPcBillItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('id')) and
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
		);

		$buildUpQuantitySummary = PostContractSubPackageRemeasurementBuildUpQuantitySummaryTable::getByBillItemIdAndBillColumnSettingId($spPcBillItemRate->id, $billColumnSetting->id, $request->getParameter('type'));

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

		$data = array(
			'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
			'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
			'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator
		);

		return $this->renderJson($data);
	}

	public function executeBuildUpQuantityItemAdd(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

		$items = array();

		$item = new PostContractSubPackageRemeasurementBuildUpQuantityItem();

		$con = $item->getTable()->getConnection();

		$isFormulatedColumn = false;

		if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
		{
			$this->forward404Unless(
				$spPcBillItemRate = Doctrine_Core::getTable('SubPackagePostContractBillItemRate')->find($request->getParameter('relation_id')) and
				$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_column_setting_id'))
			);

			$formulatedColumnNames = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($spPcBillItemRate->BillItem->UnitOfMeasurement);

			$previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find($request->getParameter('prev_item_id')) : null;

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
			$this->forward404Unless($nextItem = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find($request->getParameter('before_id')));

			$spPcBillItemRate      = $nextItem->SubPackagePostContractBillItemRate;
			$billColumnSetting     = $nextItem->BillColumnSetting;
			$formulatedColumnNames = PostContractSubPackageRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($spPcBillItemRate->BillItem->UnitOfMeasurement);

			$priority = $nextItem->priority;
		}

		try
		{
			$con->beginTransaction();

			DoctrineQuery::create()
				->update('PostContractSubPackageRemeasurementBuildUpQuantityItem')
				->set('priority', 'priority + 1')
				->where('priority >= ?', $priority)
				->andWhere('sub_package_post_contract_bill_item_rate_id = ?', $spPcBillItemRate->id)
				->andWhere('bill_column_setting_id = ?', $billColumnSetting->id)
				->andWhere('type = ?', $request->getParameter('type'))
				->execute();

			$item->sub_package_post_contract_bill_item_rate_id = $spPcBillItemRate->id;
			$item->bill_column_setting_id                      = $billColumnSetting->id;
			$item->priority                                    = $priority;
			$item->type                                        = $request->getParameter('type');
			$item->save($con);

			if ( $isFormulatedColumn )
			{
				$formulatedColumn              = new PostContractSubPackageRemeasurementBuildUpQuantityFormulatedColumn();
				$formulatedColumn->relation_id = $item->id;
				$formulatedColumn->column_name = $fieldName;
				$formulatedColumn->setFormula($fieldValue);

				$formulatedColumn->save($con);
			}

			$con->commit();

			$success  = true;
			$errorMsg = null;
			$data     = array();
			$form     = new BaseForm();

			$data['id']          = $item->id;
			$data['description'] = $item->description;
			$data['sign']        = (string) $item->sign;
			$data['sign_symbol'] = $item->getSignText();
			$data['total']       = $item->calculateTotal();
			$data['relation_id'] = $spPcBillItemRate->id;
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
					'relation_id' => $spPcBillItemRate->id,
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
			$item = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find($request->getParameter('id'))
		);

		$rowData = array();
		$con     = $item->getTable()->getConnection();

		$billItem = $item->SubPackagePostContractBillItemRate->BillItem;

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
				$formulatedColumnTable = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityFormulatedColumn');

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
			$buildUpItem = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find($request->getParameter('id'))
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
			$buildUpQuantityItem = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find($request->getParameter('id'))
		);

		$data         = array();
		$lastPosition = false;
		$success      = false;
		$errorMsg     = null;

		$targetBuildUpQuantityItem = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find(intval($request->getParameter('target_id')));

		if ( !$targetBuildUpQuantityItem )
		{
			$this->forward404Unless($targetBuildUpQuantityItem = Doctrine_Core::getTable('PostContractSubPackageRemeasurementBuildUpQuantityItem')->find($request->getParameter('prev_item_id')));
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
					$formulatedColumnNames  = PostContractRemeasurementBuildUpQuantityItemTable::getFormulatedColumnNames($buildUpQuantityItem->SubPackagePostContractBillItemRate->BillItem->UnitOfMeasurement);
					$newBuildUpQuantityItem = $buildUpQuantityItem->copyTo($targetBuildUpQuantityItem, $lastPosition);

					$form = new BaseForm();

					$data['id']          = $newBuildUpQuantityItem->id;
					$data['description'] = $newBuildUpQuantityItem->description;
					$data['sign']        = (string) $newBuildUpQuantityItem->sign;
					$data['sign_symbol'] = $newBuildUpQuantityItem->getSigntext();
					$data['relation_id'] = $newBuildUpQuantityItem->sub_package_post_contract_bill_item_rate_id;
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
