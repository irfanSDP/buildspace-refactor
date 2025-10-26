<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php $rowCount = 0;
		$headerCount    = 6; ?>
		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('bqReportHeader', array(
				'reportTitle'  => $printingPageTitle,
				'topLeftRow1'  => null,
				'topRightRow1' => $columnDescription,
				'topLeftRow2'  => $elementTitle,
				'topRightRow2' => $billDescription,
			));
			?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2">No</td>
		<td class="bqHeadCell" style="min-width:400px;width:auto;" rowspan="2">Description</td>
		<td class="bqHeadCell" style="min-width:60px;width:60px;" rowspan="2">Unit</td>
		<td class="bqHeadCell" style="min-width:180px;width:180px;" colspan="2">Quantities</td>
		<td class="bqHeadCell" style="min-width:40px;width:40px;" rowspan="2">Difference %</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:90px;width:90px;">Qty</td>
		<td class="bqHeadCell" style="min-width:90px;width:90px;">Qty 2</td>
	</tr>

	<?php
	$firstItem = false;

	for ( $x = 0; $x <= $maxRows; $x ++ ):

		$itemPadding     = 6;
		$itemRow          = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
		$headerClass      = null;
		$headerStyle      = null;
		$borderTopStyling = null;

		$rowCount ++;

		$qty1       = $itemRow ? $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::QTY_1] : null;
		$qty2       = $itemRow ? $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::QTY_2] : null;
		$difference = $itemRow ? $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::DIFFERENCE] : null;
		$unit       = $itemRow ? $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_UNIT] : null;
		$itemId     = $itemRow ? $itemRow[0] : null;

		if ( !$firstItem )
		{
			$borderTopStyling = 'border-top: 1px solid black;';
		}

		if ( $itemRow and ( $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER OR $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER_N ) )
		{
			$headerClass = 'bqHead' . $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_LEVEL];
		}
		?>
		<tr>
			<td class="bqCounterCell"
			    style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
			<td class="bqDescriptionCell"
			    style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
				<?php if ( $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_TYPE] == sfBillItemQtyIncludingQty2ReportGenerator::ROW_TYPE_PC_RATE ): ?>
					<?php include_partial('printBQ/primeCostRateTable', array( 'printAmountOnly' => $printAmountOnly, 'currency' => $currency, 'itemRow' => $itemRow, 'priceFormatting' => $priceFormatting, 'printNoPrice' => $printNoPrice, 'printFullDecimal' => $printFullDecimal, 'rateCommaRemove' => $rateCommaRemove )) ?>
				<?php else: ?>
					<?php $preClass = $headerClass ? $headerClass : 'description' ?>
					<?php echo $itemRow ? '<pre class="' . $preClass . '">' . trim($itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_DESCRIPTION]) . '</pre>' : null ?>
				<?php endif?>
			</td>
			<td class="bqUnitCell" style="<?php echo $borderTopStyling; ?>">
				<?php echo ( $itemRow[sfBillItemQtyIncludingQty2ReportGenerator::ROW_BILL_ITEM_ID] ) ? $unit : "&nbsp;"; ?>
			</td>

			<td class="bqRateCell" style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $qty1 && $qty1 != 0 ? number_format($qty1, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
			</td>
			<td class="bqRateCell" style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $qty2 && $qty2 != 0 ? number_format($qty2, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
			</td>

			<td class="bqRateCell" style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $difference && $difference != 0 ? number_format($difference, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) . '%' : null; ?>
			</td>
		</tr>
		<?php $firstItem = true;
		unset( $itemPage[$x], $amount ); endfor; ?>

	<tr>
		<td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;"
		    colspan="<?php echo $headerCount; ?>">
			Page <?php echo $pageCount; ?>
		</td>
	</tr>
</table>
</body>
</html>