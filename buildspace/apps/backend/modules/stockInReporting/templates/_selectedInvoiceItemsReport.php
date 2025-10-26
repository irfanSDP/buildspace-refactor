<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php $rowCount = 0;
		$headerCount    = 12; ?>
		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('scheduleOfRateReporting/bqReportHeader', array(
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
		<td class="bqHeadCell" style="min-width:60px;width:60px;">No</td>
		<td class="bqHeadCell" style="min-width:400px;width:auto;">Description</td>
		<td class="bqHeadCell" style="min-width:90px;width:90px;">Qty</td>
		<td class="bqHeadCell" style="min-width:70px;width:70px;">Unit</td>
		<td class="bqHeadCell" style="min-width:90px;width:90px;">Rate</td>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">Discount</td>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">Tax</td>
		<td class="bqHeadCell" style="min-width:140px;width:140px;">Total without Tax</td>
		<td class="bqHeadCell" style="min-width:140px;width:140px;">Total with Tax</td>
		<td class="bqHeadCell" style="min-width:140px;width:140px;">Total GST Amount</td>
		<td class="bqHeadCell" style="min-width:90px;width:90px;">DO Qty</td>
		<td class="bqHeadCell" style="min-width:90px;width:90px;">Balance Qty</td>
	</tr>

	<?php
	$firstItem   = false;
	$totalAmount = 0;

	for ( $x = 0; $x <= $maxRows; $x ++ ):

		$itemPadding      = 6;
		$itemRow          = isset( $itemPage[$x] ) ? $itemPage[$x] : false;
		$headerClass      = null;
		$headerStyle      = null;
		$borderTopStyling = null;

		$rowCount ++;

		$qty              = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT] : null;
		$total            = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_WITH_TAX] : null;
		$totalWithoutTax  = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_TOTAL_WITHOUT_TAX] : null;
		$doQty            = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_DO_QTY] : null;
		$balanceQty       = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_BALANCE_QTY] : null;
		$unit             = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_UNIT] : null;
		$rate             = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_RATE] : null;
		$discount         = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_DISCOUNT] : null;
		$tax              = $itemRow ? $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_TAX] : null;
		$itemId           = $itemRow ? $itemRow[0] : null;
		$fontColorStyling = 'colorBlack';
		$totalCostDiff    = $total - $totalWithoutTax;

		if ( !$firstItem )
		{
			$borderTopStyling = 'border-top: 1px solid black;';
		}

		if ( $itemRow and ( $itemRow[sfBuildSpaceStockInInvoiceItemReportPageGenerator::ROW_BILL_ITEM_TYPE] == ResourceItem::TYPE_HEADER ) )
		{
			$headerClass = 'bqHead1';
			$headerStyle = 'text-decoration:underline;';
		}
		?>
		<tr>
			<td class="bqCounterCell"
			    style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;' ?></td>
			<td class="bqDescriptionCell"
			    style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
				<?php $preClass = $headerClass ? $headerClass : 'description' ?>
				<?php echo $itemRow ? '<pre class="' . $preClass . '" style="' . $headerStyle . '">' . trim($itemRow[2]) . '</pre>' : null ?>
			</td>

			<!-- Qty -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $qty && $qty != 0 ? number_format($qty, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- Unit -->
			<td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling; ?>">
				<?php echo $unit; ?>
			</td>

			<!-- Rate -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $rate && $rate != 0 ? number_format($rate, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- Discount -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $discount && $discount != 0 ? number_format($discount, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) . '%' : null ?>
			</td>

			<!-- Tax -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $tax && $tax != 0 ? number_format($tax, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) . '%' : null ?>
			</td>

			<!-- Total Cost Without Tax -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $totalWithoutTax && $totalWithoutTax != 0 ? number_format($totalWithoutTax, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- Total Cost -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $total && $total != 0 ? number_format($total, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- Total GST Amount -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $totalCostDiff && $totalCostDiff != 0 ? number_format($totalCostDiff, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- DO Qty -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $doQty && $doQty != 0 ? number_format($doQty, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- Balance Qty -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $balanceQty && $balanceQty != 0 ? number_format($balanceQty, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>
		</tr>
		<?php $firstItem = true;
		unset( $itemRow, $itemPage[$x], $amount ); endfor; ?>

	<tr>
		<td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;"
		    colspan="<?php echo $headerCount; ?>">
			Page <?php echo $pageCount; ?>
		</td>
	</tr>
</table>
</body>
</html>