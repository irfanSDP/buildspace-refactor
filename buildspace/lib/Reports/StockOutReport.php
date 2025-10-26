<?php

class StockOutReport {

	public static function getRecordsWithDeliveryOrderQuantities(ProjectStructure $project, $resourceItemIds)
	{
		$newData         = array();
		$resourceItemIds = json_decode($resourceItemIds);

		if ( empty( $resourceItemIds ) )
		{
			return array();
		}

		$newDOQuantities = StockInDeliveryOrderItemQuantityTable::getOverAllItemQuantitiesFilterByResourceItemIdByProject($project, $resourceItemIds);
		$newSOQuantities = StockOutUsedQuantityItemQuantityTable::getOverAllItemQuantitiesFilterByResourceItemIdByProject($project, $resourceItemIds);

		$totalCostsWithTax    = StockInInvoiceItemTable::getTotalCostWithTaxByItemIds($project, $resourceItemIds);
		$totalCostsWithoutTax = StockInInvoiceItemTable::getTotalCostWithoutTaxByItemIds($project, $resourceItemIds);
		$invoiceItemRemarks   = StockInInvoiceItemTable::getInvoiceRemarksByItemIds($project, $resourceItemIds);

		$resourceItemIds = array_keys($totalCostsWithTax);
		$resourceItems   = StockInInvoiceItemTable::queryToGetResourceItemHierarchy($resourceItemIds);

		foreach ( $resourceItems as $key => $item )
		{
			$generatedRemarks    = null;
			$totalCostWithTax    = 0;
			$totalCostWithoutTax = 0;
			$doQuantity          = 0;
			$soQuantity          = 0;

			if ( isset( $totalCostsWithTax[$item['id']] ) )
			{
				$totalCostWithTax = $totalCostsWithTax[$item['id']];
			}

			if ( isset( $totalCostsWithoutTax[$item['id']] ) )
			{
				$totalCostWithoutTax = $totalCostsWithoutTax[$item['id']];
			}

			if ( isset( $newDOQuantities[$item['id']] ) )
			{
				$doQuantity = $newDOQuantities[$item['id']];
			}

			if ( isset( $newSOQuantities[$item['id']] ) )
			{
				$soQuantity = $newSOQuantities[$item['id']];
			}

			if ( isset( $invoiceItemRemarks[$item['id']] ) )
			{
				$remarks = array_unique($invoiceItemRemarks[$item['id']]);

				$generatedRemarks = ' - ' . implode(', ', $remarks);

				unset( $remarks, $invoiceItemRemarks[$item['id']] );
			}

			$resourceItems[$key]['description']            = $resourceItems[$key]['description'] . $generatedRemarks;
			$resourceItems[$key]['uom_symbol']             = $item['uom'];
			$resourceItems[$key]['total_cost_without_tax'] = $totalCostWithoutTax;
			$resourceItems[$key]['total_cost']             = $totalCostWithTax;
			$resourceItems[$key]['do_quantity']            = $doQuantity;
			$resourceItems[$key]['stock_out_quantity']     = $soQuantity;
			$resourceItems[$key]['balance_quantity']       = $doQuantity - $soQuantity;

			// resource_trade_id
			$newData[$item['resource_trade_id']][] = $resourceItems[$key];

			unset( $resourceItems[$key], $item );
		}

		return $newData;
	}

}