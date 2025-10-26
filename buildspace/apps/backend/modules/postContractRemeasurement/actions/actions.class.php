<?php

/**
 * postContractRemeasurement actions.
 *
 * @package    buildspace
 * @subpackage postContractRemeasurement
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractRemeasurementActions extends BaseActions {

	public function executeGetAllBills(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
			$project->type == ProjectStructure::TYPE_ROOT
		);

		// get current project filtering mode
		$filterBy = $request->getParameter('opt');

		$tenderAlternativeProjectStructureIds = [];
        $tenderAlternative = $project->getAwardedTenderAlternative();

        if($tenderAlternative)
        {
            //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
            $tenderAlternativeProjectStructureIds = [-1];
            $tenderAlternativesBills = $tenderAlternative->getAssignedBills();

            if($tenderAlternativesBills)
            {
                $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
            }
        }

		$query = DoctrineQuery::create()
			->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity')
			->from('ProjectStructure s')
			->leftJoin('s.BillType t')
			->leftJoin('s.BillColumnSettings c');

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$query->leftJoin('s.Elements e')
				->leftJoin('e.Items i');
		}

		$query->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
			->andWhere('s.root_id = ?', $project->id)
			->andWhere('s.type = ?', ProjectStructure::TYPE_BILL);
		
		if(!empty($tenderAlternativeProjectStructureIds))
		{
			$query->whereIn('s.id', $tenderAlternativeProjectStructureIds);
		}

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$query->andWhere('t.type = ? OR i.type = ?', array( BillType::TYPE_PROVISIONAL, BillItem::TYPE_ITEM_PROVISIONAL ))
				->andWhere('i.project_revision_deleted_at IS NULL');
		}
		
		$records = $query->addOrderBy('s.lft ASC')->fetchArray();

		foreach ( $records as $key => $record )
		{
			$billFilterBy = $filterBy;

			if ( $record['BillType']['type'] == BillType::TYPE_PROVISIONAL )
			{
				$billFilterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$billAddition = 0;
			$billOmission = 0;

			$remeasurementClaims = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByProject($record['id'], $record['BillColumnSettings'], $billFilterBy);

			foreach ( $remeasurementClaims as $billColumnSettingKey => $remeasurementClaim )
			{
				$omission = 0;
				$addition = 0;
				$quantity = 0;

				foreach ( $record['BillColumnSettings'] as $billColumnSetting )
				{
					if ( $billColumnSetting['id'] == $billColumnSettingKey )
					{
						$quantity = $billColumnSetting['quantity'];
						break;
					}
				}

				$omission += $remeasurementClaim[0]['omission'];
				$addition += $remeasurementClaim[0]['addition'];

				$billAddition += $addition * $quantity;
				$billOmission += $omission * $quantity;
			}

			$records[$key]['omission']             = $billOmission;
			$records[$key]['addition']             = $billAddition;
			$records[$key]['nettAdditionOmission'] = $billAddition - $billOmission;

			unset( $remeasurementClaims, $records[$key]['BillType'], $records[$key]['BillColumnSettings'], $billRecord, $bill );
		}

		$defaultLastRow = array(
			'id'    => Constants::GRID_LAST_ROW,
			'title' => '',
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetBillTypes(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$filterBy = $request->getParameter('opt');

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		$query = DoctrineQuery::create()
			->select('s.id, s.name, s.quantity')
			->from('BillColumnSetting s')
			->where('s.project_structure_id = ?', $bill->id)
			->addOrderBy('s.id ASC');

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$query->leftJoin('s.ProjectStructure p')
				->leftJoin('p.Elements e')
				->leftJoin('e.Items i')
				->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL)
				->andWhere('i.project_revision_deleted_at IS NULL');
		}

		$records = $query->fetchArray();

		foreach ( $records as $key => $billType )
		{
			$omission = 0;
			$addition = 0;

			$remeasurementClaims = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByElement($bill, $billType, $filterBy);

			foreach ( $remeasurementClaims as $remeasurementClaim )
			{
				$omission += $remeasurementClaim[0]['omission'];
				$addition += $remeasurementClaim[0]['addition'];
			}

			$records[$key]['omission']             = $omission * $billType['quantity'];
			$records[$key]['addition']             = $addition * $billType['quantity'];
			$records[$key]['nettAdditionOmission'] = $records[$key]['addition'] - $records[$key]['omission'];

			unset( $billType );
		}

		$defaultLastRow = array(
			'id'   => Constants::GRID_LAST_ROW,
			'name' => '',
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetElementList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('btId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$filterBy = $request->getParameter('opt');

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		$query = DoctrineQuery::create()
			->select('e.id, e.description, e.note, fc.column_name, fc.value, fc.final_value')
			->from('BillElement e')
			->leftJoin('e.FormulatedColumns fc')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC');

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$query->leftJoin('e.Items i')->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
		}

		$elements = $query->fetchArray();

		// get element remeasurement's claim costing
		$elementTotalRates = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByElement($bill, $billType->toArray(), $filterBy);

		$form = new BaseForm();

		foreach ( $elements as $key => $element )
		{
			$omission = 0;
			$addition = 0;

			if ( array_key_exists($element['id'], $elementTotalRates) )
			{
				$omission = $elementTotalRates[$element['id']][0]['omission'];
				$addition = $elementTotalRates[$element['id']][0]['addition'];
			}

			$elements[$key]['omission']             = $omission;
			$elements[$key]['addition']             = $addition;
			$elements[$key]['nettAdditionOmission'] = $elements[$key]['addition'] - $elements[$key]['omission'];
			$elements[$key]['has_note']             = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
			$elements[$key]['note']                 = (string) $element['note'];
			$elements[$key]['relation_id']          = $bill->id;
			$elements[$key]['_csrf_token']          = $form->getCSRFToken();
		}

		unset( $elementTotalRates );

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'has_note'                   => false,
			'grand_total'                => 0,
			'original_grand_total'       => 0,
			'overall_total_after_markup' => 0,
			'element_sum_total'          => 0,
			'relation_id'                => $bill->id,
			'_csrf_token'                => $form->getCSRFToken()
		);

		array_push($elements, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $elements
		));
	}

	public function executeGetItemList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('elementId')) and
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('btId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$form         = new BaseForm();
		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$filterBy     = $request->getParameter('opt');

		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = PostContractBillItemRateTable::getRemeasurementItemList($postContract, $billType, $element, $filterBy);

		foreach ( $billItems as $billItem )
		{
			$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
			$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
			$billItem['type']                    = (string) $billItem['type'];
			$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
			$billItem['relation_id']             = $element->id;
			$billItem['linked']                  = false;
			$billItem['_csrf_token']             = $form->getCSRFToken();
			$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
			$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
			$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
			$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
			$billItem['omission-has_build_up']   = false;
			$billItem['addition-qty_per_unit']   = 0;
			$billItem['addition-total_per_unit'] = 0;
			$billItem['addition-has_build_up']   = false;

			if ( array_key_exists($billItem['post_contract_bill_item_rate_id'], $remeasurementClaims) )
			{
				$costing = $remeasurementClaims[$billItem['post_contract_bill_item_rate_id']];

				$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
				$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
				$billItem['addition-has_build_up']   = $costing['has_build_up'];

				unset( $costing );
			}

			$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

			if ( array_key_exists($billType->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billType->id]) )
			{
				$billItemTypeRef = $billItemTypeReferences[$billType->id][$billItem['id']];

				unset( $billItemTypeReferences[$billType->id][$billItem['id']] );

				if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
				{
					foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
					{
						$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

						unset( $billItemTypeRefFormulatedColumn );
					}
				}
			}

			array_push($items, $billItem);
			unset( $billItem );
		}

		unset( $billItems, $remeasurementClaims );

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'rate'                     => 0,
			'omission-qty_per_unit'    => 0,
			'omission-total_per_unit'  => 0,
			'addition-qty_per_unit'    => 0,
			'addition-total_per_unit'  => 0,
			'nett_addition_omission'   => 0,
			'relation_id'              => $element->id,
			'level'                    => 0,
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
			'_csrf_token'              => $form->getCSRFToken()
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeRemeasurementItemUpdate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('btId')) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $billType->ProjectStructure->root_id) and
			$postContractItemRate = Doctrine_Core::getTable('PostContractBillItemRate')->find($request->getParameter('id'))
		);

		$attrName = $request->getParameter('attr_name');
		$value    = is_numeric($request->getParameter('val')) ? $request->getParameter('val') : 0;

		if ( $attrName != 'addition-qty_per_unit' )
		{
			throw new Exception('Invalid column submission !');
		}

		$searchCol   = 'post_contract_bill_item_rate_idAndbill_column_setting_id';
		$searchVal   = array( $postContractItemRate->id, $billType->id );
		$claimRecord = Doctrine_Core::getTable('PostContractRemeasurementClaim')->findOneBy($searchCol, $searchVal);

		try
		{
			// get item remeasurement total per unit
			$totalPerUnit = $value * $postContractItemRate->rate;

			$claimRecord = ( $claimRecord ) ? $claimRecord : new PostContractRemeasurementClaim();

			if ( $claimRecord->isNew() )
			{
				$claimRecord->post_contract_bill_item_rate_id = $postContractItemRate->id;
				$claimRecord->bill_column_setting_id          = $billType->id;
			}

			$claimRecord->qty_per_unit   = Utilities::prelimRounding($value);
			$claimRecord->total_per_unit = Utilities::prelimRounding($totalPerUnit);
			$claimRecord->has_build_up   = false;
			$claimRecord->save();

			// get post contract item total
			$postContractItemTotal = DoctrineQuery::create()
				->from('PostContractBillItemType e')
				->where('e.post_contract_id = ?', $postContract->id)
				->andWhere('e.bill_item_id = ?', $postContractItemRate->bill_item_id)
				->andWhere('e.bill_column_setting_id = ?', $billType->id)
				->limit(1)
				->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
				->fetchOne();

			$itemTotalPerUnit = ( $postContractItemTotal ) ? Utilities::prelimRounding($postContractItemTotal['total_per_unit']) : 0;

			$item['addition-qty_per_unit']   = $value;
			$item['addition-total_per_unit'] = $totalPerUnit;
			$item['addition-has_build_up']   = $claimRecord->has_build_up;
			$item['nett_addition_omission']  = Utilities::prelimRounding($totalPerUnit - $itemTotalPerUnit);

			$success = true;
		}
		catch (Exception $e)
		{
			$success = false;
			$item    = array();
		}

		return $this->renderJson(array( 'success' => $success, 'item' => $item ));
	}
}
