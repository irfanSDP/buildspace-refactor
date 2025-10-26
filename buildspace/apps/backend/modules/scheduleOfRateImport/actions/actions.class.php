<?php

/**
 * scheduleOfRateImport actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfRateImport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfRateImportActions extends BaseActions {

	public function executeGetResourceList(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$records = DoctrineQuery::create()->select('r.id, r.name')
			->from('Resource r')
			->addOrderBy('r.id ASC')
			->fetchArray();

		$form = new BaseForm();

		foreach ( $records as $key => $record )
		{
			$records[$key]['can_be_deleted'] = ResourceTable::linkToSoR($record['id']) ? false : true;
			$records[$key]['_csrf_token']    = $form->getCSRFToken();

			unset( $record );
		}

		$records[] = array(
			'id'             => Constants::GRID_LAST_ROW,
			'name'           => null,
			'can_be_deleted' => false,
			'_csrf_token'    => $form->getCSRFToken(),
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'label'      => 'name',
			'items'      => $records
		));
	}

	public function executeImportResourceItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$sorTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('trade_id'))
		);

		$conn     = $sorTrade->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids      = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
			$withRate = $request->getParameter('with_rate') == 'true' ? true : false;

			ScheduleOfRateItemTable::importResourceItems($conn, $request->getParameter('id'), $sorTrade, $ids, $withRate);

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

	public function executeGetScheduleOfRateList(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$records = DoctrineQuery::create()->select('r.id, r.name')
			->from('ScheduleOfRate r')
			->addOrderBy('r.id ASC')
			->fetchArray();

		$form = new BaseForm();

		foreach ( $records as $key => $record )
		{
			$records[$key]['_csrf_token'] = $form->getCSRFToken();
		}

		$records[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'name'        => null,
			'_csrf_token' => $form->getCSRFToken(),
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'label'      => 'name',
			'items'      => $records
		));
	}

	public function executeImportSORItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		Doctrine_Manager::connection()->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$sorTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('trade_id'))
		);

		$conn     = $sorTrade->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids      = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
			$withRate = $request->getParameter('with_rate') == 'true' ? true : false;

			ScheduleOfRateItemTable::importSORItems($conn, $request->getParameter('id'), $sorTrade, $ids, $withRate);

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

	public function executeGetScheduleOfRateListWithResource(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resource_id'))
		);

		// need to find which SoR that is currently using the Resource
		$scheduleOfRates = ScheduleOfRateTable::getRecordsAssociatedWithResource($resource);

		// add default empty row
		$scheduleOfRates[] = array(
			'id'   => Constants::GRID_LAST_ROW,
			'name' => null,
		);

		$data['identifier'] = 'id';
		$data['items']      = $scheduleOfRates;

		return $this->renderJson($data);
	}

	public function executeGetScheduleOfRateTradeListWithResource(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resource_id')) and
			$sor = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('sor_id'))
		);

		// need to find which SoR that is currently using the Resource
		$scheduleOfRateTrades = ScheduleOfRateTradeTable::getRecordsAssociatedWithResourceAndScheduleOfRate($resource, $sor);

		// add default empty row
		$scheduleOfRateTrades[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
		);

		$data['identifier'] = 'id';
		$data['items']      = $scheduleOfRateTrades;

		return $this->renderJson($data);
	}

	public function executeGetScheduleOfRateItemListWithResource(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resource_id')) and
			$sor = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('sor_id')) and
			$sorTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('trade_id'))
		);

		$data                      = array();
		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');
		$form                      = new BaseForm();

		// need to find which SoR that is currently using the Resource
		list(
			$items, $formulatedColumns
			) = ScheduleOfRateItemTable::getRecordsAssociatedWithResourceAndScheduleOfRateTrade($resource, $sorTrade);

		foreach ( $items as $sorItem )
		{
			$sorItem['type']        = (string) $sorItem['type'];
			$sorItem['uom_id']      = $sorItem['uom_id'] > 0 ? (string) $sorItem['uom_id'] : '-1';
			$sorItem['uom_symbol']  = $sorItem['uom_id'] > 0 ? $sorItem['uom_symbol'] : '';
			$sorItem['updated_at']  = date('d/m/Y H:i', strtotime($sorItem['updated_at']));
			$sorItem['_csrf_token'] = $form->getCSRFToken();

			foreach ( $formulatedColumnConstants as $constant )
			{
				$sorItem[$constant . '-final_value']        = 0;
				$sorItem[$constant . '-value']              = '';
				$sorItem[$constant . '-has_cell_reference'] = false;
				$sorItem[$constant . '-has_formula']        = false;
				$sorItem[$constant . '-has_build_up']       = false;
			}

			if ( array_key_exists($sorItem['id'], $formulatedColumns) )
			{
				foreach ( $formulatedColumns[$sorItem['id']] as $formulatedColumn )
				{
					$columnName                                   = $formulatedColumn['column_name'];
					$sorItem[$columnName . '-final_value']        = $formulatedColumn['final_value'];
					$sorItem[$columnName . '-value']              = $formulatedColumn['value'];
					$sorItem[$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
					$sorItem[$columnName . '-has_cell_reference'] = false;
					$sorItem[$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
				}
			}

			$data['items'][] = $sorItem;

			unset( $formulatedColumns[$sorItem['id']], $sorItem );
		}

		// empty row
		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
			'uom_id'      => 0,
			'uom_symbol'  => null,
			'_csrf_token' => $form->getCSRFToken(),
		);

		foreach ( $formulatedColumnConstants as $constant )
		{
			$defaultLastRow[$constant . '-final_value']        = "";
			$defaultLastRow[$constant . '-value']              = "";
			$defaultLastRow[$constant . '-has_build_up']       = false;
			$defaultLastRow[$constant . '-has_cell_reference'] = false;
			$defaultLastRow[$constant . '-has_formula']        = false;
		}

		$data['items'][]    = $defaultLastRow;
		$data['identifier'] = 'id';

		return $this->renderJson($data);
	}

	public function executeGetScheduleOfRateBuildUpRateItemListWithResource(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$resource = Doctrine_Core::getTable('Resource')->find($request->getParameter('resource_library_id')) and
			$scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('sor_item_id'))
		);

		$buildUpRateItems = DoctrineQuery::create()
			->select('i.id, i.description, i.uom_id, i.total, i.line_total, i.resource_item_library_id,
			ifc.column_name, ifc.value, ifc.final_value, ifc.linked, uom.symbol')
			->from('ScheduleOfRateBuildUpRateItem i')
			->leftJoin('i.FormulatedColumns ifc')
			->leftJoin('i.UnitOfMeasurement uom')
			->leftJoin('i.ScheduleOfRateBuildUpRateResource sorburr')
			->where('i.schedule_of_rate_item_id = ?', array( $scheduleOfRateItem->id ))
			->andWhere('sorburr.resource_library_id = ?', array( $resource->id ))
			->addOrderBy('i.priority ASC')
			->fetchArray();

		$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');

		foreach ( $buildUpRateItems as $key => $buildUpRateItem )
		{
			$buildUpRateItems[$key]['uom_id']     = $buildUpRateItem['uom_id'] > 0 ? (string) $buildUpRateItem['uom_id'] : '-1';
			$buildUpRateItems[$key]['uom_symbol'] = $buildUpRateItem['uom_id'] > 0 ? $buildUpRateItem['UnitOfMeasurement']['symbol'] : '';
			$buildUpRateItems[$key]['linked']     = $buildUpRateItem['resource_item_library_id'] > 0 ? true : false;

			foreach ( $formulatedColumnConstants as $constant )
			{
				$buildUpRateItems[$key][$constant . '-final_value']        = 0;
				$buildUpRateItems[$key][$constant . '-value']              = '';
				$buildUpRateItems[$key][$constant . '-has_cell_reference'] = false;
				$buildUpRateItems[$key][$constant . '-has_formula']        = false;
				$buildUpRateItems[$key][$constant . '-linked']             = false;
			}

			foreach ( $buildUpRateItem['FormulatedColumns'] as $formulatedColumn )
			{
				$columnName                                                  = $formulatedColumn['column_name'];
				$buildUpRateItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
				$buildUpRateItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
				$buildUpRateItems[$key][$columnName . '-has_cell_reference'] = false;
				$buildUpRateItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

				if ( $columnName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $columnName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $columnName == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
				{
					$buildUpRateItems[$key][$columnName . '-linked'] = $formulatedColumn['linked'];
				}
			}

			unset( $buildUpRateItem, $buildUpRateItems[$key]['FormulatedColumns'], $buildUpRateItems[$key]['UnitOfMeasurement'] );
		}

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'uom_id'      => '-1',
			'uom_symbol'  => '',
			'total'       => '',
			'line_total'  => '',
			'linked'      => false,
		);

		foreach ( $formulatedColumnConstants as $constant )
		{
			$defaultLastRow[$constant . '-final_value']        = "";
			$defaultLastRow[$constant . '-value']              = "";
			$defaultLastRow[$constant . '-linked']             = false;
			$defaultLastRow[$constant . '-has_cell_reference'] = false;
			$defaultLastRow[$constant . '-has_formula']        = false;
		}

		array_push($buildUpRateItems, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $buildUpRateItems
		));
	}

	public function executeImportSORBuildUpItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless($request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$resource = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateResource')->find($request->getParameter('rid')) and
			$scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id'))
		);

		$errorMsg     = null;
		$items        = array();
		$totalBuildUp = 0;

		try
		{
			$form                      = new BaseForm();
			$formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateBuildUpRateItem');
			$buildUpRateItems          = $scheduleOfRateItem->importSORBuildUpItems(Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))), $resource);

			$scheduleOfRateItem->save();

			$totalBuildUp = $scheduleOfRateItem->calculateBuildUpTotalByResourceId($resource->id);

			foreach ( $buildUpRateItems as $buildUpRateItem )
			{
				$item                = array();
				$item['id']          = $buildUpRateItem->id;
				$item['description'] = $buildUpRateItem->description;
				$item['uom_id']      = $buildUpRateItem->uom_id > 0 ? (string) $buildUpRateItem->uom_id : '-1';
				$item['uom_symbol']  = $buildUpRateItem->uom_id > 0 ? $buildUpRateItem->UnitOfMeasurement->symbol : '';
				$item['relation_id'] = $scheduleOfRateItem->id;
				$item['linked']      = $buildUpRateItem->resource_item_library_id > 0 ? true : false;
				$item['_csrf_token'] = $form->getCSRFToken();

				foreach ( $formulatedColumnConstants as $constant )
				{
					$formulatedColumn                        = $buildUpRateItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
					$finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
					$item[$constant . '-final_value']        = $finalValue;
					$item[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
					$item[$constant . '-has_cell_reference'] = false;
					$item[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

					if ( $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_RATE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_WASTAGE or $constant == ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_CONSTANT )
					{
						$item[$constant . '-linked'] = $formulatedColumn ? $formulatedColumn['linked'] : false;
					}
				}

				$item['total']      = $buildUpRateItem->calculateTotal();
				$item['line_total'] = $buildUpRateItem->calculateLineTotal();

				array_push($items, $item);
			}

			$defaultLastRow = array(
				'id'          => Constants::GRID_LAST_ROW,
				'description' => '',
				'uom_id'      => '-1',
				'uom_symbol'  => '',
				'relation_id' => $scheduleOfRateItem->id,
				'total'       => '',
				'line_total'  => '',
				'linked'      => false,
				'_csrf_token' => $form->getCSRFToken()
			);

			foreach ( $formulatedColumnConstants as $constant )
			{
				$defaultLastRow[$constant . '-final_value']        = "";
				$defaultLastRow[$constant . '-value']              = "";
				$defaultLastRow[$constant . '-linked']             = false;
				$defaultLastRow[$constant . '-has_cell_reference'] = false;
				$defaultLastRow[$constant . '-has_formula']        = false;
			}

			array_push($items, $defaultLastRow);

			$success = true;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'totalBuildUp' => $totalBuildUp ));
	}

}